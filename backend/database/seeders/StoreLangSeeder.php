<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\StoreLang;
use Illuminate\Database\Seeder;

class StoreLangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'store_id' => 1,
                'name' => 'Givvo Büyük alışveriş merkezi | Givvo',
                'meta_title' => 'Givvo Büyük alışveriş merkezi | Givvo',
                'meta_description' => "Şimdi Givvo Mega Mall ile çevrimiçi alışveriş yapın! Givvo'ta Givvo Mega Mall'u ziyaret edin.",
                'lang' => 'tr'
            ],

            [
                'store_id' => 2,
                'name' => 'Jshop',
                'meta_title' => 'Jshop Büyük alışveriş merkezi | Jshop',
                'meta_description' => "Şimdi Jshop Mega Mall ile çevrimiçi alışveriş yapın! Jshop'ta Jshop Mega Mall'u ziyaret edin.",
                'lang' => 'tr'
            ],


            [
                'store_id' => 1,
                'name' => 'Givvo',
                'meta_title' => 'Givvo मेगा मॉल | Givvo',
                'meta_description' => 'ईशॉप मेगा मॉल के साथ अभी ऑनलाइन खरीदारी करें! ईशॉप पर ईशॉप मेगा मॉल जाएँ।',
                'lang' => 'hi'
            ],

            [
                'store_id' => 2,
                'name' => 'Jshop',
                'meta_title' => 'Jshop मेगा मॉल | Jshop',
                'meta_description' => 'जेशॉप मेगा मॉल के साथ अभी ऑनलाइन खरीदारी करें! Jshop पर Jshop मेगा मॉल पर जाएँ।',
                'lang' => 'hi'
            ],


            [
                'store_id' => 1,
                'name' => 'Givvo',
                'meta_title' => 'Givvo Méga centre commercial | Givvo',
                'meta_description' => 'Achetez en ligne avec Givvo Mega Mall maintenant ! Visitez Givvo Mega Mall sur Givvo.',
                'lang' => 'fr'
            ],

            [
                'store_id' => 2,
                'name' => 'Jshop',
                'meta_title' => 'Jshop Méga centre commercial | Jshop',
                'meta_description' => 'Achetez en ligne avec Jshop Mega Mall maintenant ! Visitez Jshop Mega Mall sur Jshop.',
                'lang' => 'fr'
            ],


            [
                'store_id' => 1,
                'name' => 'Givvo',
                'meta_title' => 'Givvo ميجا مول | Givvo',
                'meta_description' => 'تسوق عبر الإنترنت مع Givvo Mega Mall الآن! قم بزيارة Ishop Mega Mall في Ishop.',
                'lang' => 'ar'
            ],

            [
                'store_id' => 2,
                'name' => 'Jshop',
                'meta_title' => 'Jshop ميجا مول | Jshop',
                'meta_description' => 'تسوق عبر الإنترنت مع Jshop Mega Mall الآن! قم بزيارة Jshop Mega Mall في Jshop.',
                'lang' => 'ar'
            ]
        ];


        $store1 = Store::where('id', 1)->first();
        $store2 = Store::where('id', 2)->first();

        if(!StoreLang::first() && $store1 && $store2){
            foreach ($items as $i) {
                StoreLang::create($i);
            }
        }
    }
}
