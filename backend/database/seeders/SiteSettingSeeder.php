<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Setting;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $metaTitle = "Online Shopping for Men, Electronics, Apparel, Computers, Books, DVDs & more";
        $metaDescription = "USA's #1 shopping platform for baby & kids essentials, toys, fashion & electronic items, and more! Lowest Price Guaranteed | Cashback Deals";

        $items = [
            [
                'id' => 1,
                'site_name' => 'Íshop',
                'site_url' => 'https://givvo.com',
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'header_logo' => 'header-logo.svg',
                'footer_logo' => 'footer-logo.svg',
                'email_logo' => 'email-logo.png',
                'copyright_text' => 'All rights reserved by Givvo',
                'admin_id' => 1
            ]
        ];


        $admin = Admin::where('id', 1)->first();


        if(!SiteSetting::first() && $admin){
            foreach ($items as $i) {
                SiteSetting::create($i);
            }
        }


    }
}
