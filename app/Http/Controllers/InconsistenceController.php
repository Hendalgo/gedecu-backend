<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Inconsistence;
use App\Models\Report;
use App\Models\Store;
use App\Models\Subreport;
use App\Services\KeyValueMap;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InconsistenceController extends Controller
{
    protected $keyValueMap;

    public function __construct()
    {
        $this->keyValueMap = new KeyValueMap();
    }

    public function verify_all()
    {
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $inconsistences = Inconsistence::where('verified', 0)->update(['verified' => 1]);

        return response()->json($inconsistences);
    }

    public function verify_inconsistence(Request $request, $id)
    {
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $inconsistence = Inconsistence::where('subreport_id', $id)->update(['verified' => 1]);
        if (! $inconsistence) {
            return response()->json(['error' => 'Inconsistence not found'], 404);
        }
        return response()->json($inconsistence);
    }

    public function index(Request $request)
    {
        //Just can access admin
        $currentUser = auth()->user();
        if ($currentUser->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        //Get query parameters
        $since = $request->input('since');
        $until = $request->input('until');
        $search = $request->input('search');
        $type = $request->input('type');
        $date = $request->input('date');
        $per_page = $request->input('per_page', 10);
        $paginate = $request->input('paginate', 'yes');
        $order_by = $request->input('order', 'created_at');
        $order = $request->input('order_by', 'desc');
        $verified = $request->input('verified');


        $subreports = Subreport::query();
        
        if ($search) {
            $subreports = $subreports->where(function ($query) use ($search) {
                $query->whereHas('data', function ($subQuery) use ($search) {
                    $subQuery->where('value', 'like', '%' . $search . '%');
                })->orWhereHas('inconsistences.data', function ($subQuery) use ($search) {
                    $subQuery->where('value', 'like', '%' . $search . '%');
                });
            });
        }
        
        if ($since) {
            $subreports = $subreports->where('subreports.created_at', '>=', $since);
        }
        if ($until) {
            $subreports = $subreports->where('subreports.created_at', '<=', $until);
        }
        if ($type) {
            $subreports = $subreports->where('subreports.type_id', $type);
        }
        if ($date) {
            $subreports = $subreports->where('subreports.created_at', 'like', '%' . $date . '%');
        }
        if ($verified == strtolower('yes')) {
            $subreports = $subreports->whereHas('inconsistences', function ($query) {
                $query->where('inconsistences.verified', true);
            });
        }
        else if ($verified == strtolower('no')) {
            $subreports = $subreports->whereHas('inconsistences', function ($query) {
                $query->where('inconsistences.verified', false);
            });
        }

        $subreports->whereExists(function($query){
            $query->select('*')
                ->from('inconsistences')
                ->whereColumn('inconsistences.subreport_id', 'subreports.id');
        })
        ->with(['report' => function ($query) {
            $query->with('type', 'user.store', 'user.role');
        },
        'data',
        'inconsistences' => function ($query) {
            $query->with('data', 'report.user.store', 'report.type', 'report.user.role');
        }])
        ->orderBy($order_by, $order);

        if ($paginate == 'no') {
            $subreports = $subreports->get();
        } else {
            $subreports = $subreports->paginate($per_page);
        }
        $subreports->data = $this->keyValueMap->transformElement($subreports);
        $subreports->each(function ($subreport) {
            $subreport->inconsistences = $this->keyValueMap->transformElement($subreport->inconsistences);
        });
        return response()->json($subreports);
    }

    public function invoke($filtered, $sub, $type, $subreports)
    {
        if ($type == 1 || $type == 26) {
            $this->proveedor_proveedor($filtered, $sub, $type);
        }
        if ($type == 2 || $type == 6) {
            $this->helpG($filtered, $sub, $type);
        }
        if ($type == 23 || $type == 4) {
            $this->giro_local($filtered, $sub, $type, $subreports);
        }
        if ($type == 17 || $type == 27) {
            $this->efectivo_depositante_entrega_efectivo($filtered, $sub, $type);
        }
        if ($type == 15 || $type == 25) {
            $this->ayuda_recibida_local_ayuda_realizada_local($filtered, $sub, $type);
        }
    }

    private function ayuda_recibida_local_ayuda_realizada_local($filtered, $sub, $type)
    {
        $filtered = $filtered->filter(function ($item) use ($sub) {
            $itemData = json_decode($item->data, true);
            if ($itemData['store_id'] == $sub->report->user->store->id) {
                return true;
            }

            return false;
        });

        return $this->check_if_have_matches($filtered, $sub);
    }

    private function efectivo_depositante_entrega_efectivo($filtered, $sub, $type)
    {
        /*Entrega de efectivo encargado*/
        if ($type == 17) {
            $filtered = $filtered->filter(function ($item) use ($sub) {
                $subData = json_decode($sub->data, true);
                /*The id of the selected user must coincide with the id of the user that
                 * created the report
                 */
                if ($subData['user_id'] == $item->report->user_id) {
                    return true;
                }

                return false;
            });
        }
        /*Efectivo depositante*/
        if ($type == 27) {
            $filtered = $filtered->filter(function ($item) use ($sub) {
                $subData = json_decode($sub->data, true);
                $itemData = json_decode($item->data, true);
                /**The id of current user created report
                 * must coincide with the id of the user that
                 * encargado selected in the subreport
                 */
                if ($sub->report->user_id == $itemData['user_id']) {
                    return true;
                }

                return false;
            });
        }

        return $this->check_if_have_matches($filtered, $sub);
    }

    /**Check Help Gestor received and sent */
    private function helpG($filtered, $sub, $type)
    {
        $filtered = $filtered->filter(function ($item) use ($sub) {
            $subData = json_decode($sub->data, true);
            if ($subData['user_id'] == $item->report->user_id) {
                //Bank from bank account should be the same
                return true;
            }

            return false;
        });

        return $this->check_if_have_matches($filtered, $sub);
    }

    /*Check Provider from Gestor type and Provider from provider */
    private function proveedor_proveedor($filtered, $sub, $type)
    {
        if ($type == 1) {
            $filtered = $filtered->filter(function ($item) use ($sub) {
                $subData = json_decode($sub->data, true);
                $itemData = json_decode($item->data, true);
                if ($subData['supplier_id'] == $item->report->user_id) {
                    if ($subData['account_id'] == $itemData['account_id']) {
                        return true;
                    }
                }

                return false;
            });
        }
        if ($type == 26) {
            $filtered = $filtered->filter(function ($item) use ($sub) {
                $subData = json_decode($sub->data, true);
                $itemData = json_decode($item->data, true);
                if ($subData['user_id'] == $item->report->user_id) {
                    if ($subData['account_id'] == $itemData['account_id']) {
                        return true;
                    }
                }

                return false;
            });
        }

        //If the filtered collection is not empty, then the subreport is consistent and should mark
        //the inconsistencies as resolved checking the filtered collection
        //from the inconsistencies table
        return $this->check_if_have_matches($filtered, $sub);
    }

    private function giro_local($filtered, $sub, $type, $subreports)
    {
        if ($type == 23) {
            $filtered = $filtered->filter(function ($item) use ($sub) {
                //The bank account from the subreport should be the same as the bank account from the
                $subData = json_decode($sub->data, true);
                $itemData = json_decode($item->data, true);
                $store = null;
                if (auth()->user()){
                    $store = auth()->user()->load('store')->store->id;
                }
                else{
                    $parent = Subreport::with('report.user.store')->find($sub->id);
                    if($parent->report->user->store){
                        $store = $parent->report->user->store->id;
                    }
                }
                if ($subData['user_id'] == $item->report->user_id && $subData['rate'] == $itemData['rate'] &&  Carbon::parse($item->created_at)->diffInHours($sub->created_at) <= 24 && $store == $itemData['store_id']) {
                   /*The bank account from the subreport should be the same as the bank account from the
                    * inconsistency
                    */ 
                   $bankAccount = BankAccount::with('bank')->find($itemData['account_id']);
                   if($bankAccount->bank->id == $subData['bank_id']){
                       return true;
                   }
                }
                
                return false;
            });

            /*Group the filtered collection by report id */
            $filtered = $filtered->groupBy('report_id');
            $filtered = $filtered->filter(function ($item) use ($sub){
                $amount = 0;
                $transferences_quantity = 0;
                $subData = json_decode($sub->data, true);
                foreach ($item as $subreport) {
                    $data = json_decode($subreport->data, true);
                    $amount += $data['amount'];
                    $transferences_quantity += $data['transferences_quantity'];
                }
                if($amount == $subData['amount'] && $transferences_quantity == $subData['transferences_quantity']){
                    return true;
                }
                return false;
            });
            $filtered = $filtered->flatten();
        }
        else if ($type == 4){
            $subData = json_decode($sub->data, true);
            $amount = 0;
            $bank = BankAccount::with('bank')->find($subData['account_id'])->bank;
            $transferences_quantity = 0;
            $subreports->filter(function ($item) use ($sub, &$amount, &$transferences_quantity, $bank){
                $subData = json_decode($sub->data, true);
                if (gettype($item->data) == 'string') {
                    $itemData = json_decode($item->data, true);
                } else {
                    $itemData = $item->data;
                }
                $store = null;

                $parent = Subreport::with('report.user.store')->find($item->id);
                if($parent->report->user->store){
                    $store = $parent->report->user->store->id;
                }
                if($subData['rate'] == $itemData['rate'] && Carbon::parse($item->created_at)->diffInHours($sub->created_at) <= 24 && $subData['store_id'] == $itemData['store_id'] && ($subData['amount'] != $itemData['amount'] || $subData['transferences_quantity'] != $itemData['transferences_quantity']) && $store == $subData['store_id']){
                    $bankAccount = BankAccount::with('bank')->find($itemData['account_id']);
                    if($bankAccount->bank->id == $bank->id){
                        $amount += $itemData['amount'];
                        $transferences_quantity += $itemData['transferences_quantity'];
                        return true;
                    }
                    
                }
                return false;
            });
            $amount += $subData['amount'];
            $transferences_quantity += $subData['transferences_quantity'];

            

            $filtered = $filtered->filter(function ($item) use ($sub, $bank, $subData, $amount, $transferences_quantity){
                $data = json_decode($item->data, true);
                $user = '';
                $store = null;
                if (auth()->user()){
                    $user = auth()->user()->id;
                }
                $parent = Subreport::with('report')->find($sub->id);
                if($parent->report->user->store){
                    $store = $parent->report->user->store->id;
                }
                if($data['amount'] == $amount && $data['transferences_quantity'] == $transferences_quantity && Carbon::parse($item->created_at)->diffInHours($sub->created_at) <= 24 && $bank->id == $data['bank_id'] && $data['rate'] == $subData['rate'] && $user == $data['user_id'] && $store == $data['store_id']){
                    return true;
                }
            });
            $filtered = $filtered->flatten();
        }
        return $this->check_if_have_matches($filtered, $sub);
    }

    public function check_inconsistences($report, $subreports)
    {

        $toCompare = Subreport::where('duplicate', false)
            ->whereDoesntHave('inconsistences', function ($query) {
                $query->where('verified', 1);
            })
            ->with('report.type', 'data')
            ->get()
            ->where('report.type.id', $report->type->associated_type_id);
        $toCompare = $this->keyValueMap->transformElement($toCompare);
        //Transform the data to json
        //bc before it works with json data
        //and now it works with key value
        foreach ($toCompare as $key => $value) {
            $toCompare[$key]->data = json_encode($value->data);
        }

        foreach ($subreports as $sub) {
            //if the subreport is duplicated, then skip it
            $sub->data = json_encode($sub->data);
            //Filter the subreports that have the same currency and amount
            if($report->type->id != 4 && $report->type->id != 23){
                $filtered = $toCompare->filter(function ($value, $key) use ($sub) {

                    $valueData = json_decode($value->data, true);
                    $subData = json_decode($sub->data, true);
                    if ($valueData['currency_id'] === $subData['currency_id'] && $valueData['amount'] === $subData['amount'] && Carbon::parse($value->created_at)->diffInHours($sub->created_at) <= 24){
                        return true;
                    }
    
                    return false;
                });
            }else{
                $filtered = $toCompare;
            }
            $this->invoke($filtered, $sub, $report->type->id, $subreports);
        }
    }

    private function check_if_have_matches($filtered, $sub)
    {

        if ($filtered->isEmpty()) {
            $inconsistence = Inconsistence::create([
                'subreport_id' => $sub->id,
            ]);
            if (! $inconsistence->id) {
                throw new \Exception('Error creating the inconsistency');
            }

            return false;
        }
        $count = $filtered->count();
        $filtered->each(function ($item) use ($sub, $count) {
            /*If the filtered collection is not empty, then the subreport is consistent*/
            $inconsistence = Inconsistence::where('subreport_id', $item->id)->latest('created_at')->first();

            if($count > 1 && $inconsistence){
                $inconsistence->delete();
                $inconsistence = null;
            }

            if ($inconsistence) {
                if ($inconsistence->associated_id == null && $inconsistence->associated_id != $sub->id) {
                    $inconsistence->associated_id = $sub->id;
                    $inconsistence->save();
                } 
                else if ($inconsistence->associated_id != $sub->id){
                   $inconsistence= Inconsistence::create([
                        'subreport_id' => $sub->id,
                        'associated_id' => $item->id,
                    ]);
                }
            }
            else{
                $inconsistence = Inconsistence::create([
                    'subreport_id' => $sub->id,
                    'associated_id' => $item->id,
                ]);
            }
        });
        return true;
    }
}
