<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $faq = file_get_contents(base_path() . "/database/seeders/pageData/faq.ini");
        $about = file_get_contents(base_path() . "/database/seeders/pageData/about.ini");
        $refundPolicy = file_get_contents(base_path() . "/database/seeders/pageData/refundPolicy.ini");
        $privacyPolicy = file_get_contents(base_path() . "/database/seeders/pageData/privacyPolicy.ini");
        $help = file_get_contents(base_path() . "/database/seeders/pageData/help.ini");

        $items = [
            [
                'id' => 1,
                'title' => 'About',
                'description' => $about,
                'slug' => 'about',
                'meta_title' => 'Givvo - About',
                'meta_description' => 'About',
                'admin_id' => 1
            ],
            [
                'id' => 2,
                'title' => 'Faq',
                'description' => $faq,

                'slug' => 'faq',
                'meta_title' => 'Givvo - Faq',
                'meta_description' => 'Faq',
                'admin_id' => 1
            ],
            [
                'id' => 3,
                'title' => 'Contact',
                'description' => 'Contact',
                'slug' => 'contact',
                'meta_title' => 'Givvo - Contact',
                'page_from_component' => 1,
                'meta_description' => 'Contact',
                'admin_id' => 1
            ],

            [
                'id' => 4,
                'title' => 'Refund Policy',
                'description' => $refundPolicy,
                'slug' => 'refund-policy',
                'meta_title' => 'Givvo - Refund Policy',
                'meta_description' => 'Refund Policy',
                'admin_id' => 1
            ],
            [
                'id' => 5,
                'title' => 'Privacy Policy',
                'description' => $privacyPolicy,
                'slug' => 'privacy-policy',
                'meta_title' => 'Givvo - Privacy Policy',
                'meta_description' => 'Privacy Policy',
                'admin_id' => 1
            ],
            [
                'id' => 6,
                'title' => 'Help',
                'description' => $help,
                'slug' => 'help',
                'meta_title' => 'Givvo - Help',
                'meta_description' => 'Help',
                'admin_id' => 1
            ],
            [
                'id' => 7,
                'title' => 'Sitemap',
                'description' => 'Sitemap',
                'slug' => 'sitemap',
                'meta_title' => 'Givvo - Sitemap',
                'meta_description' => 'Sitemap',
                'page_from_component' => 1,
                'admin_id' => 1
            ]
        ];

        $admin = Admin::where('id', 1)->first();


        if(!Page::first() && $admin){
            foreach ($items as $i) {
                Page::create($i);
            }
        }


    }
}
