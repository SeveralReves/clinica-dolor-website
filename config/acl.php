<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default role
    |--------------------------------------------------------------------------
    */
    'default_role' => 'user',

    /*
    |--------------------------------------------------------------------------
    | Super roles (bypass)
    |--------------------------------------------------------------------------
    | Estos roles tienen acceso a todo.
    */
    'super_roles' => ['superadmin'],

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    | Define permisos por rol.
    | - Puedes usar:
    |   - permisos directos: ['users.view', 'users.create']
    |   - comodín: 'users.*'
    |   - herencia: 'inherits' => ['user']
    */
    'roles' => [

        'user' => [
            'label' => 'Usuario',
            'permissions' => [
                'dashboard.view',
            ],
        ],

        'assist' => [
            'label' => 'Asistente',
            'inherits' => ['user'],
            'permissions' => [
                'units.view',
                'units.move_state',
                'incidents.create',
                'incidents.view',
            ],
        ],

        'doctor' => [
            'label' => 'Doctor',
            'inherits' => ['assist'],
            'permissions' => [
                'incidents.manage',
                'reports.view',
            ],
        ],

        'admin' => [
            'label' => 'Administrador',
            'inherits' => ['doctor'],
            'permissions' => [
                'buques.manage',
                'cargas.manage',
                'patios.manage',
                'users.view',
                'users.update',
            ],
        ],

        'superadmin' => [
            'label' => 'Super Admin',
            'permissions' => ['*'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions registry (opcional)
    |--------------------------------------------------------------------------
    | Útil para listar permisos en UI/seeders y tener “catálogo” humano.
    | No es obligatorio para que funcione.
    */
    'permissions' => [
        'dashboard.view'   => 'Ver dashboard',
        'units.view'       => 'Ver unidades',
        'units.move_state' => 'Mover unidades de estado',
        'incidents.create' => 'Crear incidencias',
        'incidents.view'   => 'Ver incidencias',
        'incidents.manage' => 'Gestionar incidencias',
        'reports.view'     => 'Ver reportes',
        'buques.manage'    => 'Gestionar buques',
        'cargas.manage'    => 'Gestionar cargas',
        'patios.manage'    => 'Gestionar patios',
        'users.view'       => 'Ver usuarios',
        'users.update'     => 'Editar usuarios',
    ],
];
