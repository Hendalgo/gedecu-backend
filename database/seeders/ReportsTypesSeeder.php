<?php

namespace Database\Seeders;

use App\Models\ReportType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportsTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $peticion = new ReportType();

        $peticion->name = 'Petición transferencia';
        $peticion->config = json_encode([
            'styles' => [
                'borderColor' => '#9EC5FE',
                'backgroundColor' => '#E7F1FF',
                'color' => '#052C65'
            ]
        ]);

        $peticion->save();

        $transfer_send = new ReportType();

        $transfer_send->name = 'Transferencia enviada';
        $transfer_send->config = json_encode([
            'styles' => [
                'borderColor' => '#9EEAF9',
                'backgroundColor' => '#CFF4FC',
                'color' => '#055160'
            ]
        ]);
        $transfer_send->save();

        $caja =  new ReportType();
        $caja->name = 'Caja fuerte';
        $caja->config = json_encode([
            'styles' => [
                'borderColor' => '#C29FFA',
                'backgroundColor' => '#E0CFFC',
                'color' => '#290661'
            ]
        ]);

        $caja->save();

        $deposit = new ReportType();
        $deposit->name = 'Depositante';
        $deposit->config = json_encode([
            'styles' => [
                'borderColor' => '#EFADCE',
                'backgroundColor' => '#F7D6E6',
                'color' => '#561435'
            ] 
        ]);

        $deposit->save();

        $corresponsal = new ReportType();
        $corresponsal->name = 'Corresponsal';
        $corresponsal->config = json_encode([
            'styles' => [
                'borderColor' => '#FECBA1',
                'backgroundColor' => '#FFE5D0',
                'color' => '#653208'
            ] 
        ]);

        $corresponsal->save();

        $ingreso = new ReportType();
        $ingreso->name = 'Ingreso';
        $ingreso->config = json_encode([
            'styles' => [
                'borderColor' => '#A3CFBB',
                'backgroundColor' => '#D1E7DD',
                'color' => '#0A3622'
            ] 
        ]);

        $ingreso->save();

        $egreso = new ReportType();
        $egreso->name = 'Egreso';
        $egreso->config = json_encode([
            'styles' => [
                'borderColor' => '#F1AEB5',
                'backgroundColor' => '#F8D7DA',
                'color' => '#58151C'
            ] 
        ]);

        $egreso->save();

        $nomina = new ReportType();
        $nomina->name = 'Nomina';
        $nomina->config = json_encode([
            'styles' => [
                'borderColor' => '#DEE2E6',
                'backgroundColor' => '#E9ECEF',
                'color' => '#343A40'
            ] 
        ]);

        $nomina->save();
    }
}
