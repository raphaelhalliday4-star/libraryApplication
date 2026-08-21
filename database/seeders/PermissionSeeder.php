<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'dashboard.view',

            'authors.view',
            'authors.create',
            'authors.update',
            'authors.delete',

            'publishers.view',
            'publishers.create',
            'publishers.update',
            'publishers.delete',

            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            'books.view',
            'books.create',
            'books.update',
            'books.delete',
            'books.upload-cover',
            'books.search',

            'members.view',
            'members.create',
            'members.update',
            'members.delete',
            'members.activate',
            'members.deactivate',

            'borrowings.view',
            'borrowings.view-own',
            'borrowings.create',
            'borrowings.approve',
            'borrowings.return',
            'borrowings.return-own',
            'borrowings.cancel',

            'reservations.view',
            'reservations.view-own',
            'reservations.create',
            'reservations.cancel',
            'reservations.cancel-own',
            'reservations.manage',

            'fines.view',
            'fines.view-own',
            'fines.create',
            'fines.update',
            'fines.pay',
            'fines.pay-own',
            'fines.waive',

            'reports.view',
            'reports.export',

            'notifications.view',
            'notifications.view-own',
            'notifications.send',

            'files.upload',
            'files.delete',

            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            'permissions.view',

            'settings.view',
            'settings.update',

            'audit-logs.view',

            'profile.view',
            'profile.update',
            'profile.change-password',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission,'guard_name' => 'api',]);
        }

    }
}
