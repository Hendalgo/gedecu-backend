<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Inconsistence;
use App\Models\Report;
use App\Models\ReportType;
use App\Models\RoleReportPermission;
use App\Models\Store;
use App\Models\Subreport;
use App\Models\SubreportData;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\KeyValueMap;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    protected $KeyMapValue;

    public function __construct()
    {
        $this->KeyMapValue = new KeyValueMap();
    }

    public function index(Request $request)
    {
        $currentUser = User::find(auth()->user()->id);

        // Get query parameters
        $order = $request->get('order', 'created_at');
        $orderBy = $request->get('order_by', 'desc');
        $date = $request->get('date');
        $since = $request->get('since');
        $until = $request->get('until');
        $search = $request->get('search');
        $user = $request->get('user');
        $type = $request->get('type_id');
        $role = $request->get('role');
        $draft = $request->get('draft');

        //Get TimeZone header
        $timezone = $request->header('TimeZone');

        // Start query
        $query = Report::query();

        // Apply filters

        if (! $user && $currentUser->role->id === 1) {
            $query = $query->leftJoin('users', 'reports.user_id', '=', 'users.id')
                ->select('users.id as user_id', 'users.name as user_name', 'users.email', DB::raw('DATE_FORMAT(MAX(reports.created_at), "%Y-%m-%dT%T.000000Z") as report_date'))
                ->orderByDesc('report_date')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->where('reports.status', 'completed');

            if ($search) {
                $query = $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'LIKE', '%'.$search.'%')
                        ->orWhere('users.email', 'LIKE', '%'.$search.'%');
                });
            }
            
            if ($date) {
                $query = $query->whereDate(DB::raw('DATE(CONVERT_TZ(reports.created_at, "+00:00", "'.$timezone.'"))'), $date);
            }
            if ($role) {
                $query = $query->where('users.role_id', $role);
            }

            return response()->json($query->with('user.role')->paginate(10), 200);
        }
        if ($draft === 'yes') {
            $query = $query->where('status', 'draft');
        } else {
            $query = $query->where('status', 'completed');
        }
        if ($type) {
            $query = $query->where('reports.type_id', $type);
        }
        if ($since) {
            $query = $query->whereDate('reports.created_at', '>=', $since);
        }
        if ($until) {
            $query = $query->whereDate('reports.created_at', '<=', $until);
        }
        if ($date) {
            $query = $query->whereDate(DB::raw('DATE(CONVERT_TZ(reports.created_at, "+00:00", "'.$timezone.'"))'), $date);
        }
        if ($order && $orderBy) {
            $query = $query->orderBy($order, $orderBy);
        }
        //If the user is admin and the request has a user id, get all reports from that user with subreports
        //else get the reports, with the subreport from current user
        if ($currentUser->role->id === 1) {
            $query = $query->where('reports.user_id', $user)->with('type', 'subreports.inconsistences.data', 'subreports.inconsistencesAssociated.data', 'user.role')->paginate(10);

            foreach ($query as $report) {
                $report->subreports = $this->KeyMapValue->transformElement($report->subreports);
                foreach ($report->subreports as $subreport) {
                    $subreport->inconsistencesFinal = $subreport->inconsistences->concat($subreport->inconsistencesAssociated);
                    $subreport->inconsistencesFinal = $this->KeyMapValue->transformElement($subreport->inconsistencesFinal);
                    unset($subreport->inconsistences);
                    unset($subreport->inconsistencesAssociated);
                    $subreport->inconsistences = $subreport->inconsistencesFinal;
                    unset($subreport->inconsistencesFinal);

                    if (array_key_exists('account_id', $subreport->data)) {
                        $acc = BankAccount::find($subreport->data['account_id']);
                        if ($acc->delete) {
                            $data = $subreport->data;
                            $data['account_deleted'] = true;
                            $subreport->data = $data;
                        }
                    }
                }
            }

            return response()->json($query, 200);
        } else {
            $query = $query->where('reports.user_id', $currentUser->id)->with('type', 'subreports.data')->paginate(10);

            foreach ($query as $report) {
                $report->subreports = $this->KeyMapValue->transformElement($report->subreports);
            }

            return response()->json($query, 200);
        }

        return response()->json(['error' => 'Ocurrio un error al intentar visualizar los reportes'], 500);
    }
    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:reports_types,id',
        ]);
        
        $isDraft = $request->get('isDraft', "no");
        $role_report_permission = RoleReportPermission::where('role_id', auth()->user()->role->id)
            ->where('report_type_id', $request->type_id)
            ->first();
        if ($role_report_permission) {
            $subreport = new SubreportController();
            $validatedSubreports = $subreport->validate_subreport($request);
            $report_type = ReportType::with(['validations'])->find($request->type_id);
            $report_type_config = json_decode($report_type->meta_data, true);
            if (!is_array($validatedSubreports)) {
                return response()->json(json_decode($validatedSubreports), 422);
            }
            try {
                $report = [];
                // Create the report
                DB::transaction(function () use (&$report, $request, $validatedSubreports, $report_type, $report_type_config, $isDraft){
                    $report = Report::create([
                        'type_id' => $request->type_id,
                        'status' => $isDraft == "yes"? 'draft' : 'completed',
                        'user_id' => auth()->user()->id,
                        'meta_data' => json_encode([]),
                    ]);
                    
                    // Create the subreports
                    $insertedSub = $this->create_subreport($validatedSubreports, $report, $report_type_config);

                    //Add or substract the amount to the bank account
                    if($isDraft != "yes"){
                        foreach ($validatedSubreports as $key => $subreport) {
                            $this->add_or_substract_amount($subreport, $report_type_config, $report_type, $report, 'create', $insertedSub[$key]->id);
                        }
                    }
                });

                return response()->json($report, 201);
            } catch (\Error $e) {
                return response()->json(['error' => $e->getMessage()], 422);
            } catch (QueryException $e) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
        } else {
            return response()->json(['error' => 'No tienes permiso para crear este tipo de reporte'], 401);
        }
    }

    public function show($id)
    {
        $report = Report::with('type', 'user.role', 'subreports.data', 'subreports.inconsistences.data', 'subreports.inconsistences.report.user.role', 'subreports.inconsistences.report.user.role', 'subreports.inconsistences.report.type', 'subreports.inconsistences.report.user.store', 'subreports.inconsistencesAssociated.data', 'subreports.inconsistencesAssociated.report.user.role', 'subreports.inconsistencesAssociated.report.user.role', 'subreports.inconsistencesAssociated.report.type', 'subreports.inconsistencesAssociated.report.user.store')->find($id);
        if (! $report) {
            return response()->json(['error' => 'No se encontró el reporte'], 404);
        }
        $currentUser = User::find(auth()->user()->id);
        $report->subreports = $this->KeyMapValue->transformElement($report->subreports);
        foreach ($report->subreports as $subreport) {
            $verified = Inconsistence::where('subreport_id', $subreport->id)->where('verified', 1)->first();
            if ($verified) {
                $subreport->verified = true;
            }

            $subreport->inconsistencesFinal = $subreport->inconsistences->concat($subreport->inconsistencesAssociated);
            $subreport->inconsistencesFinal = $this->KeyMapValue->transformElement($subreport->inconsistencesFinal);
            unset($subreport->inconsistences);
            unset($subreport->inconsistencesAssociated);
            $subreport->inconsistences = $subreport->inconsistencesFinal;
            unset($subreport->inconsistencesFinal);

            if (array_key_exists('account_id', $subreport->data)) {
                $acc = BankAccount::find($subreport->data['account_id']);
                if ($acc->delete) {
                    $data = $subreport->data;
                    $data['account_deleted'] = true;
                    $subreport->data = $data;
                }
            }
        }
        if ($currentUser->role->id === 1) {
            return response()->json($report, 200);
        } else {
            if ($report->user_id === $currentUser->id) {
                return response()->json($report, 200);
            } else {
                return response()->json(['error' => 'No tienes permiso para ver este reporte'], 401);
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        Validator::make(['id' => $id], [
            'id' => 'required|exists:subreports,id',
        ])->validate();

        $subreport = Subreport::with('data')->findOrFail($id);
        $report = Report::findOrFail($subreport->report_id);
        $subreportsCount = $report->subreports()->count();
        $sub = $this->KeyMapValue->transformElement($subreport)[0];
        $currentUser = User::find(auth()->user()->id);
        if ($currentUser->role->id === 1) {

            DB::transaction(function () use ($sub, $report, $id) {
                //Undo the amount of the subreport
                $report_type = ReportType::find($report->type_id);
                $report_type_config = json_decode($report_type->meta_data, true);
                $this->add_or_substract_amount($sub->data, $report_type_config, $report_type, $report, 'undo', $id);

                //Verify if the report is has associated inconsistences
                $inconsistences = Inconsistence::where('subreport_id', $id)->delete();
                $inconsistence = Inconsistence::where('associated_id', $id)->update(['associated_id' => null]);
            });

            if ($subreportsCount === 1) {
                $report->delete();

                return response()->json(['message' => 'Reporte eliminado'], 200);
            }

            $subreport->delete();

            return response()->json(['message' => 'Subreporte eliminado'], 200);
        }

        return response()->json(['message' => 'forbiden'], 401);

    }
    
    public function update(Request $request, $id)
    {
        $subreports = $request->all();
        $isDraft = $request->get('isDraft', false);
        //Just the user that created the report can edit it
        $report = Report::with('type')->find($id);
        if ($report->user_id !== auth()->user()->id) {
            return response()->json(['error' => 'No tienes permiso para editar este reporte'], 401);
        }

        $reportValidate = new SubreportController();
        $reportValidate->validate_without_request([...$report->toArray(), 'subreports' => $subreports]);

        $report->load('subreports');

        //Validate if the report is editable
        if ($report->editable === 0 || Carbon::parse($report->created_at)->diffInDays(Carbon::now()) > 1) {
            return response()->json(['error' => 'No puedes editar este reporte'], 401);
        }
        //Validate if each subreport has a valid id and belongs to the report
        foreach ($subreports as $subreport) {
            $sub = Validator::make($subreport, [
                'id' => 'required|exists:subreports,id,report_id,'.$id,
            ])->validate();
        }


        return response()->json(['message' => 'Reporte editado'], 200);

        $edited = DB::transaction(function () use ($subreports, $report, $isDraft) {
            
            if ($isDraft !== "yes") {
                $report->status = 'completed';
                foreach ($subreports as $subreport) {

                    //Undo the amount of the subreport
                    if(!isset($subreport['id'])){
                        //Then is a new subreport and we need to create it
                        $report_type = ReportType::find($report->type_id);
                        $report_type_config = json_decode($report_type->meta_data, true);
                        $this->create_subreport([$subreport], $report, $report_type_config);
                        continue;
                    }
                    //Last subreport data
                    $sub = Subreport::findOrFail($subreport['id']);
                    $auxSub = Subreport::with('data')->findOrFail($subreport['id']);
                    $subData = $this->KeyMapValue->transformElement($auxSub)[0];
    
                    $report_type = ReportType::find($report->type_id);
                    $report_type_config = json_decode($report_type->meta_data, true);
                    $this->add_or_substract_amount($subData->data, $report_type_config, $report_type, $report, 'undo', $subreport['id']);
    
                    $amount = $subreport['amount'];
                    $currency = $subreport['currency_id'];
                    if (array_key_exists('convert_amount', $report_type_config)) {
                        $amount = $this->calculateAmount($subreport);
                        $currency = $subreport['conversionCurrency_id'];
                    }
    
                    $sub->amount = $amount;
                    $sub->currency_id = $currency;
                    $sub->duplicate = $subreport['isDuplicated'];
                    $sub->save();
    
                    //Edit subreport data
                    $subreport_data = SubreportData::where('subreport_id', $subreport['id'])->get();
                    foreach ($subreport_data as $data) {
                        $data->value = $subreport[$data->key];
                        $data->save();
                    }
    
                    //Add or substract the amount to the bank account
                    $this->add_or_substract_amount($subreport, $report_type_config, $report_type, $report, 'update', $subreport['id']);
                    // Delete Inconsistences
                    Inconsistence::where('subreport_id', $subreport['id'])->delete();
                    //update to null the associated_id of the inconsistences
                    Inconsistence::where('associated_id', $subreport['id'])->update(['associated_id' => null]);
                }
            }
            
            $report->editable = 0;
            $report->save();
            $inconsistence = new InconsistenceController();
            $report->subreports = $report->subreports()->get();
            $report->subreports = $this->KeyMapValue->transformElement($report->subreports);

            if($isDraft !== "yes"){
                $inconsistence->check_inconsistences($report, $report->subreports);
            }

            return true;
        });

        return response()->json(['message' => 'Reporte editado'], 200);
    }

    private function create_subreport(array $subreport, $report, $report_type_config)
    {
        $data = [];

        foreach ($subreport as $sub) {
            $currency = $sub['currency_id'];
            $amount = $sub['amount'];

            if (array_key_exists('convert_amount', $report_type_config)) {
                $currency = $sub['conversionCurrency_id'];
                $amount = $this->calculateAmount($sub);
            }
            $data[] = [
                'duplicate' => $sub['isDuplicated'],
                'amount' => $amount,
                'duplicate_status' => false,
                'currency_id' => $currency,
                'created_at' => Carbon::parse($sub['date']),
            ];
        }
        $inconsistence = new InconsistenceController();
        $insertedSubs = $report->subreports()->createMany($data);
        $subreport_data = [];
        foreach ($insertedSubs as $index => $insertedSub) {
            foreach ($subreport[$index] as $key => $value) {
                $subreport_data[] = [
                    'key' => $key,
                    'value' => $value,
                    'subreport_id' => $insertedSub->id,
                    'created_at' => Carbon::parse($subreport[$index]['date']),
                ];
            }
        }
        SubreportData::insert($subreport_data);
        $insertedSubs->load('report.user.store', 'data');
        $insertedSubs = $this->KeyMapValue->transformElement($insertedSubs);
        $inconsistence->check_inconsistences($report, $insertedSubs);

        return $insertedSubs;
    }

    //Calculate the amount of the subreport
    //This because the rate could be in the same currency or in the conversion currency
    private function calculateAmount(array $sub): float
    {
        if (array_key_exists('rate_currency', $sub)) {
            if ($sub['rate_currency'] == $sub['currency_id']) {
                return $sub['amount'] * $sub['rate'];
            } elseif ($sub['rate_currency'] == $sub['conversionCurrency_id']) {
                return $sub['amount'] / $sub['rate'];
            } else {
                throw new \Exception('No se encontró la tasa de cambio');
            }
        } else {
            throw new \Exception('No se encontró la tasa de cambio');
        }
    }

    private function add_or_substract_amount($subreport, $report_type_config, $report_type, $report, $operation, $subreport_id = null)
    {
        $amount = $subreport['amount'];
        $subreport['id'] = $subreport_id ?? $subreport['id'];
        $currency = $subreport['currency_id'];
        if ($report_type->id == 41) {
            $store = Store::with('accounts')->where('user_id', $report->user_id)->first();
            $convertedAmount = $this->calculateAmount($subreport);
            $amount = $operation === 'undo' ? $amount * -1 : $amount;
            $convertedAmount = $operation === 'undo' ? $convertedAmount * -1 : $convertedAmount;
            if (! $store) {
                throw new \Exception('No se encontró el local del usuario');
            }
            foreach ($store->accounts as $account) {
                if ($account->account_type_id == 3) {
                    $account->balance += $convertedAmount;
                    $account->save();

                    $transaction = new Transaction();
                    $transaction->type = "income";
                    $transaction->subreport_id = $subreport['id'];
                    $transaction->account_id = $account->id;
                    $transaction->amount = abs($convertedAmount);
                    $transaction->currency_id = $account->currency_id;
                    $transaction->created_at = $subreport['date'];
                }
            }

            $wallet = BankAccount::find($subreport['wallet_id']);
            $wallet->balance = $wallet->balance - $amount;
            $wallet->save();

            if ($operation !== 'undo') {
                $transaction = new Transaction();
                $transaction->type = "expense";
                $transaction->subreport_id = $subreport['id'];
                $transaction->account_id = $subreport['wallet_id'];
                $transaction->amount = abs($amount);
                $transaction->currency_id = $currency;
                $transaction->created_at = $subreport['date'];
                $transaction->save();
            }
            return;
        } elseif ($report_type->id == 40) {
            $wallet = BankAccount::find($subreport['wallet_id']);
            $bank = BankAccount::find($subreport['account_id']);
            $convertedAmount = $this->calculateAmount($subreport);

            $amount = $operation === 'undo' ? $amount * -1 : $amount;
            $convertedAmount = $operation === 'undo' ? $convertedAmount * -1 : $convertedAmount;

            $wallet->balance = $wallet->balance - $amount;
            $bank->balance = $bank->balance + $convertedAmount;

            $wallet->save();
            $bank->save();

            if ($operation !== 'undo') {
                $transaction = new Transaction();
                $transaction->type = "expense";
                $transaction->subreport_id = $subreport['id'];
                $transaction->account_id = $subreport['account_id'];
                $transaction->amount = abs($amount);
                $transaction->currency_id = $currency;
                $transaction->created_at = $subreport['date'];
                $transaction->save();

                $transaction = new Transaction();
                $transaction->type = "income";
                $transaction->subreport_id = $subreport['id'];
                $transaction->account_id = $subreport['wallet_id']; 
                $transaction->amount = abs($convertedAmount);
                $transaction->currency_id = $subreport['conversionCurrency_id'];
                $transaction->created_at = $subreport['date'];
                $transaction->save();
            }

            return;
        }
        if ($report_type->id == 42) {
            $wallet = BankAccount::find($subreport['wallet_id']);
            $bank = BankAccount::find($subreport['account_id']);
            $convertedAmount = $this->calculateAmount($subreport);

            $amount = $operation === 'undo' ? $amount * -1 : $amount;
            $convertedAmount = $operation === 'undo' ? $convertedAmount * -1 : $convertedAmount;

            $wallet->balance = $wallet->balance + $amount;
            $bank->balance = $bank->balance - $convertedAmount;

            $wallet->save();
            $bank->save();

            if ($operation !== 'undo') {
                $transaction = new Transaction();
                $transaction->type = "income";
                $transaction->subreport_id = $subreport['id'];
                $transaction->account_id = $subreport['account_id'];
                $transaction->amount = abs($amount);
                $transaction->currency_id = $currency;
                $transaction->created_at = $subreport['date'];
                $transaction->save();

                $transaction = new Transaction();
                $transaction->type = "expense";
                $transaction->subreport_id = $subreport['id'];
                $transaction->account_id = $subreport['wallet_id']; 
                $transaction->amount = abs($convertedAmount);
                $transaction->currency_id = $subreport['conversionCurrency_id'];
                $transaction->created_at = $subreport['date'];
                $transaction->save();
            }

            return;
        } elseif ($report_type->id == 43) {
            $store = Store::with('accounts')->where('user_id', $report->user_id)->first();
            $convertedAmount = $this->calculateAmount($subreport);

            $amount = $operation === 'undo' ? $amount * -1 : $amount;
            $convertedAmount = $operation === 'undo' ? $convertedAmount * -1 : $convertedAmount;

            if (! $store) {
                throw new \Exception('No se encontró el local del usuario');
            }
            foreach ($store->accounts as $account) {
                if ($account->account_type_id == 3) {
                    $account->balance -= $convertedAmount;
                    $account->save();
                }
            }
            $wallet = BankAccount::find($subreport['wallet_id']);
            $wallet->balance = $wallet->balance + $amount;
            $wallet->save();

            if ($operation !== 'undo') {
                $transaction = new Transaction();
                $transaction->type = "income";
                $transaction->subreport_id = $subreport['id'];
                $transaction->account_id = $subreport['wallet_id'];
                $transaction->amount = abs($amount);
                $transaction->currency_id = $currency;
                $transaction->created_at = $subreport['date'];
                $transaction->save();
            }

            return;
        }
        if (array_key_exists('convert_amount', $report_type_config)) {
            $amount = $this->calculateAmount($subreport);
            $currency = $subreport['conversionCurrency_id'];
        }
        if ($report_type->type === 'neutro') {
            $transaction = new Transaction();
            $transaction->amount = abs($amount);
            $transaction->subreport_id = $subreport['id'];
            $transaction->currency_id = $currency;
            $transaction->created_at = $subreport['date'];
            $transaction->type = 'neutro';
            $transaction->save();

            return;
        }
        if ($report_type->type === 'income' && $operation === 'undo') {
            $amount = $amount * -1;
        } elseif ($report_type->type === 'expense' && $operation === 'update') {
            $amount = $amount * -1;
        } elseif ($report_type->type === 'expense' && $operation === 'create') {
            $amount = $amount * -1;
        }
        $transaction = new Transaction();
        $transaction2 = new Transaction();

        if (array_key_exists('user_balance', $report_type_config)) {
            $userBalance = UserBalance::where('user_id', $report->user_id)->first();
            if (! $userBalance) {
                throw new \Exception('No se encontró el balance del usuario');
            }
            $userBalance->balance += $amount;
            $userBalance->save();
            
            $transaction->balance_id = $userBalance->id;
            $transaction->currency_id = $userBalance->currency_id;
            $transaction->amount = abs($amount);
            $transaction->subreport_id = $subreport['id'];
            $transaction->type = $report_type->type;
            $transaction->created_at = $subreport['date'];

        } elseif (array_key_exists('receiverAccount_id', $subreport) && array_key_exists('senderAccount_id', $subreport)) {
            //This is a traspaso
            $senderAccount = BankAccount::find($subreport['senderAccount_id']);
            $receiverAccount = BankAccount::find($subreport['receiverAccount_id']);
            $senderAccount->balance = $senderAccount->balance - $amount;
            $receiverAccount->balance = $receiverAccount->balance + $amount;
            $senderAccount->save();
            $receiverAccount->save();

            $transaction->account_id = $subreport['senderAccount_id'];
            $transaction->currency_id = $currency;
            $transaction->amount = abs($amount);
            $transaction->subreport_id = $subreport['id'];
            $transaction->type = 'expense';
            $transaction->created_at = $subreport['date'];

            $transaction2->account_id = $subreport['receiverAccount_id'];
            $transaction2->currency_id = $currency;
            $transaction2->amount = abs($amount);
            $transaction2->subreport_id = $subreport['id'];
            $transaction2->type = 'income';
            $transaction2->created_at = $subreport['date'];

        } elseif (array_key_exists('account_id', $subreport)) {
            //Update bank account
            $bankAccount = BankAccount::find($subreport['account_id']);
            $bankAccount->balance = $bankAccount->balance + $amount;
            $bankAccount->save();

            $transaction->account_id = $subreport['account_id'];
            $transaction->currency_id = $currency;
            $transaction->amount = abs($amount);
            $transaction->subreport_id = $subreport['id'];
            $transaction->type = $report_type->type;
            $transaction->created_at = $subreport['date'];

        } elseif (! array_key_exists('account_id', $subreport)) {
            $store = Store::with('accounts')->where('user_id', $report->user_id)->first();
            if (! $store) {
                throw new \Exception('No se encontró el local del usuario');
            }
            foreach ($store->accounts as $account) {
                if ($account->account_type_id == 3 && $account->currency_id == $currency) {
                    $account->balance += $amount;
                    $account->save();

                    $transaction->account_id = $account->id;
                    $transaction->currency_id = $currency;
                    $transaction->amount = abs($amount);
                    $transaction->subreport_id = $subreport['id'];
                    $transaction->type = $report_type->type;
                    $transaction->created_at = $subreport['date'];
                }
            }
        }

        if ($operation !== 'undo') {
            $transaction->save();
            if (isset($transaction2->account_id)) {
                $transaction2->save();
            }
        }else{
            Transaction::where('subreport_id', $subreport['id'])->delete();
        }
    }
}
