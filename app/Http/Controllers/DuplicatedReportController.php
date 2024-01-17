<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Subreport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuplicatedReportController extends Controller
{
    public function index (Request $request){
        $currentUser = auth()->user();
        $paginated = $request->get('paginated', 'yes');
        $per_page = $request->get('per_page', 10);
        $completed = $request->get('completed', 'no');
        $subreports = Subreport::query()
            ->where('subreports.duplicate', true)
            ->leftjoin('reports', 'subreports.report_id', '=', 'reports.id')
            ->leftjoin('users', 'reports.user_id', '=', 'users.id')
            ->select('subreports.*')
            ->groupBy('subreports.id');

        if ($completed === 'yes') {
            $subreports = $subreports->where('subreports.duplicate_status', true);
        }
        if ($currentUser->role->id !== 1 ){
            $subreports = $subreports->where('users.id', $currentUser->id);
        }
        if ($paginated === 'no') {
            return response()->json($subreports->with('report.user', 'currency')->get(), 200);
        }
        return response()->json($subreports->with('report.user', 'currency')->paginate($per_page), 200);
    }
    public function duplicated_complete(Request $request, $id){
        $currentUser = auth()->user();
        // Check if user is admin
        if ($currentUser->role->id !== 1 ){
            return response()->json(['message' => 'You are not authorized to complete this action'], 403);
        }

        $subreport = Subreport::query()->findOrFail($id);

        if ($subreport->duplicate_status === true){
            return response()->json(['message' => 'Este reporte ya fue completado'], 400);
        }
        if ($subreport->duplicate === false){
            return response()->json(['message' => 'Este reporte no es un duplicado'], 400);
        }

        try {
            DB::transaction(function () use ($request, $subreport) {
                $validatedData = $request->validate([
                    'amount' => 'required|numeric',
                    'currency_id' => 'required|exists:currencies,id',
                    'date' => 'required|date',
                ]);
                //Check if account_id is present
                // if the account exist 
                if (array_key_exists('account_id', $request->all())) {
                    $validatedData['account_id'] = $request->validate([
                        'account_id' => 'required|exists:bank_accounts,id',
                    ]);
                    $account = BankAccount::query()->findOrFail($validatedData['account_id']);
                    $account->balance = $account->balance + $validatedData['amount'];
                    $account->save();
                }
                else if (array_key_exists('store_id', $request->all())) {
                    $validatedData['store_id'] = $request->validate([
                        'store_id' => 'required|exists:stores,id',
                    ]);

                    $cashAccount = BankAccount::where('store_id', $validatedData['store_id'])->first();
                    $cashAccount->balance = $cashAccount->balance + $validatedData['amount'];
                    $cashAccount->save();
                }
                else {
                    return response()->json(['message' => 'Debe especificar una cuenta o un comercio'], 400);
                }
                $subreport->duplicate_status = true;
                $subreport->duplicate_data = $validatedData;
                $subreport->save();
            });
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al completar el reporte'], 400);
        }
    }
}
