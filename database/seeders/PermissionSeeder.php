<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'description' => 'Access to admin dashboard and statistics',
            ],
            [
                'name' => 'Manage Admins',
                'slug' => 'admins',
                'description' => 'Create, edit, and delete admin accounts',
            ],
            [
                'name' => 'Manage Users',
                'slug' => 'users',
                'description' => 'View and manage platform users (gamers & recruiters)',
            ],
            [
                'name' => 'Manage Permissions',
                'slug' => 'permissions',
                'description' => 'Assign and revoke admin permissions',
            ],
            [
                'name' => 'Manage FAQs',
                'slug' => 'faqs',
                'description' => 'Create, edit, and manage frequently asked questions',
            ],
            [
                'name' => 'Manage Feedback',
                'slug' => 'feedback',
                'description' => 'View and manage user feedback submissions',
            ],
            [
                'name' => 'Manage Tickets',
                'slug' => 'tickets',
                'description' => 'View, assign, and respond to support tickets',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}









