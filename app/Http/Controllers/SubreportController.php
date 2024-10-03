<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubreportController extends Controller
{
    public function validate_subreport(Request $request)
    {
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
            $validator->setRules(['date' => 'required|date|date_format:Y-m-d']);
            //Get the validations for the role all
            $reportValidations = $report_type->validations->where('validation_role', 'all')->toArray();

            //Get the validations for the role
            $reportValidationsRole = $report_type->validations->where('validation_role', auth()->user()->role->id)->toArray();
            if (array_key_exists('convert_amount', $report_type_config)) {
                $validator->setRules(['conversionCurrency_id' => 'required|exists:currencies,id']);
            }


            foreach ($reportValidations as $validation) {
                echo $validation['name'];
                $validator->setRules([$validation['name'] => $validation['validation']]);
                if ($validator->fails()) {
                    $errorMessages = $validator->errors()->all();
                    
                    return response()->json(['error' => 'Error de validación en el subreporte', 'validation_errors' => $errorMessages], 422);
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

        return $validatedSubreports;
    }

    public function validate_without_request($request)
    {
        Validator::make($request, [
            'subreports' => 'required|array',
        ])->validate();
        $subreports = $request['subreports'];
        $report_type = ReportType::with(['validations'])->find($request['type_id']);
        $report_type_config = json_decode($report_type->meta_data, true);

        $validator = Validator::make([], []); // Create a empty intance of validator
        $validatedSubreports = [];

        // Validate all fields in the subreports
        foreach ($subreports as $subreport) {
            $validator->setData($subreport);
            $validator->setRules(['currency_id' => 'required|exists:currencies,id']);

            // Validate date
            $validator->setRules(['date' => 'required|date|date_format:Y-m-d']);
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

        return $validatedSubreports;

    }
}
