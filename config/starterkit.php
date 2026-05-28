<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'pagination' => 8,

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */

    'roles' => [
        'admin',
        'user',
    ],

    'default_admin_role' => 'admin',

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    | Format: '<resource> <action>' => '<human readable label>'
    */

    'permissions' => [
        'users index'  => 'View Users',
        'users create' => 'Create User',
        'users edit'   => 'Edit User',
        'users delete' => 'Delete User',
        'roles index'  => 'View Roles',
        'roles create' => 'Create Role',
        'roles edit'   => 'Edit Role',
        'roles delete' => 'Delete Role',
    ],

];
