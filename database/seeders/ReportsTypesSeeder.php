<?php

namespace Database\Seeders;

use App\Models\ReportType;
use Illuminate\Database\Seeder;

class ReportsTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reportTypes = [
            [
                'id' => 1,
                'name' => 'Proovedor',
                'description' => null,
                'type' => 'income',
                'config' => '{"styles":{"color":"#052C65","borderColor":"#9EC5FE","backgroundColor":"#E7F1FF"}}',
                'delete' => 0,
                'created_at' => '2023-11-09 06:21:08',
                'updated_at' => '2023-11-09 06:21:08',
                'country' => 0,
                'meta_data' => '{"name":"Proovedor"}',
            ],
            [
                'id' => 2,
                'name' => 'Ayuda recibida',
                'description' => null,
                'type' => 'income',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2023-11-09 06:21:08',
                'updated_at' => '2023-11-09 06:21:08',
                'country' => 0,
                'meta_data' => '{"name":"Ayuda recibida venezuela"}',
            ],
            [
                'id' => 3,
                'name' => 'Billetera',
                'description' => null,
                'type' => 'income',
                'config' => '{"styles":{"color":"#290661","borderColor":"#C29FFA","backgroundColor":"#E0CFFC"}}',
                'delete' => 0,
                'created_at' => '2023-11-09 06:21:08',
                'updated_at' => '2023-11-09 06:21:08',
                'country' => 0,
                'meta_data' => '{"name":"Billetera"}',
            ],
            [
                'id' => 4,
                'name' => 'Local',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#561435","borderColor":"#EFADCE","backgroundColor":"#F7D6E6"}}',
                'delete' => 0,
                'created_at' => '2023-11-09 06:21:08',
                'updated_at' => '2023-11-09 06:21:08',
                'country' => 0,
                'meta_data' => '{"name":"Locales","convert_amount":true}',
            ],
            [
                'id' => 5,
                'name' => 'Billetera',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#653208","borderColor":"#FECBA1","backgroundColor":"#FFE5D0"}}',
                'delete' => 0,
                'created_at' => '2023-11-09 06:21:08',
                'updated_at' => '2023-11-09 06:21:08',
                'country' => 0,
                'meta_data' => '{"name":"Billetera"}',
            ],
            [
                'id' => 6,
                'name' => 'Ayuda Realizada',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#0A3622","borderColor":"#A3CFBB","backgroundColor":"#D1E7DD"}}',
                'delete' => 0,
                'created_at' => '2023-11-09 06:21:08',
                'updated_at' => '2023-11-09 06:21:08',
                'country' => 0,
                'meta_data' => '{"name":"Ayuda Realizada"}',
            ],
            [
                'id' => 7,
                'name' => 'Traspasos',
                'description' => null,
                'type' => 'income',
                'config' => '{"styles":{"color":"#58151C","borderColor":"#F1AEB5","backgroundColor":"#F8D7DA"}}',
                'delete' => 0,
                'created_at' => '2023-11-09 06:21:08',
                'updated_at' => '2023-11-09 06:21:08',
                'country' => 0,
                'meta_data' => '{"name":"Traspasos"}',
            ],
            [
                'id' => 8,
                'name' => 'Recargas',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#343A40","borderColor":"#DEE2E6","backgroundColor":"#E9ECEF"}}',
                'delete' => 0,
                'created_at' => '2023-11-09 06:21:08',
                'updated_at' => '2023-11-09 06:21:08',
                'country' => 0,
                'meta_data' => '{"name":"Recargas"}',
            ],
            [
                'id' => 9,
                'name' => 'Comisiones',
                'description' => 'Ingreso de billeter',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#58151C","borderColor":"#F1AEB5","backgroundColor":"#F8D7DA"}}',
                'delete' => 0,
                'created_at' => '2023-11-09 21:44:33',
                'updated_at' => '2023-11-10 00:48:52',
                'country' => 0,
                'meta_data' => '{"name":"Comisiones"}',
            ],
            [
                'id' => 10,
                'name' => 'Créditos',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#052C65","borderColor":"#9EC5FE","backgroundColor":"#E7F1FF"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:05:24',
                'updated_at' => '2023-12-24 12:05:24',
                'country' => 0,
                'meta_data' => '{"name":"Créditos"}',
            ],
            [
                'id' => 11,
                'name' => 'Otros',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#052C65","borderColor":"#9EC5FE","backgroundColor":"#E7F1FF"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:10:52',
                'updated_at' => '2023-12-24 12:10:52',
                'country' => 0,
                'meta_data' => '{"name":"Otros"}',
            ],
            [
                'id' => 12,
                'name' => 'Cuenta Billetera',
                'description' => 'Cuenta billetera, de gestores de locales',
                'type' => 'income',
                'config' => '{"styles":{"color":"#290661","borderColor":"#C29FFA","backgroundColor":"#E0CFFC"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:11:45',
                'updated_at' => '2023-12-24 12:11:45',
                'country' => 1,
                'meta_data' => '{"name":"Cuenta Billetera","type":"2"}',
            ],
            [
                'id' => 13,
                'name' => 'Efectivo',
                'description' => null,
                'type' => 'income',
                'config' => '{"styles":{"color":"#052C65","borderColor":"#9EC5FE","backgroundColor":"#E7F1FF"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:12:30',
                'updated_at' => '2023-12-24 12:12:30',
                'country' => 1,
                'meta_data' => '{"name":"Efectivo","type":"2"}',
            ],
            [
                'id' => 14,
                'name' => 'Transferencia',
                'description' => null,
                'type' => 'income',
                'config' => '{"styles":{"color":"#0A3622","borderColor":"#A3CFBB","backgroundColor":"#D1E7DD"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:12:38',
                'updated_at' => '2023-12-24 12:12:38',
                'country' => 1,
                'meta_data' => '{"name":"Transferencia","type":"2"}',
            ],
            [
                'id' => 15,
                'name' => 'Ayuda Recibida',
                'description' => null,
                'type' => 'income',
                'config' => '{"styles":{"color":"#343A40","borderColor":"#DEE2E6","backgroundColor":"#E9ECEF"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:12:50',
                'updated_at' => '2023-12-24 12:12:50',
                'country' => 1,
                'meta_data' => '{"name":"Ayuda","type":"2"}',
            ],
            [
                'id' => 16,
                'name' => 'Traspaso',
                'description' => null,
                'type' => 'income',
                'config' => '{"styles":{"color":"#343A40","borderColor":"#DEE2E6","backgroundColor":"#E9ECEF"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:12:55',
                'updated_at' => '2023-12-24 12:12:55',
                'country' => 1,
                'meta_data' => '{"name":"Traspaso","type":"2"}',
            ],
            [
                'id' => 17,
                'name' => 'Entrega Efectivo',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#290661","borderColor":"#C29FFA","backgroundColor":"#E0CFFC"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:14:31',
                'updated_at' => '2023-12-24 12:14:31',
                'country' => 1,
                'meta_data' => '{"name":"Entrega Efectivo","type":"2"}',
            ],
            [
                'id' => 18,
                'name' => 'Cuenta billetera (Efectivo)',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#58151C","borderColor":"#F1AEB5","backgroundColor":"#F8D7DA"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:14:47',
                'updated_at' => '2023-12-24 12:14:47',
                'country' => 1,
                'meta_data' => '{"name":"Cuenta Billetera (Efectivo)","type":"2"}',
            ],
            [
                'id' => 19,
                'name' => 'Comisiones',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#290661","borderColor":"#C29FFA","backgroundColor":"#E0CFFC"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:15:22',
                'updated_at' => '2023-12-24 12:15:22',
                'country' => 1,
                'meta_data' => '{"name":"Comisiones","type":2}',
            ],
            [
                'id' => 20,
                'name' => 'Créditos',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#052C65","borderColor":"#9EC5FE","backgroundColor":"#E7F1FF"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:15:31',
                'updated_at' => '2023-12-24 12:15:31',
                'country' => 1,
                'meta_data' => '{"name":"Créditos","type":2}',
            ],
            [
                'id' => 21,
                'name' => 'Otros',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#343A40","borderColor":"#DEE2E6","backgroundColor":"#E9ECEF"}}',
                'delete' => 0,
                'created_at' => '2023-12-24 12:15:47',
                'updated_at' => '2023-12-24 12:15:47',
                'country' => 1,
                'meta_data' => '{"name":"Otros","type":2}',
            ],
            [
                'id' => 22,
                'name' => 'Billetera',
                'description' => null,
                'type' => 'neutro',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2023-12-25 09:01:19',
                'updated_at' => '2023-12-25 09:01:19',
                'country' => 1,
                'meta_data' => '{"name":"Billetera","type":"1","admin":[{"name":"country_id","validation":"required|exists:countries,id"}],"special":[{"name":"country_id","validation":"required|exists:countries,id"}]}',
            ],
            [
                'id' => 23,
                'name' => 'Giros',
                'description' => null,
                'type' => 'neutro',
                'config' => '{"styles":{"color":"#290661","borderColor":"#C29FFA","backgroundColor":"#E0CFFC"}}',
                'delete' => 0,
                'created_at' => '2023-12-25 09:53:03',
                'updated_at' => '2023-12-25 09:53:03',
                'country' => 0,
                'meta_data' => '{"name":"Giros","type":"1","admin":[{"name":"country_id","validation":"required|exists:countries,id"}],"special":[{"name":"country_id","validation":"required|exists:countries,id"}]}',
            ],
            /**This is a duplicated record, but it must be here. Don't delete it */
            [
                'id' => 24,
                'name' => 'cuenta billetera',
                'description' => 'Cuenta billetera, de gestores de locales',
                'type' => 'income',
                'config' => '{"styles":{"color":"#58151C","borderColor":"#F1AEB5","backgroundColor":"#F8D7DA"}}',
                'delete' => 1,
                'created_at' => '2023-12-27 16:43:14',
                'updated_at' => '2023-12-27 16:43:14',
                'country' => 1,
                'meta_data' => '{"name":"Cuenta Billetera","type":"2"}',
            ],
            [
                'id' => 25,
                'name' => 'Ayuda realizada',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2024-01-03 04:31:31',
                'updated_at' => '2024-01-03 04:31:31',
                'country' => 1,
                'meta_data' => '{"name":"Efectivo Depositante","type":"2"}',
            ],
            [
                'id' => 26,
                'name' => 'Proovedor',
                'description' => 'Egreso realizado por el proveedor',
                'type' => 'neutro',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2024-01-03 05:10:04',
                'updated_at' => '2024-01-03 05:10:04',
                'country' => 1,
                'meta_data' => '{"name":"Proovedor","type":3}',
            ],
            [
                'id' => 27,
                'name' => 'Efectivo',
                'description' => 'Efectivo depositante',
                'type' => 'income',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2024-01-07 07:10:28',
                'updated_at' => '2024-01-07 07:10:28',
                'country' => 1,
                'meta_data' => '{"name":"Efectivo depositante","type":4,"user_balance":true}',
            ],
            [
                'id' => 28,
                'name' => 'Billetera',
                'description' => 'Billetera Depositante',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2024-01-07 07:10:28',
                'updated_at' => '2024-01-07 07:10:28',
                'country' => 1,
                'meta_data' => '{"name":"Billetera depositante","type":4,"user_balance":true}',
            ],
            [
                'id' => 29,
                'name' => 'Proveedor',
                'description' => 'Provedor depositante',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2024-01-07 07:21:15',
                'updated_at' => '2024-01-07 07:21:15',
                'country' => 1,
                'meta_data' => '{"name":"Proveedor","type":4,"user_balance":true}',
            ],
            [
                'id' => 30,
                'name' => 'Entrega',
                'description' => 'Entrega depositante',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2024-01-07 07:21:15',
                'updated_at' => '2024-01-07 07:21:15',
                'country' => 1,
                'meta_data' => '{"name":"Entrega","type":4,"user_balance":true}',
            ],
            [
                'id' => 31,
                'name' => 'Efectivo',
                'description' => 'Efecivo ingreso caja fuerte',
                'type' => 'income',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2024-01-07 07:21:15',
                'updated_at' => '2024-01-07 07:21:15',
                'country' => 1,
                'meta_data' => '{"name":"Efectivo caja fuerte","type":5, "user_balance":true}',
            ],
            [
                'id' => 32,
                'name' => 'Efectivo',
                'description' => 'Efectivo Egreso caja fuerte',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2024-01-07 07:21:15',
                'updated_at' => '2024-01-07 07:21:15',
                'country' => 1,
                'meta_data' => '{"name":"Efectivo egreso caja fuerte","type":5 ,"user_balance":true}',
            ],
            [
                'id' => 33,
                'name' => 'Entrega',
                'description' => 'Entrega caja fuerte',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#343A40","borderColor":"#DEE2E6","backgroundColor":"#E9ECEF"}}',
                'delete' => 0,
                'created_at' => '2024-01-07 07:21:15',
                'updated_at' => '2024-01-07 07:21:15',
                'country' => 1,
                'meta_data' => '{"name":"Entrega","type":5 ,"user_balance":true}',
            ],
            [
                'id' => 34,
                'name' => 'Depósitos',
                'description' => 'depositos ',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2024-01-07 07:51:13',
                'updated_at' => '2024-01-07 07:51:13',
                'country' => 1,
                'meta_data' => '{"name":"Billetera depositante","type":2}',
            ],
            [
                'id' => 35,
                'name' => 'Transferencia',
                'description' => 'Transferencia encargado de local',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}',
                'delete' => 0,
                'created_at' => '2024-01-07 07:51:13',
                'updated_at' => '2024-01-07 07:51:13',
                'country' => 1,
                'meta_data' => '{"name":"Billetera depositante","type":2}',
            ],
            [
                'id' => 36,
                'name' => 'Cuenta Billetera (Transferencia)',
                'description' => '',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#561435","borderColor":"#EFADCE","backgroundColor":"#F7D6E6"}}',
                'delete' => 0,
                'created_at' => '2024-01-17 04:18:57',
                'updated_at' => '2024-01-17 04:18:57',
                'country' => 1,
                'meta_data' => '{"name":"Cuenta Billetera (Transferencia)","type":"2"}',
            ],
            [
                'id' => 37,
                'name' => 'Nómina',
                'description' => '',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#561435","borderColor":"#EFADCE","backgroundColor":"#F7D6E6"}}',
                'delete' => 0,
                'created_at' => '2024-01-17 04:18:57',
                'updated_at' => '2024-01-17 04:18:57',
                'country' => 1,
                'meta_data' => '{"name":"Nómina","type":"2"}',
            ],
            [
                'id' => 38,
                'name' => 'Comisiones por Giros',
                'description' => '',
                'type' => 'income',
                'config' => '{"styles":{"color":"#561435","borderColor":"#EFADCE","backgroundColor":"#F7D6E6"}}',
                'delete' => 0,
                'created_at' => '2024-01-17 04:18:57',
                'updated_at' => '2024-01-17 04:18:57',
                'country' => 1,
                'meta_data' => '{"name":"Income","type":"2"}',
            ],
            [
                'id' => 39,
                'name' => 'Billetera Jefe',
                'description' => '',
                'type' => 'income',
                'config' => '{"styles":{"color":"#561435","borderColor":"#EFADCE","backgroundColor":"#F7D6E6"}}',
                'delete' => 0,
                'created_at' => '2024-01-17 04:18:57',
                'updated_at' => '2024-01-17 04:18:57',
                'country' => 1,
                'meta_data' => '{"name":"Income","type":"1"}',
            ],
            [
                'id' => 40,
                'name' => 'Billetera Cliente (Transferencia)',
                'description' => '',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#561435","borderColor":"#EFADCE","backgroundColor":"#F7D6E6"}}',
                'delete' => 0,
                'created_at' => '2024-01-17 04:18:57',
                'updated_at' => '2024-01-17 04:18:57',
                'country' => 1,
                'meta_data' => '{"name":"Income","type":"1"}',
            ],
            [
                'id' => 41,
                'name' => 'Billetera Cliente (Efectivo)',
                'description' => '',
                'type' => 'expense',
                'config' => '{"styles":{"color":"#561435","borderColor":"#EFADCE","backgroundColor":"#F7D6E6"}}',
                'delete' => 0,
                'created_at' => '2024-01-17 04:18:57',
                'updated_at' => '2024-01-17 04:18:57',
                'country' => 1,
                'meta_data' => '{"name":"Income","type":"1"}',
            ],
            [
                'id' => 42,
                'name' => 'Billetera Cliente (Transferencia)',
                'description' => '',
                'type' => 'income',
                'config' => '{"styles":{"color":"#561435","borderColor":"#EFADCE","backgroundColor":"#F7D6E6"}}',
                'delete' => 0,
                'created_at' => '2024-01-17 04:18:57',
                'updated_at' => '2024-01-17 04:18:57',
                'country' => 1,
                'meta_data' => '{"name":"Billetera Cliente (Transferencia)","type":"1"}',
            ],
            [
                'id' => 43,
                'name' => 'Billetera Cliente (Efectivo)',
                'description' => '',
                'type' => 'income',
                'config' => '{"styles":{"color":"#561435","borderColor":"#EFADCE","backgroundColor":"#F7D6E6"}}',
                'delete' => 0,
                'created_at' => '2024-01-17 04:18:57',
                'updated_at' => '2024-01-17 04:18:57',
                'country' => 1,
                'meta_data' => '{"name":"Billetera Cliente (Efectivo)","type":"1"}',
            ]
        ];
        ReportType::insert($reportTypes);
        $this->update();
    }

    private function update(): void
    {
        /**
         * Proveedor - Proveedor
         */
        $reportType = ReportType::find(1);
        $reportType->associated_type_id = 26;
        $reportType->save();

        $reportType = ReportType::find(26);
        $reportType->associated_type_id = 1;
        $reportType->save();

        /*
         * Giro - Local
         */
        $reportType = ReportType::find(4);
        $reportType->associated_type_id = 23;
        $reportType->save();

        $reportType = ReportType::find(23);
        $reportType->associated_type_id = 4;
        $reportType->save();

        /*Ayuda Realizada Gestor - Ayuda Recibida Gestor*/
        $reportType = ReportType::find(6);
        $reportType->associated_type_id = 15;
        $reportType->save();

        $reportType = ReportType::find(15);
        $reportType->associated_type_id = 6;
        $reportType->save();

        /*Entrega Efectivo - Efectivo*/
        $reportType = ReportType::find(17);
        $reportType->associated_type_id = 27;
        $reportType->save();

        $reportType = ReportType::find(27);
        $reportType->associated_type_id = 17;
        $reportType->save();

        /*Ayuda realizada local - Ayuda recibida local*/
        $reportType = ReportType::find(25);
        $reportType->associated_type_id = 15;
        $reportType->save();

        $reportType = ReportType::find(15);
        $reportType->associated_type_id = 25;
        $reportType->save();
    }
}
