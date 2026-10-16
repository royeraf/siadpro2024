<?php

namespace App\Console\Commands;

use App\Services\Security\SecurityCatalog;
use Illuminate\Console\Command;

class SecuritySyncCommand extends Command
{
    protected $signature = 'security:sync {--dry-run : Solo muestra el diff, sin aplicar cambios}';

    protected $description = 'Reconcilia roles y permisos con el catálogo canónico (config/security.php)';

    public function handle(SecurityCatalog $catalog): int
    {
        $audit = $catalog->audit();

        $this->info('Auditoría del catálogo de seguridad');
        $this->line('Roles en catálogo: ' . count($catalog->roleNames()));
        $this->line('Permisos en catálogo: ' . count($catalog->permissionNames()));

        $this->newLine();
        $this->line('Roles faltantes: ' . ($audit['missing_roles'] ? implode(', ', $audit['missing_roles']) : 'ninguno'));
        $this->line('Permisos faltantes: ' . count($audit['missing_permissions']));
        if ($audit['missing_permissions']) {
            $this->line('  → ' . implode(', ', $audit['missing_permissions']));
        }

        $this->line('Permisos con reparto distinto: ' . count($audit['permission_diffs']));
        foreach ($audit['permission_diffs'] as $diff) {
            $this->line(sprintf(
                '  · %s  +[%s]  -[%s]',
                $diff['name'],
                implode(', ', $diff['add']),
                implode(', ', $diff['remove'])
            ));
        }

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->comment('Modo --dry-run: no se aplicó ningún cambio.');

            return self::SUCCESS;
        }

        $result = $catalog->sync();

        $this->newLine();
        $this->info('Sincronización aplicada.');
        $this->line('Roles creados: ' . ($result['roles_created'] ? implode(', ', $result['roles_created']) : 'ninguno'));
        $this->line('Permisos creados: ' . count($result['permissions_created']));

        return self::SUCCESS;
    }
}
