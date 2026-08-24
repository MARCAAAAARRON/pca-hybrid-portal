<?php

namespace Database\Seeders;

use App\Models\FieldSite;
use Illuminate\Database\Seeder;

class FieldSiteSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            [
                'name' => 'Loay Farm',
                'description' => 'PCA Bohol Sub-Station, Loay, Bohol — Field site managed by Loay Supervisor',
            ],
            [
                'name' => 'Balilihan Farm',
                'description' => 'PCA Bohol Sub-Station, Balilihan, Bohol — Field site managed by Balilihan Supervisor',
            ],
        ];

        foreach ($sites as $site) {
            FieldSite::firstOrCreate(['name' => $site['name']], $site);
        }
    }
}
