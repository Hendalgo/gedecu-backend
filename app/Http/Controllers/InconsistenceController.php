<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Inconsistence;
use App\Models\Report;
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
        $date = $request->input('date');
        $per_page = $request->input('per_page', 10);
        $paginate = $request->input('paginate', 'yes');
        $order_by = $request->input('order', 'created_at');
        $order = $request->input('order_by', 'desc');

        $subreports = Report::with([
            'type',
            'user.store',
            'subreports' => function ($query) {
                $query->with([
                    'data',
                    'inconsistences' => function ($query) {
                        $query->whereNull('associated_id'); // Ignora las inconsistencias asociadas
                    },
                ]);
            },
        ])
            ->whereHas('subreports', function ($query) {
                $query->whereExists(function ($query) {
                    $query->select('*')
                        ->from('inconsistences')
                        ->whereColumn('inconsistences.subreport_id', 'subreports.id')
                        ->where('inconsistences.verified', 0)
                        ->whereNull('inconsistences.associated_id');
                });
            });

        if ($search) {
            $subreports = $subreports->where(function ($query) use ($search) {
                $query->whereHas('subreports', function ($query) use ($search) {
                    $query->whereHas('data', function ($query) use ($search) {
                        $query->where('value', 'like', '%'.$search.'%');
                    });
                })->orWhereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%');
                })->orWhereHas('type', function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%');
                })->orWhere('created_at', 'like', '%'.$search.'%');
            });
        }

        if ($since) {
            $subreports = $subreports->whereHas('subreports', function ($query) use ($since) {
                $query->where('subreports.created_at', '>=', $since);
            });
        }
        if ($until) {
            $subreports = $subreports->whereHas('subreports', function ($query) use ($until) {
                $query->where('subreports.created_at', '<=', $until);
            });
        }
        if ($date) {
            $subreports = $subreports->whereHas('subreports', function ($query) use ($date) {
                $query->whereDate('subreports.created_at', $date);
            });
        }

        $subreports = $subreports->orderBy($order_by, $order);
        if ($paginate == 'no') {
            $subreports = $subreports->get();
            $subreports->each(function ($report) {
                $report->subreports->each(function ($subreport) {
                    $subreport = $this->keyValueMap->transformElement($subreport);
                });
            });
        } else {
            $subreports = $subreports->paginate($per_page);
            $subreports->each(function ($report) {
                $report->subreports->each(function ($subreport) {
                    $subreport = $this->keyValueMap->transformElement($subreport);
                });
            });
        }

        return response()->json($subreports);
    }

    public function invoke($filtered, $sub, $type, $subreports)
    {
        /**Grouped */
        if ($type == 1 || $type == 26) {
            $this->proveedor_proveedor($filtered, $sub, $type, $subreports);
        }
        /*Grouped */
        if ($type == 2 || $type == 6) {
            $this->helpG($filtered, $sub, $type, $subreports);
        }
        /**Done group */
        if ($type == 23 || $type == 4) {
            $this->giro_local($filtered, $sub, $type, $subreports);
        }
        /**Grouped */
        if ($type == 17 || $type == 27) {
            $this->efectivo_depositante_entrega_efectivo($filtered, $sub, $type, $subreports);
        }
        /*Done group */
        if ($type == 15 || $type == 25) {
            $this->ayuda_recibida_local_ayuda_realizada_local($filtered, $sub, $type, $subreports);
        }
    }

    /*Grouped */
    private function ayuda_recibida_local_ayuda_realizada_local($filtered, $sub, $type, $subreport)
    {
        $amount = 0;
        $subData = json_decode($sub->data, true);

        //Get siblings of the current subreport
        $subreport = $subreport->filter(function ($item) use ($sub, &$amount) {
            $subData = json_decode($sub->data, true);
            if (gettype($item->data) == 'string') {
                $itemData = json_decode($item->data, true);
            } else {
                $itemData = $item->data;
            }

            if ($subData['store_id'] == $itemData['store_id'] && Carbon::parse($item->created_at)->diffInHours($sub->created_at) <= 24 && $sub->id != $item->id) {
                $amount += $itemData['amount'];
            }
        });

        $amount += $subData['amount'];

        $filtered = $filtered->filter(function ($item) use ($sub) {
            $itemData = json_decode($item->data, true);
            if ($itemData['store_id'] == $sub->report->user->store->id) {
                return true;
            }

            return false;
        });

        //group the filtered collection by report id
        $filtered = $filtered->groupBy('report_id');

        $filtered = $filtered->filter(function ($item) use ($sub, $amount) {
            $amountLocal = 0;
            $subData = json_decode($sub->data, true);
            foreach ($item as $subreport) {
                $data = json_decode($subreport->data, true);
                $amountLocal += $data['amount'];
            }
            if ($amountLocal == $amount) {
                return true;
            }

            return false;
        });

        $filtered = $filtered->flatten();

        return $this->check_if_have_matches($filtered, $sub);
    }

    private function efectivo_depositante_entrega_efectivo($filtered, $sub, $type, $subreports)
    {
        /*Entrega de efectivo encargado*/
        if ($type == 17) {

            $amount = 0;
            $subData = json_decode($sub->data, true);

            //Get siblings of the current subreport
            $subreports = $subreports->filter(function ($item) use ($sub, &$amount) {
                $subData = json_decode($sub->data, true);
                if (gettype($item->data) == 'string') {
                    $itemData = json_decode($item->data, true);
                } else {
                    $itemData = $item->data;
                }

                if ($subData['user_id'] == $itemData['user_id'] && Carbon::parse($item->created_at)->diffInHours($sub->created_at) <= 24 && $sub->id != $item->id) {
                    $amount += $itemData['amount'];
                }
            });

            $amount += $subData['amount'];

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

            //group the filtered collection by report id
            $filtered = $filtered->groupBy('report_id');

            $filtered = $filtered->filter(function ($item) use ($sub, $amount) {
                $amountLocal = 0;
                $subData = json_decode($sub->data, true);
                foreach ($item as $subreport) {
                    $data = json_decode($subreport->data, true);
                    $amountLocal += $data['amount'];
                }
                if ($amountLocal == $amount) {
                    return true;
                }

                return false;
            });

            $filtered = $filtered->flatten();
        }
        /*Efectivo (Renamed to Entrega de local) depositante*/
        if ($type == 27) {
            $amount = 0;
            $subData = json_decode($sub->data, true);

            //Get siblings of the current subreport

            $subreports = $subreports->filter(function ($item) use ($sub, &$amount) {
                $subData = json_decode($sub->data, true);
                if (gettype($item->data) == 'string') {
                    $itemData = json_decode($item->data, true);
                } else {
                    $itemData = $item->data;
                }

                if ($subData['store_id'] == $itemData['store_id'] && Carbon::parse($item->created_at)->diffInHours($sub->created_at) <= 24 && $sub->id != $item->id) {
                    $amount += $itemData['amount'];
                }
            });

            $amount += $subData['amount'];

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

            //group the filtered collection by report id
            $filtered = $filtered->groupBy('report_id');

            $filtered = $filtered->filter(function ($item) use ($sub, $amount) {
                $amountLocal = 0;
                $subData = json_decode($sub->data, true);
                foreach ($item as $subreport) {
                    $data = json_decode($subreport->data, true);
                    $amountLocal += $data['amount'];
                }
                if ($amountLocal == $amount) {
                    return true;
                }

                return false;
            });

            $filtered = $filtered->flatten();
        }

        return $this->check_if_have_matches($filtered, $sub);
    }

    /**Check Help Gestor received and sent */
    private function helpG($filtered, $sub, $type, $subreports)
    {
        $amount = 0;
        $subData = json_decode($sub->data, true);
        //Get siblings of the current subreport
        $subreports = $subreports->filter(function ($item) use ($sub, &$amount) {
            $subData = json_decode($sub->data, true);
            if (gettype($item->data) == 'string') {
                $itemData = json_decode($item->data, true);
            } else {
                $itemData = $item->data;
            }

            if ($subData['user_id'] == $itemData['user_id'] && Carbon::parse($item->created_at)->diffInHours($sub->created_at) <= 24 && $sub->id != $item->id) {
                $amount += $itemData['amount'];
            }
        });

        $amount += $subData['amount'];

        $filtered = $filtered->filter(function ($item) use ($sub) {
            $subData = json_decode($sub->data, true);
            if ($subData['user_id'] == $item->report->user_id) {
                return true;
            }

            return false;
        });

        //group the filtered collection by report id
        $filtered = $filtered->groupBy('report_id');

        $filtered = $filtered->filter(function ($item) use ($sub, $amount) {
            $amountLocal = 0;
            $subData = json_decode($sub->data, true);
            foreach ($item as $subreport) {
                $data = json_decode($subreport->data, true);
                $amountLocal += $data['amount'];
            }
            if ($amountLocal == $amount) {
                return true;
            }

            return false;
        });

        $filtered = $filtered->flatten();

        return $this->check_if_have_matches($filtered, $sub);
    }

    /*Check Provider from Gestor type and Provider from provider */
    private function proveedor_proveedor($filtered, $sub, $type, $subreports)
    {
        if ($type == 1) {
            $amount = 0;
            $subData = json_decode($sub->data, true);

            //Get siblings of the current subreport
            $subreports = $subreports->filter(function ($item) use ($sub, &$amount) {
                $subData = json_decode($sub->data, true);
                if (gettype($item->data) == 'string') {
                    $itemData = json_decode($item->data, true);
                } else {
                    $itemData = $item->data;
                }

                if ($subData['supplier_id'] == $itemData['supplier_id'] && Carbon::parse($item->created_at)->diffInHours($sub->created_at) <= 24 && $sub->id != $item->id && $subData['account_id'] == $itemData['account_id']) {
                    $amount += $itemData['amount'];
                }
            });

            $amount += $subData['amount'];

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
            //group the filtered collection by report id
            $filtered = $filtered->groupBy('report_id');
            $filtered = $filtered->filter(function ($item) use ($sub, $amount) {
                $amountLocal = 0;
                $subData = json_decode($sub->data, true);
                foreach ($item as $subreport) {
                    $data = json_decode($subreport->data, true);
                    $amountLocal += $data['amount'];
                }
                if ($amountLocal == $amount) {
                    return true;
                }

                return false;
            });
            $filtered = $filtered->flatten();
        }
        if ($type == 26) {
            $amount = 0;
            $subData = json_decode($sub->data, true);

            //Get siblings of the current subreport
            $subreports = $subreports->filter(function ($item) use ($sub, &$amount) {
                $subData = json_decode($sub->data, true);
                if (gettype($item->data) == 'string') {
                    $itemData = json_decode($item->data, true);
                } else {
                    $itemData = $item->data;
                }

                if ($subData['user_id'] == $itemData['user_id'] && Carbon::parse($item->created_at)->diffInHours($sub->created_at) <= 24 && $sub->id != $item->id && $subData['account_id'] == $itemData['account_id']) {
                    $amount += $itemData['amount'];
                }
            });

            $amount += $subData['amount'];

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

            $filtered = $filtered->groupBy('report_id');
            $filtered = $filtered->filter(function ($item) use ($sub, $amount) {
                $amountLocal = 0;
                $subData = json_decode($sub->data, true);
                foreach ($item as $subreport) {
                    $data = json_decode($subreport->data, true);
                    $amountLocal += $data['amount'];
                }
                if ($amountLocal == $amount) {
                    return true;
                }

                return false;
            });

            $filtered = $filtered->flatten();
        }

        //If the filtered collection is not empty, then the subreport is consistent and should mark
        //the inconsistencies as resolved checking the filtered collection
        //from the inconsistencies table
        return $this->check_if_have_matches($filtered, $sub);
    }

    /**Done group */
    private function giro_local($filtered, $sub, $type, $subreports)
    {
        try{
            $amount = 0;
            $transferences_quantity = 0;
            $subData = json_decode($sub->data, true);

            //Get siblings of the current subreport
            $subreports = $subreports->filter(function ($item) use ($sub, &$amount, &$transferences_quantity, $type) {
                $subData = json_decode($sub->data, true);
                if (gettype($item->data) == 'string') {
                    $itemData = json_decode($item->data, true);
                } else {
                    $itemData = $item->data;
                }

                if ($type == 23) {
                    if ($subData['user_id'] != $itemData['user_id'] || $subData['bank_id'] != $itemData['bank_id']) {
                        return false;
                    }
                } else {
                    if ($subData['store_id'] != $itemData['store_id'] || $subData['bank_id'] != $itemData['bank_id']) {
                        return false;
                    }
                }

                if ($subData['rate'] == $itemData['rate'] && Carbon::parse($item->created_at)->diffInHours($sub->created_at) <= 24 && $sub->id != $item->id) {
                    $amount += $itemData['amount'];
                    $transferences_quantity += $itemData['transferences_quantity'];
                }
            });

            $amount += $subData['amount'];
            $transferences_quantity += $subData['transferences_quantity'];

            //Start to filter the possible matches

            $filtered = $filtered->filter(function ($item) use ($sub, $type) {
                //The bank account from the subreport should be the same as the bank account from the
                $subData = json_decode($sub->data, true);
                $itemData = json_decode($item->data, true);
                $store = $subData['store_id'];
                $bank_id = null;
                if (array_key_exists('bank_id', $itemData)) {
                    $bank_id = $itemData['bank_id'];
                }
                if ($type == 23) {
                    if ($subData['user_id'] != $item->report->user_id && $store != $itemData['store_id']) {
                        return false;
                    }
                } else {
                    if (auth()->user()) {
                        $user = auth()->user()->id;
                    } else {
                        $user = Subreport::with('report.user')->find($sub->id)->report->user->id;
                    }
                    if ($user != $itemData['user_id'] && $subData['store_id'] != $store) {
                        return false;
                    }
                }
                if ($subData['rate'] == $itemData['rate'] && Carbon::parse($item->created_at)->diffInHours($sub->created_at) <= 24 && $subData['bank_id'] == $bank_id && $store == $itemData['store_id']) {
                    return true;
                }

                return false;
            });

            /*Group the filtered collection by report id */
            $filtered = $filtered->groupBy('report_id');

            $filtered = $filtered->filter(function ($item) use ($sub, $amount, $transferences_quantity) {
                $amountLocal = 0;
                $transferences_quantityLocal = 0;
                $subData = json_decode($sub->data, true);
                foreach ($item as $subreport) {
                    $data = json_decode($subreport->data, true);
                    $amountLocal += $data['amount'];
                    $transferences_quantityLocal += $data['transferences_quantity'];
                }
                if ($amountLocal == $amount && $transferences_quantityLocal == $transferences_quantity) {
                    return true;
                }

                return false;
            });

            $filtered = $filtered->flatten();

            return $this->check_if_have_matches($filtered, $sub);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function check_inconsistences($report, $subreports)
    {

        $toCompare = Subreport::where('duplicate', false)
            ->whereDoesntHave('inconsistences', function ($query) {
                $query->where('verified', 1);
            })
            ->whereDoesntHave('inconsistences', function ($query) {
                $query->whereNotNull('associated_id');
            })
            ->get();
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
            $inconsistence = Inconsistence::where('subreport_id', $item->id)->where(function ($query) use ($sub) {
                $query->where('associated_id', $sub->id)->orWhere('associated_id', null);
            })->latest('created_at')->first();

            if ($count > 1 && $inconsistence) {
                $inconsistence->delete();
                $inconsistence = null;
            }

            if ($inconsistence) {
                $inconsistence->associated_id = $sub->id;
                $inconsistence->verified = 1;
                $inconsistence->save();
            } else {
                $inconsistence = Inconsistence::create([
                    'subreport_id' => $sub->id,
                    'associated_id' => $item->id,
                    'verified' => 1,
                ]);
            }
        });

        return true;
    }
}
