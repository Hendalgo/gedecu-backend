<?php

namespace App\Http\Controllers;

use App\Models\Inconsistence;
use App\Models\Subreport;
use App\Services\KeyValueMap;
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
        $inconsistence = Inconsistence::find($id);
        if (! $inconsistence) {
            return response()->json(['error' => 'Inconsistence not found'], 404);
        }
        $inconsistence->verified = 1;
        $inconsistence->save();

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
        $verified = $request->input('verified');

        //Get subreports that are not verified on the inconsistencies table
        $subreports = Subreport::join('inconsistences', 'subreports.id', '=', 'inconsistences.subreport_id')
            ->select('subreports.*')
            ->with('report.type', 'data', 'report.user.store');


        //If the date is set, then filter the subreports by date
        if ($date) {
            $subreports = $subreports->whereDate('subreports.created_at', $date);
        }
        if ($type) {
            $subreports = $subreports->whereHas('report.type', function ($query) use ($type) {
                $query->where('id', $type);
            });
        }
        if ($verified === 'yes') {
            $subreports = $subreports->where('inconsistences.verified', 1);
        }
        if ($verified === 'no') {
            $subreports = $subreports->where('inconsistences.verified', 0);
        }
        if ($search) {
            $subreports = $subreports->when($search, function ($query, $search) {
                return $query->where('data', 'like', '%'.$search.'%')
                    ->orWhereHas('report.user.store', function ($query) use ($search) {
                        $query->where('name', 'like', '%'.$search.'%')
                            ->orWhere('location', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('report.user', function ($query) use ($search) {
                        $query->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }
        if ($since && $until) {
            $subreports = $subreports->whereBetween('subreports.created_at', [$since, $until]);
        }

        if ($paginate == 'yes') {
            $subreports = $subreports->paginate($per_page);
        } else {
            $subreports = $subreports->get();
        }
        return response()->json($subreports);
    }

    public function invoke($filtered, $sub, $type)
    {
        if ($type == 1 || $type == 26) {
            $this->proveedor_proveedor($filtered, $sub, $type);
        }
        if ($type == 2 || $type == 6) {
            $this->helpG($filtered, $sub, $type);
        }
        if ($type == 23 || $type == 4) {
            $this->giro_local($filtered, $sub, $type);
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

    private function giro_local($filtered, $sub, $type)
    {
        if ($type == 23) {
            $filtered = $filtered->filter(function ($item) use ($sub) {
                $subData = json_decode($sub->data, true);
                if ($subData['user_id'] == $item->report->user_id) {
                    return true;
                }

                return false;
            });
        }

        $filtered = $filtered->filter(function ($item) use ($sub) {
            $itemData = json_decode($item->data, true);
            $subData = json_decode($sub->data, true);
            if ($subData['transferences_quantity'] == $itemData['transferences_quantity']) {
                if ($subData['rate'] == $itemData['rate']) {
                    return true;
                }
            }

            return false;
        });

        return $this->check_if_have_matches($filtered, $sub);
    }

    public function check_inconsistences($report, $subreports)
    {

        $toCompare = Subreport::where('duplicate', false)
            ->whereDoesntHave('inconsistences', function ($query) {
                $query->where('verified', 1);
            })
            ->whereBetween('created_at', [$report->created_at->subDay(), $report->created_at])
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
            if ($sub->duplicate) {
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
        $filtered->each(function ($item) {
            $inconsistence = Inconsistence::where('subreport_id', $item->id)->latest('created_at')->first();
            if ($inconsistence) {
                $inconsistence->verified = 1;
                $inconsistence->save();
            }
        });

        return true;
    }
}
