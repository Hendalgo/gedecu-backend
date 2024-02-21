<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Subreport;
use App\Services\KeyValueMap;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DuplicatedReportController extends Controller
{
    protected $keyValueMap;

    public function __construct(KeyValueMap $keyValueMap)
    {
        $this->keyValueMap = $keyValueMap;
    }

    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $paginated = $request->get('paginated', 'yes');
        $per_page = $request->get('per_page', 10);
        $completed = $request->get('completed', 'all');
        $order = $request->get('order', 'created_at');
        $orderBy = $request->get('orderBy', 'desc');
        $search = $request->get('search', null);
        $date = $request->get('date', null);
        $subreports = Subreport::query()
            ->where('subreports.duplicate', true)
            ->leftjoin('reports', 'subreports.report_id', '=', 'reports.id')
            ->leftjoin('users', 'reports.user_id', '=', 'users.id')
            ->select('subreports.*');
        $timezone = $request->header('TimeZone');
        if ($search !== null) {
            $subreports = $subreports->where(function ($query) use ($search) {
                $query->where('users.email', 'LIKE', '%'.$search.'%')
                    ->orWhere('users.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('subreports.amount', 'LIKE', '%'.$search.'%')
                    ->orWhere('subreports.data', 'LIKE', '%'.$search.'%')
                    ->orWhere(function ($query) use ($search) {
                        $query->where('subreports.duplicate_data', 'LIKE', '%'.$search.'%');
                    });
            });
        }
        if ($date) {
            $subreports = $subreports->where(function ($query) use ($date, $timezone) {
                $query->whereDate(DB::raw('DATE(CONVERT_TZ(subreports.created_at, "+00:00", "'.$timezone.'"))'), $date);
            });
        }

        if ($completed === 'yes') {
            $subreports = $subreports->where('subreports.duplicate_status', true);
        }
        if ($completed === 'no') {
            $subreports = $subreports->where('subreports.duplicate_status', false);
        }
        if ($currentUser->role->id !== 1) {
            $subreports = $subreports->where('users.id', $currentUser->id);
        }
        if ($order) {
            $subreports = $subreports->orderBy('subreports.'.$order, $orderBy);
        }
        $subreports = $subreports->with('report.user.role', 'currency', 'report.type', 'data');

        if ($paginated === 'no') {
            $subreports = $subreports->get();
        } else {
            $subreports = $subreports->paginate($per_page);
        }
        $subreports = $this->keyValueMap->transformElement($subreports);

        return response()->json($subreports, 200);
    }

    public function show($id)
    {
        $currentUser = auth()->user();
        $subreport = Subreport::query()
            ->where('subreports.duplicate', true)
            ->leftjoin('reports', 'subreports.report_id', '=', 'reports.id')
            ->leftjoin('users', 'reports.user_id', '=', 'users.id')
            ->select('subreports.*')
            ->where('subreports.id', $id);
        if ($currentUser->role->id !== 1) {
            $subreport = $subreport->where('users.id', $currentUser->id);
        }
        $subreport = $subreport->with('report.user', 'currency', 'report.type', 'data')->firstOrFail();

        $data = [];
        foreach ($subreport->data as $item_data) {
            $data[$item_data->key] = $item_data->value;
        }
        $subreport->sdata = $data;
        unset($subreport->data);
        $subreport->data = $subreport->sdata;
        unset($subreport->sdata);

        return response()->json($subreport, 200);
    }

    public function duplicated_complete(Request $request, $id)
    {
        $currentUser = auth()->user();
        // Check if user is admin
        if ($currentUser->role->id !== 1) {
            return response()->json(['message' => 'You are not authorized to complete this action'], 403);
        }
        $validateId = Validator::make(['id' => $id], [
            'id' => 'required|exists:subreports,id',
        ]);
        if ($validateId->fails()) {
            return response()->json(['message' => 'El subreporte no existe'], 400);
        }
        $subreport = Subreport::find($id);

        if (! $subreport) {
            return response()->json(['message' => 'El subreporte no existe'], 400);
        }

        if ($subreport->duplicate_status == true) {
            return response()->json(['message' => 'Este reporte ya fue completado'], 400);
        }
        if ($subreport->duplicate == false) {
            return response()->json(['message' => 'Este reporte no es un duplicado'], 400);
        }

        try {
            return DB::transaction(function () use ($request, $subreport) {
                $validatedData = $request->validate([
                    'amount' => 'required|numeric',
                    'currency_id' => 'required|exists:currencies,id',
                    'date' => 'required|date',
                ]);
                //Check if account_id is present
                // if the account exist
                if (array_key_exists('account_id', $request->all())) {
                    $validatedData['account_id'] = $request->validate([
                        'account_id' => 'required|exists:banks_accounts,id',
                    ]);
                    $account = BankAccount::where('delete', false)->where('id', $validatedData['account_id'])->firstOrFail();
                    $account->balance += $validatedData['amount'];
                    $account->save();
                } elseif (array_key_exists('store_id', $request->all())) {
                    $validatedData['store_id'] = $request->validate([
                        'store_id' => 'required|exists:stores,id',
                    ]);

                    $cashAccount = BankAccount::where('delete', false)->where('store_id', $validatedData['store_id'])->where('account_type_id', 3)->firstOrFail();
                    $cashAccount->balance += $validatedData['amount'];
                    $cashAccount->save();
                } else {
                    return response()->json(['message' => 'Debe especificar una cuenta o un comercio'], 400);
                }
                $subreport->duplicate_status = true;
                $subreport->duplicate_data = $request->all();
                $subreport->save();

                return response()->json(['message' => $subreport], 201);
            });
        } catch (Error $th) {
            return response()->json(['message' => $th], 400);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['message' => $e], 400);
        }
    }
}
