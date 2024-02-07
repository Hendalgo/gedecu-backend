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
                'meta_data' => '{"all":[{"name":"supplier_id","validation":"required|exists:users,id|user_role:4"},{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"currency_id","validation":"required|exists:currencies,id"},{"name":"reference","validation":"required"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Proovedor"}'
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
                'meta_data' => '{"all":[{"name":"user_id","validation":"required|exists:users,id|user_role:2"},{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"reference","validation":"required"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Ayuda recibida venezuela"}'
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
                'meta_data' => '{"all":[{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"transferences_quantity","validation":"required|numeric"},{"name":"amount","validation":"required|numeric"},{"name":"currency_id","validation":"required|exists:currencies,id"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Billetera"}'
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
                'meta_data' => '{"all":[{"name":"store_id","validation":"required|exists:stores,id"},{"name":"transferences_quantity","validation":"required|numeric"},{"name":"amount","validation":"required|numeric"},{"name":"rate","validation":"required|numeric"},{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Locales","convert_amount":true}'
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
                'meta_data' => '{"all":[{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"transferences_quantity","validation":"required|numeric"},{"name":"amount","validation":"required|numeric"},{"name":"currency_id","validation":"required|exists:currencies,id"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Billetera"}'
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
                'meta_data' => '{"all":[{"name":"user_id","validation":"required|exists:users,id|user_role:2"},{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Ayuda Realizada"}'
            ],
            [
                'id' => 7,
                'name' => 'Traspasos',
                'description' => null,
                'type' => 'expense',
                'config' => '{"styles":{"color":"#58151C","borderColor":"#F1AEB5","backgroundColor":"#F8D7DA"}}',
                'delete' => 0,
                'created_at' => '2023-11-09 06:21:08',
                'updated_at' => '2023-11-09 06:21:08',
                'country' => 0,
                'meta_data' => '{"all":[{"name":"senderAccount_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"receiverAccount_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Traspasos"}'
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
                'meta_data' => '{"all":[{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Recargas"}'
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
                'meta_data' => '{"all":[{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Comisiones"}'
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
                'meta_data' => '{"all":[{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Créditos"}'
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
                'meta_data' => '{"all":[{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"motive","validation":"required"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Otros"}'
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
                'meta_data' => '{"all":[{"name":"transferences_quantity","validation":"required|numeric"},{"name":"amount","validation":"required|numeric"},{"name":"rate","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Cuenta Billetera","type":"2"}'
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
                'meta_data' => '{"all":[{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Efectivo","type":"2"}'
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
                'meta_data' => '{"all":[{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Transferencia","type":"2"}'
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
                'meta_data' => '{"all":[{"name":"store_id","validation":"required|exists:stores,id"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Ayuda","type":"2"}'
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
                'meta_data' => '{"all":[{"name":"senderAccount_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"receiverAccount_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Traspaso","type":"2"}'
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
                'meta_data' => '{"all":[{"name":"user_id","validation":"required|exists:users,id"},{"name":"amount","validation":"required|numeric"}],"name":"Entrega Efectivo","type":"2"}'
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
                'meta_data' => '{"all":[{"name":"currency_id","validation":"required|exists:currencies,id"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Cuenta Billetera (Efectivo)","type":"2"}'
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
                'meta_data' => '{"all":[{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Comisiones","type":2}'
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
                'meta_data' => '{"all":[{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Créditos","type":2}'
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
                'meta_data' => '{"all":[{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"amount","validation":"required|numeric"},{"name":"motive","validation":"required"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Otros","type":2}'
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
                'meta_data' => '{"all":[{"name":"transferences_quantity","validation":"required|numeric"},{"name":"currency_id","validation":"required|exists:currencies,id"},{"name":"amount","validation":"required|numeric"},{"name":"rate","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Billetera","type":"1","admin":[{"name":"country_id","validation":"required|exists:countries,id"}],"special":[{"name":"country_id","validation":"required|exists:countries,id"}]}'
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
                'meta_data' => '{"all":[{"name":"bank_id","validation":"required|exists:banks,id"},{"name":"currency_id","validation":"required|numeric"},{"name":"transferences_quantity","validation":"required|numeric"},{"name":"user_id","validation":"required|exists:users,id|user_role:2"},{"name":"amount","validation":"required|numeric"},{"name":"rate","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Giros","type":"1","admin":[{"name":"country_id","validation":"required|exists:countries,id"}],"special":[{"name":"country_id","validation":"required|exists:countries,id"}]}'
            ],
            [
                'id' => 24,
                'name' => 'cuenta billetera',
                'description' => null,
                'type' => 'income',
                'config' => '{"styles":{"color":"#58151C","borderColor":"#F1AEB5","backgroundColor":"#F8D7DA"}}',
                'delete' => 0,
                'created_at' => '2023-12-27 16:43:14',
                'updated_at' => '2023-12-27 16:43:14',
                'country' => 1,
                'meta_data' => '{"all":[{"name":"transferences_quantity","validation":"required|numeric"},{"name":"amount","validation":"required|numeric"},{"name":"rate","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Cuenta Billetera","type":"2"}'
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
                'meta_data' => '{"all":[{"name":"store_id","validation":"required|exists:stores,id"},{"name":"account_id","validation":"required|exists:banks_accounts,id"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Efectivo Depositante","type":"2"}'
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
                'meta_data' => '{"all":[{"name":"user_id","validation":"required|exists:users,id|user_role:2"},{"name":"account_id","validation":"required|exists:banks_accounts,id"},{"name":"amount","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Proovedor","type":3}'
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
                'meta_data' => '{"all":[{"name":"amount","validation":"required|numeric"},{"name":"store_id","validation":"required|exists:stores,id"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Efectivo depositante","type":4,"user_balance":true}'
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
                'meta_data' => '{"all":[{"name":"amount","validation":"required|numeric"},{"name":"user_id","validation":"required|exists:users,id"},{"name":"isDuplicated","validation":"required|boolean"},{"name":"deposits_quantity","validation":"required|numeric"},{"name":"rate","validation":"required|numeric"}],"name":"Billetera depositante","type":4,"user_balance":true,"convert_amount":true}'
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
                'meta_data' => '{"all":[{"name":"supplier_id","validation":"required|exists:users,id|user_role:4"},{"name":"amount","validation":"required|numeric"},{"name":"currency_id","validation":"required|exists:currencies,id"},{"name":"deposits_quantity","validation":"required|numeric"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Proveedor","type":4,"user_balance":true}'
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
                'meta_data' => '{"all":[{"name":"supplier_id","validation":"required|exists:users,id|user_role:6"},{"name":"amount","validation":"required|numeric"},{"name":"currency_id","validation":"required|exists:currencies,id"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Entrega","type":4,"user_balance":true}'
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
                'meta_data' => '{"all":[{"name":"store_id","validation":"required|exists:stores,id"},{"name":"amount","validation":"required|numeric"},{"name":"currency_id","validation":"required|exists:currencies,id"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Efectivo caja fuerte","type":5, "user_balance":true}'
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
                'meta_data' => '{"all":[{"name":"amount","validation":"required|numeric"},{"name":"store_id","validation":"required|exists:stores,id"},{"name":"isDuplicated","validation":"required|boolean|is_false"},{"name":"deposits_quantity","validation":"required|numeric"},{"name":"currency_id","validation":"required|exists:currencies,id"}],"name":"Efectivo egreso caja fuerte","type":5 ,"user_balance":true}'
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
                'meta_data' => '{"all":[{"name":"supplier_id","validation":"required|exists:users,id|user_role:4"},{"name":"amount","validation":"required|numeric"},{"name":"currency_id","validation":"required|exists:currencies,id"},{"name":"isDuplicated","validation":"required|boolean|is_false"}],"name":"Entrega","type":5 ,"user_balance":true}'
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
                'meta_data' => '{"all":[{"name":"amount","validation":"required|numeric"},{"name":"user_id","validation":"required|exists:users,id"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Billetera depositante","type":2}'
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
                'meta_data' => '{"all":[{"name":"amount","validation":"required|numeric"},{"name":"user_id","validation":"required|exists:users,id"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Billetera depositante","type":2}'
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
                'meta_data' => '{"all":[{"name":"currency_id","validation":"required|exists:currencies,id"},{"name":"amount","validation":"required|numeric"},{"name":"account_id","validation":"required|exists:banks_accounts,id|bank_account_owner"},{"name":"isDuplicated","validation":"required|boolean"}],"name":"Cuenta Billetera (Transferencia)","type":"2"}'
            ]
        ];
        ReportType::insert($reportTypes);
    }
}
