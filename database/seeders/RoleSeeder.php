<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'administrator' => [
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
            ],
            'librarian' => [
                'dashboard.view',

                'authors.view',
                'authors.create',
                'authors.update',

                'publishers.view',
                'publishers.create',
                'publishers.update',

                'categories.view',
                'categories.create',
                'categories.update',

                'books.view',
                'books.create',
                'books.update',
                'books.upload-cover',
                'books.search',

                'members.view',
                'members.create',
                'members.update',
                'members.activate',
                'members.deactivate',

                'borrowings.view',
                'borrowings.create',
                'borrowings.approve',
                'borrowings.return',
                'borrowings.cancel',

                'reservations.view',
                'reservations.create',
                'reservations.cancel',
                'reservations.manage',

                'fines.view',
                'fines.create',
                'fines.update',
                'fines.pay',

                'reports.view',
                'reports.export',

                'notifications.view',
                'notifications.send',

                'files.upload',
                'files.delete',

                'profile.view',
                'profile.update',
                'profile.change-password',
            ],
            'staff' => [
                'dashboard.view',

                'authors.view',
                'publishers.view',
                'categories.view',

                'books.view',
                'books.search',

                'members.view',
                'members.create',
                'members.update',

                'borrowings.view',
                'borrowings.create',
                'borrowings.return',

                'reservations.view',
                'reservations.create',
                'reservations.cancel',

                'fines.view',
                'fines.pay',

                'notifications.view',

                'profile.view',
                'profile.update',
                'profile.change-password',
            ],
            'member' => [
                'profile.view',
                'profile.update',
                'profile.change-password',

                'books.view',
                'books.search',

                'borrowings.view-own',
                'borrowings.create',
                'borrowings.return-own',

                'reservations.view-own',
                'reservations.create',
                'reservations.cancel-own',

                'fines.view-own',
                'fines.pay-own',

                'notifications.view-own',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'api',
            ]);

            $role->syncPermissions($permissions);
        }
    }
}
