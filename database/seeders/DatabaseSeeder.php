<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FieldSite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Field Sites
        $this->call(FieldSiteSeeder::class);

        // 2. Create Roles & Permissions FIRST
        // Must run before any User is created, since the User model's
        // saved() hook automatically calls syncRoles($user->role).
        $this->call(RolePermissionSeeder::class);

        $loay = FieldSite::where('name', 'Loay Farm')->first();
        $balilihan = FieldSite::where('name', 'Balilihan Farm')->first();

        // 3. Create Admin (PCDM / Division Chief I)
        $admin = User::firstOrCreate(
            ['email' => 'admin@pca.gov.ph'],
            [
                'name' => 'Division Chief',
                'password' => Hash::make('PCA@gov.ph'),
                'role' => 'admin',
                'field_site_id' => null,
                'email_verified_at' => now(),
                'is_approved' => true,
            ]
        );

        // 4. Create Manager (Senior Agriculturist)
        $manager = User::firstOrCreate(
            ['email' => 'manager@pca.gov.ph'],
            [
                'name' => 'Senior Agriculturist',
                'password' => Hash::make('PCA@gov.ph'),
                'role' => 'manager',
                'field_site_id' => null,
                'email_verified_at' => now(),
                'is_approved' => true,
            ]
        );

        // 5. Create Supervisors
        $loaySupervisor = User::firstOrCreate(
            ['email' => 'loay@pca.gov.ph'],
            [
                'name' => 'Loay Supervisor',
                'password' => Hash::make('PCA@gov.ph'),
                'role' => 'supervisor',
                'field_site_id' => $loay?->id,
                'email_verified_at' => now(),
                'is_approved' => true,
            ]
        );

        $baliSupervisor = User::firstOrCreate(
            ['email' => 'balilihan@pca.gov.ph'],
            [
                'name' => 'Balilihan Supervisor',
                'password' => Hash::make('PCA@gov.ph'),
                'role' => 'supervisor',
                'field_site_id' => $balilihan?->id,
                'email_verified_at' => now(),
                'is_approved' => true,
            ]
        );

        // 6. Create Superadmin (System Administrator)
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@pca.gov.ph'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('PCA@gov.ph'),
                'role' => 'superadmin',
                'field_site_id' => null,
                'email_verified_at' => now(),
                'is_approved' => true,
            ]
        );
    }
}