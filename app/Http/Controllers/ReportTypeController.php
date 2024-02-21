<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use App\Models\User;
use Illuminate\Http\Request;

class ReportTypeController extends Controller
{
    public function index(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $search = $request->get('search');
        $paginated = $request->get('paginated');
        $reports = ReportType::withCount(['reports as count'])->where('delete', false);
        $reports->when($search, function ($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
        });
        $reports = $reports->leftJoin('roles_reports_permissions', 'reports_types.id', '=', 'roles_reports_permissions.report_type_id')
            ->where('roles_reports_permissions.role_id', $user->role->id);
        if ($paginated) {
            if ($paginated === 'no') {
                return response()->json($reports->get(), 200);
            }
        }

        return response()->json($reports->paginate(10), 200);
    }

    public function store(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {

            $messages = [
                'name.required' => 'El nombre es un campo requerido',
                'type.required' => 'Tipo es requerido',
                'type.in' => 'Tipo invalido',
            ];
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:income,expense,neutro',
                'country' => 'required|boolean',
                'meta_data' => 'required|JSON',
            ], $messages);

            $report_type_config = ReportType::inRandomOrder()->first()->config;

            $validatedData['config'] = $report_type_config;

            $report_type = ReportType::create($validatedData);

            if ($report_type) {
                return response()->json($report_type, 201);
            } else {
                return response()->json(['error' => 'Hubo un problema al crear el tipo reporte'], 500);
            }
        }

        return response()->json(['message' => 'forbiden'], 401);
    }

    public function update(Request $request, $id)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $messages = [
                'name.required' => 'El nombre es un campo requerido',
                'type.required' => 'Tipo es requerido',
                'type.in' => 'Tipo invalido',
            ];
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'description' => 'string',
                'type' => 'required|in:income,expense,neutro',
            ], $messages);

            $report_type = ReportType::find($id);

            $report_type->name = $validatedData['name'];
            $report_type->description = $validatedData['description'];
            $report_type->type = $validatedData['type'];

            if ($report_type->save()) {
                return response()->json(['message' => 'exito'], 201);
            } else {
                return response()->json(['error' => 'Hubo un problema al crear el tipo reporte'], 500);
            }
        }

        return response()->json(['message' => 'forbiden'], 401);
    }

    public function delete($id)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {

            $report_type = ReportType::find($id);
            $report_type->delete = true;
            if ($report_type->save()) {
                return response()->json(['message' => 'exito'], 201);
            } else {
                return response()->json(['error' => 'Hubo un problema al crear el tipo reporte'], 500);
            }
        }

        return response()->json(['message' => 'forbiden'], 401);
    }
}
