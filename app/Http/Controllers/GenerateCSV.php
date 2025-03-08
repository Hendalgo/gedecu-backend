<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Subreport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class GenerateCSV extends Controller
{
    public function generateCSV()
    {
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Array para almacenar los datos agrupados por mes
        $data = [];

        // Procesar los reportes en lotes de 100 (puedes ajustar el tamaño del lote)
        Report::where('type_id', 47)
            ->with('subreports.data') // Cargar subreportes relacionados
            ->chunk(100, function ($reports) use (&$data) {
                foreach ($reports as $report) {
                    foreach ($report->subreports as $subreport) {
                        $date = Carbon::parse($subreport->created_at);
                        $monthKey = $date->format('Y-m'); // Formato: Año-Mes (ej. 2023-01)

                        foreach ($subreport->data as $dataItem) {
                            if ($dataItem->key == 'amount') {
                                if (!isset($data[$monthKey])) {
                                    $data[$monthKey] = [
                                        'year' => $date->format('Y'),
                                        'month' => $date->format('m'),
                                        'month_name' => $date->format('F'),
                                        'total_amount' => 0,
                                        'currency' => '', // Inicializar la moneda
                                    ];
                                }
                                $data[$monthKey]['total_amount'] += $dataItem->value;
                            }
                        }
                    }
                }
            });

        // Ordenar los datos por mes (clave Y-m)
        ksort($data);

        // Convertir los datos procesados a un array para el CSV
        $csvData = [];
        $csvData[] = ['Año', 'Mes', 'Nombre del Mes', 'Monto Total' ]; // Encabezados del CSV

        foreach ($data as $row) {
            // Formatear el monto con el formato 000.000.000,00
            $formattedAmount = number_format($row['total_amount'], 2, ',', '.');

            // Agregar la moneda al final del monto
            $formattedAmountWithCurrency = $formattedAmount . ' ' . $row['currency'];

            $csvData[] = [
                $row['year'],
                $row['month'],
                $row['month_name'],
                'VES '.$formattedAmountWithCurrency, // Monto formateado con la moneda
            ];
        }

        // Generar el archivo CSV
        $filename = 'reportes_suma_por_mes_' . date('Ymd_His') . '.csv';
        $handle = fopen($filename, 'w+');

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        // Descargar el archivo CSV
        return Response::download($filename)->deleteFileAfterSend(true);
    }
}