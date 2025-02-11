<?php

declare(strict_types=1);

namespace App\Support\Permissions;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class PermissionManager
{
    public static function getPermissions(): array
    {
        return [
            // User management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',

            // Role management
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',

            // Event management
            'view_events',
            'create_events',
            'edit_events',
            'delete_events',
            'publish_events',
            'feature_events',

            // Category management
            'view_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',

            // Ticket management
            'view_tickets',
            'create_tickets',
            'edit_tickets',
            'delete_tickets',

            // Order management
            'view_orders',
            'manage_orders',
            'process_refunds',

            // Report access
            'view_reports',
            'export_reports',

            // Content moderation
            'moderate_content',
            'manage_comments',
            'manage_reviews',

            // Settings
            'manage_settings',
        ];
    }

    public static function getRolePermissions(): array
    {
        return [
            'super-admin' => self::getPermissions(),

            'admin' => [
                'view_users', 'create_users', 'edit_users',
                'view_roles',
                'view_events', 'edit_events', 'delete_events', 'publish_events', 'feature_events',
                'view_categories', 'create_categories', 'edit_categories',
                'view_tickets', 'edit_tickets',
                'view_orders', 'manage_orders', 'process_refunds',
                'view_reports', 'export_reports',
                'moderate_content', 'manage_comments', 'manage_reviews',
                'manage_settings',
            ],

            'moderator' => [
                'view_events', 'edit_events',
                'view_tickets',
                'view_orders',
                'moderate_content', 'manage_comments', 'manage_reviews',
                'view_reports',
            ],

            'event-organizer' => [
                'view_events', 'create_events', 'edit_events',
                'view_tickets', 'create_tickets', 'edit_tickets',
                'view_orders', 'manage_orders',
                'view_reports',
            ],

            'member' => [
                'view_events',
                'view_tickets',
            ],
        ];
    }

    public static function createPermissions(): void
    {
        foreach (self::getPermissions() as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission]);
        }
    }

    public static function createRoles(): void
    {
        foreach (self::getRolePermissions() as $roleName => $permissions) {
            $role = Role::query()->firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissions);
        }
    }
}
