<?php

namespace App\Http\Controllers;

use App\Models\Inconsistence;
use App\Models\Subreport;
use Illuminate\Http\Request;

class InconsistenceController extends Controller
{
    public function index(Request $request){
        //Get subreports that are not verified on the inconsistencies table
        $subreports = Subreport::join('inconsistences', 'subreports.id', '=', 'inconsistences.subreport_id')
            ->select('subreports.*')
            ->where('inconsistences.verified', 0)
            ->with('report.type')
            ->get();
        return response()->json($subreports);
    }
    public function invoke($filtered, $sub, $type){
        if($type == 1 || $type == 26){
            $this->proveedor_proveedor($filtered, $sub, $type);
        }
        if($type == 2 || $type == 6){
            $this->helpG($filtered, $sub, $type);
        }
        if($type == 23 || $type == 4){
            $this->giro_local($filtered, $sub, $type);
        }
        if($type == 17 || $type == 27){
            $this->efectivo_depositante_entrega_efectivo($filtered, $sub, $type);
        }
        if($type == 15 || $type == 25){
            $this->ayuda_recibida_local_ayuda_realizada_local($filtered, $sub, $type);
        }
    }

    private function ayuda_recibida_local_ayuda_realizada_local($filtered, $sub, $type){
        $filtered = $filtered->filter(function($item) use ($sub){
            $itemData = json_decode($item->data, true);
            if($itemData['store_id'] == $sub->report->user->store->id){
                return true;
            }
            return false;
        });
        return $this->check_if_have_matches($filtered, $sub);
    }
    private function efectivo_depositante_entrega_efectivo($filtered, $sub, $type){
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
        if ($type == 27){
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
    private function helpG($filtered, $sub, $type){
        $filtered = $filtered->filter(function($item) use ($sub){
            $subData = json_decode($sub->data, true);
            if($subData['user_id'] == $item->report->user_id){
                //Bank from bank account should be the same
                return true;
            }
            return false;
        });
        return $this->check_if_have_matches($filtered, $sub);
    }
    /*Check Provider from Gestor type and Provider from provider */
    private function proveedor_proveedor($filtered, $sub, $type){
        if($type == 1){
            $filtered = $filtered->filter(function($item) use ($sub){
                $subData = json_decode($sub->data, true);
                $itemData = json_decode($item->data, true);
                if($subData['supplier_id'] == $item->report->user_id){
                    if($subData['account_id'] == $itemData['account_id']){
                        return true;
                    }
                }
                return false;
            });   
        }
        if($type == 26){
            $filtered = $filtered->filter(function($item) use ($sub){
                $subData = json_decode($sub->data, true);
                $itemData = json_decode($item->data, true);
                if($subData['user_id'] == $item->report->user_id){
                    if($subData['account_id'] == $itemData['account_id']){
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
    private function giro_local($filtered, $sub, $type){
        if($type == 23){
            $filtered = $filtered->filter(function($item) use ($sub){
                $subData = json_decode($sub->data, true);
                if($subData['user_id'] == $item->report->user_id){
                    return true;
                }
                return false;
            });
        }

        $filtered = $filtered->filter(function($item) use ($sub){
            $itemData = json_decode($item->data, true);
            $subData = json_decode($sub->data, true);
            if($subData['transferences_quantity'] == $itemData['transferences_quantity']){
                if($subData['rate'] == $itemData['rate']){
                    return true;
                }
            }
            return false;
        });
        return $this->check_if_have_matches($filtered, $sub);
    }
    public function check_inconsistences($report, $subreports){
        
        $toCompare = Subreport::
            where('duplicate', false)
            ->whereDoesntHave('inconsistences', function($query){
                $query->where('verified', 1);
            })
            ->whereBetween('created_at', [$report->created_at->subDay(), $report->created_at])
            ->with('report.type')
            ->get()
            ->where('report.type.id', $report->type->associated_type_id)
            ;
            
        foreach($subreports as $sub){
            //if the subreport is duplicated, then skip it
            if($sub->duplicated){
                continue;
            }
            //Filter the subreports that have the same currency and amount
            $filtered = $toCompare->filter(function ($value, $key) use ($sub) {
                $valueData = json_decode($value->data, true);
                $subData = json_decode($sub->data, true);
            
                if ($valueData['currency_id'] === $subData['currency_id'] && $valueData['amount'] === $subData['amount']) {
                    return true;
                }
                return false;
            });
            $this->invoke($filtered, $sub, $report->type->id);
        }
    }
    
    private function check_if_have_matches($filtered, $sub){
        
        if($filtered->isEmpty()){
            $inconsistence = Inconsistence::create([
                'subreport_id' => $sub->id,
            ]);
            if(!$inconsistence->id){
                throw new \Exception('Error creating the inconsistency');
            }
            return false;
        }
        $filtered->each(function($item) use ($sub){
            $inconsistence = Inconsistence::where('subreport_id', $item->id)->latest('created_at')->first();
            if($inconsistence){
                $inconsistence->verified = 1;
                $inconsistence->save();
            }
        });
        return true;
    }
}
