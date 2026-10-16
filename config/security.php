<?php

/*
|--------------------------------------------------------------------------
| Catálogo canónico de seguridad (roles + permisos)
|--------------------------------------------------------------------------
|
| Única fuente de verdad del esquema RBAC del sistema. A partir de este
| catálogo, `php artisan security:sync` (y la migración
| `..._normalize_security_roles_and_permissions`) reconcilian de forma
| IDEMPOTENTE las tablas de Spatie Permission: crea los roles/permisos que
| falten y deja el reparto rol→permiso EXACTAMENTE como aquí se define.
|
| Reglas de negocio del reparto (convención uniforme de los 8 módulos):
|
|   registro (index/create/edit/destroy) → Admin, Docente, PC, PEC
|   view / general  (alcance DRE)        → Admin, EspecDRE
|   ugel             (alcance UGEL)      → Admin, EspecUGEL
|   director         (alcance institución)→ Admin, Director
|
| Director es SIEMPRE solo-visor; no aparece en los sets de escritura.
| Admin está presente en todos los permisos (acceso total) y es un rol
| protegido: no se renombra, no se elimina y no se le pueden quitar los
| permisos de la sección "Seguridad" (evita auto-bloqueo).
|
| Los módulos con nombre en plural (`accions`, `difusions`, `sectores`…) son
| el prefijo real de los permisos; `label` es solo para la UI.
|
*/

return [

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    | `label`   → nombre legible para la UI.
    | `level`   → jerarquía (mayor = más alto). Se usa para ordenar y para
    |             decidir a quién puede asignar quién (nunca por encima).
    | `protected` → Admin: no se puede renombrar ni eliminar desde la UI.
    */
    'roles' => [
        'Admin'     => ['label' => 'Administrador',            'level' => 100, 'protected' => true],
        'EspecDRE'  => ['label' => 'Especialista DRE',         'level' => 60,  'protected' => false],
        'EspecUGEL' => ['label' => 'Especialista UGEL',        'level' => 50,  'protected' => false],
        'Director'  => ['label' => 'Director de Institución',  'level' => 40,  'protected' => false],
        'Docente'   => ['label' => 'Docente',                  'level' => 10,  'protected' => false],
        'PC'        => ['label' => 'Profesor Coordinador',     'level' => 10,  'protected' => false],
        'PEC'       => ['label' => 'Promotor Educativo Comunitario', 'level' => 10, 'protected' => false],
    ],

    /*
    |--------------------------------------------------------------------------
    | Jerarquía de asignación de roles
    |--------------------------------------------------------------------------
    | Para cada rol, la lista de roles que puede otorgar a otros usuarios
    | (siempre su propio nivel o inferior). Admin queda fuera porque tiene
    | acceso total y lo resuelve el propio controlador.
    */
    'assignment' => [
        'EspecDRE'  => ['EspecDRE', 'EspecUGEL', 'Director', 'Docente', 'PC', 'PEC'],
        'EspecUGEL' => ['EspecUGEL', 'Director', 'Docente', 'PC', 'PEC'],
        'Director'  => ['Director', 'Docente', 'PC', 'PEC'],
        'Docente'   => ['Docente', 'PC', 'PEC'],
        'PC'        => ['Docente', 'PC', 'PEC'],
        'PEC'       => ['Docente', 'PC', 'PEC'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Módulos y permisos
    |--------------------------------------------------------------------------
    | Cada módulo agrupa sus permisos. `action` es un etiquetado semántico
    | (registro / general / ugel / director / especial) usado por la UI para
    | colorear y agrupar. Si `roles` se omite, se aplica el set del `action`.
    */
    'default_roles' => [
        'registro' => ['Admin', 'Docente', 'PC', 'PEC'],
        'general'  => ['Admin', 'EspecDRE'],
        'ugel'     => ['Admin', 'EspecUGEL'],
        'director' => ['Admin', 'Director'],
    ],

    'modules' => [

        'seguridad' => [
            'label'  => 'Seguridad',
            'icon'   => 'shield-check',
            'order'  => 1,
            'permissions' => [
                'roles.index'       => ['label' => 'Ver roles',         'action' => 'seguridad', 'roles' => ['Admin']],
                'roles.create'      => ['label' => 'Crear roles',       'action' => 'seguridad', 'roles' => ['Admin']],
                'roles.edit'        => ['label' => 'Editar roles',      'action' => 'seguridad', 'roles' => ['Admin']],
                'roles.destroy'     => ['label' => 'Eliminar roles',    'action' => 'seguridad', 'roles' => ['Admin']],
                'permissions.index' => ['label' => 'Ver permisos',      'action' => 'seguridad', 'roles' => ['Admin']],
            ],
        ],

        'usuarios' => [
            'label' => 'Usuarios',
            'icon'  => 'users',
            'order' => 2,
            'permissions' => [
                'users.index'        => ['label' => 'Listar usuarios',   'action' => 'registro', 'roles' => ['Admin']],
                'users.create'       => ['label' => 'Crear usuarios',    'action' => 'registro', 'roles' => ['Admin']],
                'users.edit'         => ['label' => 'Editar usuarios',   'action' => 'registro', 'roles' => ['Admin']],
                'users.destroy'      => ['label' => 'Eliminar usuarios', 'action' => 'registro', 'roles' => ['Admin']],
                'usersi.index'       => ['label' => 'Listar inhabilitados',   'action' => 'registro', 'roles' => ['Admin']],
                'usersi.create'      => ['label' => 'Crear inhabilitados',    'action' => 'registro', 'roles' => ['Admin']],
                'usersi.edit'        => ['label' => 'Editar inhabilitados',   'action' => 'registro', 'roles' => ['Admin']],
                'usersi.destroy'     => ['label' => 'Eliminar inhabilitados', 'action' => 'registro', 'roles' => ['Admin']],
            ],
        ],

        'instituciones' => [
            'label' => 'Instituciones',
            'icon'  => 'landmark',
            'order' => 3,
            'permissions' => [
                'institucions.index'   => ['label' => 'Listar instituciones',   'action' => 'registro', 'roles' => ['Admin']],
                'institucions.create'  => ['label' => 'Crear instituciones',    'action' => 'registro', 'roles' => ['Admin']],
                'institucions.edit'    => ['label' => 'Editar instituciones',   'action' => 'registro', 'roles' => ['Admin']],
                'institucions.destroy' => ['label' => 'Eliminar instituciones', 'action' => 'registro', 'roles' => ['Admin']],
            ],
        ],

        'dashboard' => [
            'label' => 'Dashboard',
            'icon'  => 'chart-line',
            'order' => 4,
            'permissions' => [
                'dashboard.index'    => ['label' => 'Dashboard DRE/Admin',     'action' => 'general',  'roles' => ['Admin', 'EspecDRE']],
                'dashboard.ugel'     => ['label' => 'Dashboard UGEL',          'action' => 'ugel',     'roles' => ['Admin', 'EspecUGEL']],
                'dashboard.director' => ['label' => 'Dashboard Institución',   'action' => 'director', 'roles' => ['Admin', 'Director']],
                'dashboard.pc'       => ['label' => 'Dashboard PC',            'action' => 'especial', 'roles' => ['Admin', 'PC']],
            ],
        ],

        'accions' => [
            'label' => 'Acción de Sensibilización',
            'icon'  => 'megaphone',
            'order' => 5,
            'permissions' => [
                'accions.index'    => ['label' => 'Mis registros',        'action' => 'registro'],
                'accions.create'   => ['label' => 'Crear',                'action' => 'registro'],
                'accions.edit'     => ['label' => 'Editar',               'action' => 'registro'],
                'accions.destroy'  => ['label' => 'Eliminar',             'action' => 'registro'],
                'accions.view'     => ['label' => 'General (DRE)',        'action' => 'general'],
                'accions.dre'      => ['label' => 'DRE',                  'action' => 'general',  'roles' => ['Admin', 'EspecDRE']],
                'accions.ugel'     => ['label' => 'UGEL',                 'action' => 'ugel'],
                'accions.director' => ['label' => 'Institución',          'action' => 'director'],
            ],
        ],

        'difusions' => [
            'label' => 'Acción de Difusión',
            'icon'  => 'radio',
            'order' => 6,
            'permissions' => [
                'difusions.index'    => ['label' => 'Mis registros', 'action' => 'registro'],
                'difusions.create'   => ['label' => 'Crear',         'action' => 'registro'],
                'difusions.edit'     => ['label' => 'Editar',        'action' => 'registro'],
                'difusions.destroy'  => ['label' => 'Eliminar',      'action' => 'registro'],
                'difusions.view'     => ['label' => 'General (DRE)', 'action' => 'general'],
                'difusions.ugel'     => ['label' => 'UGEL',          'action' => 'ugel'],
                'difusions.director' => ['label' => 'Institución',   'action' => 'director'],
            ],
        ],

        'sectores' => [
            'label' => 'Sectores del Aula',
            'icon'  => 'layout-grid',
            'order' => 7,
            'permissions' => [
                'sectores.index'    => ['label' => 'Mis registros', 'action' => 'registro'],
                'sectores.create'   => ['label' => 'Crear',         'action' => 'registro'],
                'sectores.edit'     => ['label' => 'Editar',        'action' => 'registro'],
                'sectores.destroy'  => ['label' => 'Eliminar',      'action' => 'registro'],
                'sectores.view'     => ['label' => 'General (DRE)', 'action' => 'general'],
                'sectores.ugel'     => ['label' => 'UGEL',          'action' => 'ugel'],
                'sectores.director' => ['label' => 'Institución',   'action' => 'director'],
            ],
        ],

        'evidencias' => [
            'label' => 'Asistencia Técnica',
            'icon'  => 'file-text',
            'order' => 8,
            'permissions' => [
                'evidencias.index'    => ['label' => 'Mis registros', 'action' => 'registro'],
                'evidencias.create'   => ['label' => 'Crear',         'action' => 'registro'],
                'evidencias.edit'     => ['label' => 'Editar',        'action' => 'registro'],
                'evidencias.destroy'  => ['label' => 'Eliminar',      'action' => 'registro'],
                'evidencias.view'     => ['label' => 'General (DRE)', 'action' => 'general'],
                'evidencias.ugel'     => ['label' => 'UGEL',          'action' => 'ugel'],
                'evidencias.director' => ['label' => 'Institución',   'action' => 'director'],
            ],
        ],

        'informes' => [
            'label' => 'Biblioteca del Aula',
            'icon'  => 'book-open',
            'order' => 9,
            'permissions' => [
                'informes.index'    => ['label' => 'Mis registros', 'action' => 'registro'],
                'informes.create'   => ['label' => 'Crear',         'action' => 'registro'],
                'informes.edit'     => ['label' => 'Editar',        'action' => 'registro'],
                'informes.destroy'  => ['label' => 'Eliminar',      'action' => 'registro'],
                'informes.view'     => ['label' => 'General (DRE)', 'action' => 'general'],
                'informes.ugel'     => ['label' => 'UGEL',          'action' => 'ugel'],
                'informes.director' => ['label' => 'Institución',   'action' => 'director'],
            ],
        ],

        'plans' => [
            'label' => 'Espacio de Lectura en el Hogar',
            'icon'  => 'book-heart',
            'order' => 10,
            'permissions' => [
                'plans.index'    => ['label' => 'Mis registros', 'action' => 'registro'],
                'plans.create'   => ['label' => 'Crear',         'action' => 'registro'],
                'plans.edit'     => ['label' => 'Editar',        'action' => 'registro'],
                'plans.destroy'  => ['label' => 'Eliminar',      'action' => 'registro'],
                'plans.view'     => ['label' => 'General (DRE)', 'action' => 'general'],
                'plans.ugel'     => ['label' => 'UGEL',          'action' => 'ugel'],
                'plans.director' => ['label' => 'Institución',   'action' => 'director'],
            ],
        ],

        'produccions' => [
            'label' => 'Producción de Textos Infantiles',
            'icon'  => 'notebook-pen',
            'order' => 11,
            'permissions' => [
                'produccions.index'    => ['label' => 'Mis registros', 'action' => 'registro'],
                'produccions.create'   => ['label' => 'Crear',         'action' => 'registro'],
                'produccions.edit'     => ['label' => 'Editar',        'action' => 'registro'],
                'produccions.destroy'  => ['label' => 'Eliminar',      'action' => 'registro'],
                'produccions.view'     => ['label' => 'General (DRE)', 'action' => 'general'],
                'produccions.ugel'     => ['label' => 'UGEL',          'action' => 'ugel'],
                'produccions.director' => ['label' => 'Institución',   'action' => 'director'],
            ],
        ],

        'agendas' => [
            'label' => 'Agenda de Lectura',
            'icon'  => 'calendar-check',
            'order' => 12,
            'permissions' => [
                'agendas.index'     => ['label' => 'Mis registros',    'action' => 'registro'],
                'agendas.create'    => ['label' => 'Crear',            'action' => 'registro'],
                'agendas.edit'      => ['label' => 'Editar',           'action' => 'registro'],
                'agendas.destroy'   => ['label' => 'Eliminar',         'action' => 'registro'],
                'agendas.evidencia' => ['label' => 'Subir evidencias', 'action' => 'registro'],
                'agendas.view'      => ['label' => 'Institución (Director)', 'action' => 'director', 'roles' => ['Admin', 'Director']],
                'agendas.general'   => ['label' => 'General (DRE)',    'action' => 'general'],
                'agendas.ugel'      => ['label' => 'UGEL',             'action' => 'ugel'],
            ],
        ],

    ],
];
