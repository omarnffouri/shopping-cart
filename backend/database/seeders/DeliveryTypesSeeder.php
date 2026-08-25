<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define delivery types and their slots
        $deliveryTypes = [
            'MORNING' => [
                'type_name' => json_encode([
                    'en' => 'Morning Delivery',
                    'tr' => 'Sabah Teslimatı',
                    'hi' => 'सुबह की डिलीवरी',
                    'ar' => 'توصيل الصباح',
                    'fr' => 'Livraison du Matin',
                ]),
                'price' => 59.00,
                'min_hours_advance' => 10,
                'available_for_today' => false,
                'cutoff_time' => '14:00:00',
                'display_order' => 1,
                'slots' => [
                    [
                        'slot_name' =>  '05:00 - 07:00 Hrs',
                        'start_time' => '05:00:00',
                        'end_time' => '07:00:00',
                        'available_if_before' => null,
                        'display_order' => 1
                    ],
                    [
                        'slot_name' => '07:00 - 09:00 Hrs',
                        'start_time' => '07:00:00',
                        'end_time' => '09:00:00',
                        'available_if_before' => null,
                        'display_order' => 2
                    ],
                ],
            ],
            'STANDARD' => [
                'type_name' => json_encode([
                    'en' => 'Standard Delivery',
                    'tr' => 'Standart Teslimat',
                    'hi' => 'मानक डिलीवरी',
                    'ar' => 'التوصيل القياسي',
                    'fr' => 'Livraison Standard',
                ]),
                'price' => 39.00,
                'min_hours_advance' => 0,
                'available_for_today' => true,
                'cutoff_time' => '20:00:00',
                'display_order' => 2,
                'slots' => [
                    [
                        'slot_name' => '05:00 - 09:00 Hrs',
                        'start_time' => '05:00:00',
                        'end_time' => '09:00:00',
                        'available_if_before' => '06:00:00',
                        'display_order' => 1
                    ],
                    [
                        'slot_name' => '09:00 - 13:00 Hrs',
                        'start_time' => '09:00:00',
                        'end_time' => '13:00:00',
                        'available_if_before' => '10:00:00',
                        'display_order' => 2
                    ],
                    [
                        'slot_name' => '12:00 - 17:00 Hrs',
                        'start_time' => '12:00:00',
                        'end_time' => '17:00:00',
                        'available_if_before' => '15:00:00',
                        'display_order' => 3
                    ],
                    [
                        'slot_name' => '14:00 - 19:00 Hrs',
                        'start_time' => '14:00:00',
                        'end_time' => '19:00:00',
                        'available_if_before' => '17:00:00',
                        'display_order' => 4
                    ],
                    [
                        'slot_name' => '16:00 - 21:00 Hrs',
                        'start_time' => '16:00:00',
                        'end_time' => '21:00:00',
                        'available_if_before' => '19:00:00',
                        'display_order' => 5
                    ],
                    [
                        'slot_name' => '19:00 - 23:00 Hrs',
                        'start_time' => '19:00:00',
                        'end_time' => '23:00:00',
                        'available_if_before' => '20:00:00',
                        'display_order' => 6
                    ],
                ],
            ],
            'FIXED_TIME' => [
                'type_name' => json_encode([
                    'en' => 'Fixed Time Delivery',
                    'tr' => 'Sabit Zamanlı Teslimat',
                    'hi' => 'निश्चित समय डिलीवरी',
                    'ar' => 'التوصيل في وقت محدد',
                    'fr' => 'Livraison à Heure Fixe',
                ]),
                'price' => 49.00,
                'min_hours_advance' => 0,
                'available_for_today' => true,
                'cutoff_time' => '20:00:00',
                'display_order' => 3,
                'slots' => [
                    [
                        'slot_name' => '09:00 - 11:00 Hrs',
                        'start_time' => '9:00:00',
                        'end_time' => '11:00:00',
                        'available_if_before' => '09:00:00',
                        'display_order' => 1
                    ],
                    [
                        'slot_name' => '11:00 - 13:00 Hrs',
                        'start_time' => '11:00:00',
                        'end_time' => '13:00:00',
                        'available_if_before' => '10:00:00',
                        'display_order' => 2
                    ],
                    [
                        'slot_name' => '13:00 - 15:00 Hrs',
                        'start_time' => '13:00:00',
                        'end_time' => '15:00:00',
                        'available_if_before' => '13:00:00',
                        'display_order' => 3
                    ],
                    [
                        'slot_name' => '15:00 - 17:00 Hrs',
                        'start_time' => '15:00:00',
                        'end_time' => '17:00:00',
                        'available_if_before' => '14:00:00',
                        'display_order' => 4
                    ],
                    [
                        'slot_name' => '17:00 - 19:00 Hrs',
                        'start_time' => '17:00:00',
                        'end_time' => '19:00:00',
                        'available_if_before' => '16:00:00',
                        'display_order' => 5
                    ],
                    [
                        'slot_name' => '19:00 - 21:00 Hrs',
                        'start_time' => '19:00:00',
                        'end_time' => '21:00:00',
                        'available_if_before' => '18:00:00',
                        'display_order' => 6
                    ],
                    [
                        'slot_name' => '21:00 - 23:00 Hrs',
                        'start_time' => '21:00:00',
                        'end_time' => '23:00:00',
                        'available_if_before' => '20:00:00',
                        'display_order' => 7
                    ],
                ],
            ],
            'MIDNIGHT' => [
                'type_name' => json_encode([
                    'en' => 'Midnight Delivery',
                    'tr' => 'Gece Yarısı Teslimatı',
                    'hi' => 'मध्यरात्रि डिलीवरी',
                    'ar' => 'توصيل منتصف الليل',
                    'fr' => 'Livraison de Minuit',
                ]),
                'price' => 79.00,
                'min_hours_advance' => 0,
                'available_for_today' => true,
                'cutoff_time' => null,
                'display_order' => 4,
                'slots' => [
                    [
                        'slot_name' => '23:00 - 23:59 Hrs',
                        'start_time' => '23:00:00',
                        'end_time' => '23:59:00',
                        'available_if_before' => null,
                        'display_order' => 1
                    ],
                ],
            ],
        ];

        foreach ($deliveryTypes as $typeCode => $data) {
            $deliveryTypeId = DB::table('delivery_types')
                ->updateOrInsert(
                    ['type_code' => $typeCode],
                    [
                        'type_name' => $data['type_name'],
                        'price' => $data['price'],
                        'min_hours_advance' => $data['min_hours_advance'],
                        'available_for_today' => $data['available_for_today'],
                        'cutoff_time' => $data['cutoff_time'],
                        'display_order' => $data['display_order'],
                        'country' => 'AE',
                        'state' => 'DU',
                        'updated_at' => now(),
                    ]
                );

            $deliveryTypeId = DB::table('delivery_types')->where('type_code', $typeCode)->value('id');

            // 2️⃣ Insert slots if they don’t exist
            foreach ($data['slots'] as $slot) {
                DB::table('time_slots')->updateOrInsert(
                    [
                        'delivery_type_id' => $deliveryTypeId,
                        'slot_name' => $slot['slot_name'],
                    ],
                    [
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'available_if_before' => $slot['available_if_before'],
                        'display_order' => $slot['display_order'],
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
