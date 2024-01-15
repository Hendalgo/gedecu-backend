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
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

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
        if ($search) {
            $query = $query->join('banks_accounts', 'reports.bank_account_id', "=", "banks_accounts.id")
               ->join('banks', 'banks_accounts.bank_id', "=", "banks.id")
               ->join('users', 'reports.user_id', "=", 'users.id')
               ->whereNotNull('reports.meta_data->store')
               ->join('stores', function ($join) {
                   $join->on('stores.id', '=', DB::raw("CAST(JSON_EXTRACT(reports.meta_data, '$.store') AS UNSIGNED)"));
               })
               ->select('reports.*', 'banks.name as bank_name');
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
        if ($currentUser->role->id === 1) {
            if($user){
                $query = $query->where('reports.user_id', $user)->with('type');
            }
            return response()->json($query->paginate(10), 200);
        }
        else{
            $query = $query->where('reports.user_id', $currentUser->id)->with('type');
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
            // Create the report
            $report = Report::create([
                'type_id' => $request->type_id,
                'user_id' => auth()->user()->id,
                'meta_data' => json_encode([])
            ]);
            try {
                if ($report) {
                    if ($report_type->type === 'income') {
                        foreach ($validatedSubreports as $subreport) {
                            $amount = $subreport['amount'];
                            if(array_key_exists('rate', $subreport)){
                                $amount = $subreport['amount'] * $subreport['rate'];
                            }
                            if(array_key_exists('receiverAccount_id
                            ', $subreport) && array_key_exists('senderAccount_id', $subreport)){
                                //This is a traspaso
                            }
                            else if(!array_key_exists('account_id', $subreport)){
                                //Update store account
                                //Update all reports type cash to add new rule
                            }
                            else{
                                //Update bank account
                            }
                        }
                    }
                    if ($report_type->type === 'expense') {
                    }
                    if($report_type->type === 'neutro'){

                    }
                    return response()->json($report, 201);
                } else {
                    return response()->json(['error' => 'Hubo un problema al crear el reporte'], 500);
                }
            } catch (Error $th) {
                $report->delete();
                return response()->json(['error' => $th], 500);
            }
        } else {
            return response()->json(['error' => 'No tienes permiso para crear este tipo de reporte'], 401);
        }
    }
    public function update (Request $request, $id){

        $currentUser = User::find(auth()->user()->id);
        if ($currentUser->role->id === 1) {
            $validateRequest = $request->validate([
                'duplicated_status' => [
                    Rule::in(['done', 'cancel'])
                ],
                'inconsistence_check' =>'boolean'
            ]);
    
            $report = Report::find($id);
            if (isset($request->inconsistence_check)) {
                $report->inconsistence_check = $request->inconsistence_check;
    
                $report->save();
    
                return response()->json(['message' => 'Exito'], 201);
            }
            
            $report->duplicated_status = $request->duplicated_status;
    
            if ($report->save()) {
                
                $report = Report::with('type')->find($id);
                $bank = BankAccount::find($report->bank_account_id);
                if ($request->duplicated_status === 'done') {
                   if ($report->type->type === 'income') {
                        $bank->balance = $bank->balance - $report->amount;
                        $bank->save();
                   } 
                   elseif ($report->type->type === 'expense') {
                        $bank->balance = $bank->balance + $report->amount;
                        $bank->save();
                   }
                }
            }
            
            return response()->json(['message' => 'Exito'], 201);
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
    public function show($id){
        $report = Report::with('type', 'user.role')->find($id);
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
        $validateRequest = $request->validate([
            'id' => 'require|exist:reports,id'
        ]);

        $report = Report::find($id);
        if($report->type_id === 1){

        }
        else{
            
        }
    }
    public function getInconsistences(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $order = $request->get('order', 'desc');
            $order_by = $request->get('order_by', 'created_at');
            $date = $request->get('date');
            $until = $request->get('until');
            $review = $request->get('reviewed');
            //Reports Expense to group in the same array report  Depositante (id 4) and Transferencia Enviada (id 2)
            $reportsE = Report::query()
                ->join('reports_types', 'reports.type_id', '=', 'reports_types.id')
                ->select('reports.*', 'reports_types.name as report_name', 'reports_types.type as operation_type');
            //Reports Income to group in the same array report Caja fuerte (id 3) and Peticion de transferencia (id 1)
            $reportsI = Report::query()
                ->join('reports_types', 'reports.type_id', '=', 'reports_types.id')
                ->select('reports.*', 'reports_types.name as report_name', 'reports_types.type as operation_type');
            
            $reportsE = $reportsE->orderBy($order_by, $order);
            $reportsI = $reportsI->orderBy($order_by, $order);
            if ($date) {
                $reportsE = $reportsE->whereDate('reports.created_at', $date);
                $reportsI = $reportsI->whereDate('reports.created_at', $date);
            }
            if ($review === 'yes') {
                $reportsE = $reportsE->where('reports.inconsistence_check', true);
                $reportsI = $reportsI->where('reports.inconsistence_check', true);
            }
            else{
                
                $reportsE = $reportsE->where('inconsistence_check', false);
                $reportsI = $reportsI->where('inconsistence_check', false);
            }
            $reportsE = $reportsE
                ->havingRaw('operation_type LIKE ?', ["expense"])
                ->with('bank_account.bank.country', 'bank_account.bank.currency', 'type', 'user')->paginate(2);
            foreach ($reportsE as $e) {
                if (isset(json_decode($e->meta_data)->store)) {
                    $e->store = Store::find(json_decode($e->meta_data)->store);
                }
            }
            foreach ($reportsE as $e) {
                if (isset(json_decode($e->meta_data)->bank)) {
                    $e->bank = Bank::find(json_decode($e->meta_data)->bank);
                }
            }
            foreach ($reportsE as $e) {
                if (isset(json_decode($e->meta_data)->account_manager)) {
                    $e->account_manager = User::find(json_decode($e->meta_data)->account_manager);
                }
            }
            $reportsI = $reportsI
                ->havingRaw('operation_type LIKE ?', ["income"])
                ->with('bank_account.bank.country', 'bank_account.bank.currency', 'type', 'user')->paginate(2);
            foreach ($reportsI as $e) {
                if (isset(json_decode($e->meta_data)->store)) {
                    $e->store = Store::find(json_decode($e->meta_data)->store);
                }
            }
            foreach ($reportsI as $e) {
                if (isset(json_decode($e->meta_data)->account_manager)) {
                    $e->account_manager = User::find(json_decode($e->meta_data)->account_manager);
                }
            }
            foreach ($reportsI as $e) {
                if (isset(json_decode($e->meta_data)->bank)) {
                    $e->bank = Bank::find(json_decode($e->meta_data)->bank);
                }
            }
            return response()->json([
                'income' => $reportsI,
                'expense' => $reportsE
            ], 200);
        }
        else{
            return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 422);
        }
    }
}
