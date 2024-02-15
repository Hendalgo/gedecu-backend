<?php

namespace App\Http\Controllers;

use App\Models\Inconsistence;
use App\Models\Subreport;
use Illuminate\Http\Request;

class InconsistenceController extends Controller
{
    public function index(Request $request){
        //Get subreports that are not verified on the inconsistencies table
        $subreports = Subreport::whereDoesntHave('inconsistences', function($query){
            $query->where('verified', 0);
        })->get();

        return response()->json($subreports);
    }
    public function invoke($filtered, $sub, $type){
        if($type == 1 || $type == 26){
            $this->proveedor_proveedor($filtered, $sub, $type);
        }
    }
    private function local_giro(){
        //...
        //...   
        //...
        //...
    }
    private function proveedor_proveedor($filtered, $sub, $type){
        if($type == 1){
            $filtered = $filtered->filter(function($item) use ($sub){
                if($sub['supplier_id'] == $item->user_id){
                    if($sub['account_id'] == $item->account_id){
                        return true;
                    }
                }
                return false;
            });

            //If the filtered collection is empty, then the subreport is inconsistent
            if($filtered->isEmpty()){
                $inconsistence = Inconsistence::create([
                    'subreport_id' => $sub->id,
                ]);
                if(!$inconsistence->id){
                    throw new \Exception('Error creating the inconsistency');
                }
            }
            //If the filtered collection is not empty, then the subreport is consistent and should mark
            //the inconsistencies as resolved checking the filtered collection
            //from the inconsistencies table
            $filtered->each(function($item) use ($sub){
                $inconsistence = Inconsistence::where($item->id);
                if($inconsistence){
                    $inconsistence->verified = 1;
                    $inconsistence->save();
                }
            });
            return true;
        }
        if($type == 26){
            $filtered = $filtered->filter(function($item) use ($sub){
                if($sub['user_id'] == $item->user_id){
                    if($sub['account_id'] == $item->account_id){
                        return true;
                    }
                }
                return false;
            });

            //If the filtered collection is empty, then the subreport is inconsistent
            if($filtered->isEmpty()){
                $inconsistence = Inconsistence::create([
                    'subreport_id' => $sub->id,
                ]);
                if(!$inconsistence->id){
                    throw new \Exception('Error creating the inconsistency');
                }
            }
            //If the filtered collection is not empty, then the subreport is consistent and should mark
            //the inconsistencies as resolved checking the filtered collection
            //from the inconsistencies table
            $filtered->each(function($item) use ($sub){
                $inconsistence = Inconsistence::where($item->id);
                if($inconsistence){
                    $inconsistence->verified = 1;
                    $inconsistence->save();
                }
            });
            return true;
        }

        throw new \Exception('Invalid type');
    }
}
