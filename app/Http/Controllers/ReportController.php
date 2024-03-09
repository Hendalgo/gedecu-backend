<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Report;
use App\Models\ReportType;
use App\Models\RoleReportPermission;
use App\Models\Store;
use App\Models\SubreportData;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\KeyValueMap;
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

        //Get TimeZone header
        $timezone = $request->header('TimeZone');

        // Start query
        $query = Report::query();

        // Apply filters

        if (! $user && $currentUser->role->id === 1) {
            $query = $query->leftJoin('users', 'reports.user_id', '=', 'users.id')
                ->select('users.id as user_id', 'users.name as user_name', 'users.email', DB::raw('MAX(reports.created_at) as report_date'))
                ->orderByDesc('report_date')
                ->groupBy('users.id', 'users.name', 'users.email');

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
            $query = $query->where('reports.user_id', $user)->with('type', 'subreports', 'user.role');

            return response()->json($query->paginate(10), 200);
        } else {
            $query = $query->where('reports.user_id', $currentUser->id)->with('type', 'subreports');

            return response()->json($query->paginate(10), 200);
        }

        return response()->json(['error' => 'Ocurrio un error al intentar visualizar los reportes'], 500);
    }

    public function store(Request $request)
    {
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
            $report_type = ReportType::with(['validations'])->find($request->type_id);
            $report_type_config = json_decode($report_type->meta_data, true);

            $validator = Validator::make([], []); // Create a empty intance of validator
            $validatedSubreports = [];

            // Validate all fields in the subreports
            foreach ($subreports as $subreport) {
                $validator->setData($subreport);
                $validator->setRules(['currency_id' => 'required|exists:currencies,id']);

                // Validate date
                $validator->setRules(['date' => 'required|date']);
                
                //Get the validations for the role all
                $reportValidations = $report_type->validations->where('validation_role', 'all')->toArray();

                //Get the validations for the role
                $reportValidationsRole = $report_type->validations->where('validation_role', auth()->user()->role->id)->toArray();
                if (array_key_exists('convert_amount', $report_type_config)) {
                    $validator->setRules(['conversionCurrency_id' => 'required|exists:currencies,id']);
                }
                foreach ($reportValidations as $validation) {
                    if (array_key_exists($validation['name'], $subreport)) {
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
                if (count($reportValidationsRole) > 0) {
                    foreach ($reportValidationsRole as $validation) {
                        if (array_key_exists($validation['name'], $subreport)) {
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
            try {
                $report = [];
                // Create the report
                DB::transaction(function () use (&$report, $request, $validatedSubreports, $report_type, $report_type_config) {
                    $report = Report::create([
                        'type_id' => $request->type_id,
                        'user_id' => auth()->user()->id,
                        'meta_data' => json_encode([]),
                    ]);

                    // Create the subreports
                    $this->create_subreport($validatedSubreports, $report, $report_type_config);

                    //Add or substract the amount to the bank account
                    if ($report_type->type === 'income') {
                        foreach ($validatedSubreports as $subreport) {
                            $amount = $subreport['amount'];
                            $currency = $subreport['currency_id'];
                            if (array_key_exists('convert_amount', $report_type_config)) {
                                $amount = $this->calculateAmount($subreport);
                                $currency = $subreport['conversionCurrency_id'];
                            }
                            if (array_key_exists('user_balance', $report_type_config)) {
                                $userBalance = UserBalance::where('user_id', auth()->user()->id)->first();
                                if (! $userBalance) {
                                    throw new \Exception('No se encontró el balance del usuario');
                                }
                                $userBalance->balance += $amount;
                                $userBalance->save();
                            } elseif (array_key_exists('receiverAccount_id', $subreport) && array_key_exists('senderAccount_id', $subreport)) {
                                //This is a traspaso
                                $senderAccount = BankAccount::find($subreport['senderAccount_id']);
                                $receiverAccount = BankAccount::find($subreport['receiverAccount_id']);
                                $senderAccount->balance = $senderAccount->balance - $amount;
                                $receiverAccount->balance = $receiverAccount->balance + $amount;
                                $senderAccount->save();
                                $receiverAccount->save();
                            } elseif (array_key_exists('account_id', $subreport)) {
                                //Update bank account
                                $bankAccount = BankAccount::find($subreport['account_id']);
                                $bankAccount->balance = $bankAccount->balance + $amount;
                                $bankAccount->save();
                            } elseif (! array_key_exists('account_id', $subreport)) {
                                $store = Store::with('accounts')->where('user_id', auth()->user()->id)->first();
                                if (! $store) {
                                    throw new \Exception('No se encontró el local del usuario');
                                }
                                foreach ($store->accounts as $account) {
                                    if ($account->account_type_id == 3 && $account->currency_id == $currency) {
                                        $account->balance += $amount;
                                        $account->save();
                                    }
                                }
                            }
                        }
                    }
                    if ($report_type->type === 'expense') {
                        foreach ($validatedSubreports as $subreport) {
                            $amount = $subreport['amount'];
                            $currency = $subreport['currency_id'];
                            if (array_key_exists('convert_amount', $report_type_config)) {
                                $amount = $this->calculateAmount($subreport);
                                $currency = $subreport['conversionCurrency_id'];
                            }
                            if (array_key_exists('user_balance', $report_type_config)) {
                                $userBalance = UserBalance::where('user_id', auth()->user()->id)->first();
                                if (! $userBalance) {
                                    throw new \Exception('No se encontró el balance del usuario');
                                }
                                $userBalance->balance -= $amount;
                                $userBalance->save();
                            } elseif (array_key_exists('receiverAccount_id', $subreport) && array_key_exists('senderAccount_id', $subreport)) {
                                //This is a traspaso
                                $senderAccount = BankAccount::find($subreport['senderAccount_id']);
                                $receiverAccount = BankAccount::find($subreport['receiverAccount_id']);
                                $senderAccount->balance = $senderAccount->balance - $amount;
                                $receiverAccount->balance = $receiverAccount->balance + $amount;
                                $senderAccount->save();
                                $receiverAccount->save();
                            } elseif (array_key_exists('account_id', $subreport)) {
                                //Update bank account
                                $bankAccount = BankAccount::find($subreport['account_id']);
                                $bankAccount->balance = $bankAccount->balance - $amount;
                                $bankAccount->save();
                            } elseif (! array_key_exists('account_id', $subreport)) {
                                $store = Store::with('accounts')->where('user_id', auth()->user()->id)->first();
                                if (! $store) {
                                    throw new \Exception('No se encontró el local del usuario');
                                }
                                foreach ($store->accounts as $account) {
                                    if ($account->account_type_id == 3 && $account->currency_id == $currency) {
                                        $account->balance -= $amount;
                                        $account->save();
                                    }
                                }
                            }
                        }
                    }
                });

                return response()->json($report, 201);
            } catch (\Error $e) {
                return response()->json(['error' => $e], 422);
            } catch (QueryException $e) {
                return response()->json(['error' => $e], 422);
            }
        } else {
            return response()->json(['error' => 'No tienes permiso para crear este tipo de reporte'], 401);
        }
    }

    public function update(Request $request, $id)
    {

        return response()->json(['message' => 'forbiden'], 401);
    }

    public function show($id)
    {
        $report = Report::with('type', 'user.role', 'subreports.data')->find($id);
        $currentUser = User::find(auth()->user()->id);
        $report->subreports = $this->KeyMapValue->transformElement($report->subreports);

        if (! $report) {
            return response()->json(['error' => 'No se encontró el reporte'], 404);
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
        
    }

    public function getInconsistences(Request $request)
    {

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
                'created_at' => $sub['date'],
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
                    'created_at' => $subreport[$index]['date'],
                ];
            }
        }
        SubreportData::insert($subreport_data);
        $insertedSubs->load('report.user.store', 'data');
        $insertedSubs = $this->KeyMapValue->transformElement($insertedSubs);
        $inconsistence->check_inconsistences($report, $insertedSubs);
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
}
