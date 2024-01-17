<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Movement;
use App\Models\Report;
use App\Models\ReportType;
use App\Models\RoleReportPermission;
use App\Models\Store;
use App\Models\User;
use App\Models\UserBalance;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\Throw_;

class ReportController extends Controller
{
    public function index(Request $request){
        $currentUser = User::find(auth()->user()->id);

         // Get query parameters
        $order = $request->get('order', 'created_at');
        $orderBy = $request->get('order_by', 'desc');
        $duplicated = $request->get('duplicated');
        $duplicated_status = $request->get('duplicated_status');
        
        $date = $request->get('date');
        $since = $request->get('since');
        $until = $request->get('until');
        $bank = $request->get('bank');
        $search = $request->get('search');
        $user = $request->get('user');
        $type = $request->get('type_id');
        // Start query
        $query = Report::query();

        // Apply filters
        
        if (!$user && $currentUser->role->id === 1) {
            $query = $query->leftJoin('users', 'reports.user_id', "=", 'users.id')
                ->select('users.id as user_id','users.name as user_name', 'users.email', DB::raw('MAX(reports.created_at) as report_date'))
                ->orderByDesc('report_date')
                ->groupBy('users.id', 'users.name', 'users.email');

            if ($search) {
                $query = $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('users.email', 'LIKE', '%' . $search . '%');
                });
            }

            if ($date) {
                $query = $query->whereDate('reports.created_at', $date);
            }
            return response()->json($query->with('user.role')->paginate(10), 200);
        }
        if($type){
            $query = $query->where('reports.type_id', $type);
        }
        if($since){
            $query = $query->whereDate('reports.created_at', '>=', $since);
        }
        if($until){
            $query = $query->whereDate('reports.created_at', '<=', $until);
        }
        if($date){
            $query = $query->whereDate('reports.created_at', $date);
        }
        if ($order && $orderBy) {
            $query = $query->orderBy($order, $orderBy);
        }
        //If the user is admin and the request has a user id, get all reports from that user with subreports
        //else get the reports, with the subreport from current user
        if ($currentUser->role->id === 1) {
            $query = $query->where('reports.user_id', $user)->with('type', 'subreports');
            return response()->json($query->paginate(10), 200);
        }
        else{
            $query = $query->where('reports.user_id', $currentUser->id)->with('type', 'subreports');
            return response()->json($query->paginate(10), 200);
        }
        return response()->json(['error' => 'Ocurrio un error al intentar visualizar los reportes'], 500);
    }
    public function store(Request $request){
        $request->validate([
            'type_id' => 'required|exists:reports_types,id',
        ]);
        $role_report_permission = RoleReportPermission::where('role_id', auth()->user()->role->id)
            ->where('report_type_id', $request->type_id)
            ->first();
        if ($role_report_permission) {
            $request->validate([
                'subreports' => 'required|array',
            ]);
            $subreports = $request->subreports;
            $report_type = ReportType::find($request->type_id);
            $report_type_config = json_decode($report_type->meta_data, true);
            $validator = Validator::make([], []); // Create a empty intance of validator
            $validatedSubreports = [];
            // Validate all fields in the subreports
            foreach ($subreports as $subreport) {
                $reportValidations = $report_type_config['all'];
                foreach ($reportValidations as $validation) {
                    if (array_key_exists($validation['name'], $subreport)) {
                        $validator->setData([$validation['name'] => $subreport[$validation['name']]]);
                        $validator->setRules([$validation['name'] => $validation['validation']]);
                        if ($validator->fails()) {
                            $errorMessages = $validator->errors()->all();
                            return response()->json(['error' => 'Error de validación en el subreporte', 'validation_errors' => $errorMessages], 422);
                        }
                    } else {
                        return response()->json(['error' => 'Campo requerido no encontrado en el subreporte'], 422);
                    }
                }
                // Find if the role need extra validations for create the report
                if (array_key_exists(auth()->user()->role->id, $report_type_config)) {
                    $reportValidationsEspecial = $report_type_config[auth()->user()->role->id];
                    foreach ($reportValidationsEspecial as $validation) {
                        if (array_key_exists($validation['name'], $subreport)) {
                            $validator->setData([$validation['name'] => $subreport[$validation['name']]]);
                            $validator->setRules([$validation['name'] => $validation['validation']]);
                            if ($validator->fails()) {
                                return response()->json(['error' => 'Error de validación en el subreporte'], 422);
                            }
                        } else {
                            return response()->json(['error' => 'Campo requerido no encontrado en el subreporte'], 422);
                        }
                    }
                }
                // Save the subreport as a validated subreport
                $validatedSubreports[] = $subreport;
            }
            try{
                $report = [];
            // Create the report
                DB::transaction(function () use (&$report, $request, $validatedSubreports, $report_type){
                    $report = Report::create([
                        'type_id' => $request->type_id,
                        'user_id' => auth()->user()->id,
                        'meta_data' => json_encode([])
                    ]); 
                    
                    // Create the subreports
                    $this->create_subreport($validatedSubreports, $report);
                    
                    //Add or substract the amount to the bank account
                    if ($report_type->type === 'income') {
                        foreach ($validatedSubreports as $subreport) {
                            $amount = $subreport['amount'];
                            if(array_key_exists('rate', $subreport)){
                                $amount = $subreport['amount'] * $subreport['rate'];
                            }
                            if(array_key_exists('receiverAccount_id', $subreport) && array_key_exists('senderAccount_id', $subreport)){
                                //This is a traspaso
                                $senderAccount = BankAccount::find($subreport['senderAccount_id']);
                                $receiverAccount = BankAccount::find($subreport['receiverAccount_id']);
                                $senderAccount->balance = $senderAccount->balance - $amount;
                                $receiverAccount->balance = $receiverAccount->balance + $amount;
                                $senderAccount->save();
                                $receiverAccount->save();
                            }
                            else if(array_key_exists('account_id', $subreport)){
                                //Update bank account
                                $bankAccount = BankAccount::find($subreport['account_id']);
                                $bankAccount->balance = $bankAccount->balance + $amount;
                                $bankAccount->save();
                            }
                            else if(!array_key_exists('account_id', $subreport)){
                                $store = Store::with('account')->where('user_id', auth()->user()->id)->first();
                                if(!$store){
                                    throw new \Exception("No se encontró el local del usuario");   
                                }
                                $store->account->balance += $amount;
                                $store->account->save();
                            }
                        }
                    }
                    if ($report_type->type === 'expense') {
                        foreach ($validatedSubreports as $subreport) {
                            $amount = $subreport['amount'];
                            if(array_key_exists('rate', $subreport)){
                                $amount = $subreport['amount'] * $subreport['rate'];
                            }
                            if(array_key_exists('receiverAccount_id', $subreport) && array_key_exists('senderAccount_id', $subreport)){
                                //This is a traspaso
                                $senderAccount = BankAccount::find($subreport['senderAccount_id']);
                                $receiverAccount = BankAccount::find($subreport['receiverAccount_id']);
                                $senderAccount->balance = $senderAccount->balance - $amount;
                                $receiverAccount->balance = $receiverAccount->balance + $amount;
                                $senderAccount->save();
                                $receiverAccount->save();
                            }
                            else if(array_key_exists('account_id', $subreport)){
                                //Update bank account
                                $bankAccount = BankAccount::find($subreport['account_id']);
                                $bankAccount->balance = $bankAccount->balance - $amount;
                                $bankAccount->save();
                            }
                            else if(!array_key_exists('account_id', $subreport)){
                                $store = Store::with('account')->where('user_id', auth()->user()->id)->first();
                                if(!$store){
                                    throw new \Exception("No se encontró el local del usuario");   
                                }
                                $store->account->balance -= $amount;
                                $store->account->save();
                            }
                        }
                    }
                    if($report_type->type === 'neutro'){
                        //Get report type meta data to validate type and operation
                        /* $meta_data = json_decode($report->meta_data, true);
                        if ($meta_data['type'] === 4) {
                            if($meta_data['operation'] === 'income'){
                                if(array_key_exists('rate', $subreport)){
                                    $meta_data['amount'] = $meta_data['amount'] * $subreport['rate'];
                                }
                                $bankAccount = UserBalance::where('user_id', auth()->user()->id);
                                $bankAccount->amount = $bankAccount->amount + $meta_data['amount'];
                                $bankAccount->save();
                            }
                            else{
                                if(array_key_exists('rate', $subreport)){
                                    $meta_data['amount'] = $meta_data['amount'] * $subreport['rate'];
                                }
                                $bankAccount = UserBalance::where('user_id', auth()->user()->id);
                                $bankAccount->amount = $bankAccount->amount - $meta_data['amount'];
                                $bankAccount->save();
                            }
                        } */
                    }
                });
                return response()->json($report, 201);
            }catch(\Exception $e){
                return response()->json(['error' => 'Hubo un problema al crear el reporte'], 422);
            }
        } else {
            return response()->json(['error' => 'No tienes permiso para crear este tipo de reporte'], 401);
        }
    }
    public function update (Request $request, $id){

        return response()->json(['message' => 'forbiden'], 401);
    }
    public function show($id){
        $report = Report::with('type', 'user.role', 'subreports')->find($id);
        $currentUser = User::find(auth()->user()->id);
        if (!$report) {
           return response()->json(['error' => 'No se encontró el reporte'], 404);
        }
        if($currentUser->role->id === 1){
            return response()->json($report, 200);
        }
        else{
            if($report->user_id === $currentUser->id){
                return response()->json($report, 200);
            }
            else{
                return response()->json(['error' => 'No tienes permiso para ver este reporte'], 401);
            }
        }
    }
    public function destroy(Request $request, $id){
        return response()->json(['message' => 'forbiden'], 401);
    }
    public function getInconsistences(Request $request){
       
    }
    private function create_subreport (Array $subreport, $report){
        $data = [];
        foreach ($subreport as $sub) {
            $data[] = [
                'duplicate' =>  $sub['isDuplicated'],
                'amount' => $sub['amount'],
                'currency_id' => $sub['currency_id'],
                'data' => json_encode($sub)
            ];
        }
        $report->subreports()->createMany($data);
    }
}
