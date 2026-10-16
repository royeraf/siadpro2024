<?php

use App\Services\Security\SecurityCatalog;
use Illuminate\Database\Migrations\Migration;

/**
 * Normaliza el esquema de roles y permisos a partir del catálogo canónico
 * (`config/security.php`), aplicando la reconciliación idempotente de
 * `SecurityCatalog::sync()`.
 *
 * Qué corrige respecto al estado heredado (seeders que nunca corrieron en
 * producción + migraciones parciales + roles con permisos de más):
 *
 *  1. Crea el rol `PEC`, que se usaba en seeders/migraciones/jerarquía pero
 *     nunca se creó (un `db:seed` desde cero fallaba con firstOrFail).
 *  2. Deja el reparto rol→permiso EXACTAMENTE como el catálogo. Eso alinea
 *     las derivas documentadas: `accions.view` y `produccions.view` vuelven a
 *     Admin+EspecDRE (retenían EspecUGEL/Director de un seeder nunca depurado)
 *     y Director deja de tener escritura en los módulos donde aún la tenía.
 *  3. Restringe `users.*` (y `usersi.*`) a Admin: hoy los tienen TODOS los
 *     roles, incluidos Docente/PC/PEC.
 *  4. Añade los permisos de la sección Seguridad (`roles.*`, `permissions.index`)
 *     solo a Admin.
 *
 * No elimina roles ni permisos existentes que no estén en el catálogo, para no
 * romper referencias inesperadas; solo reconcilia lo que declara.
 *
 * Reversible a mano con `php artisan security:sync` tras editar el catálogo;
 * el `down()` queda como no-op a propósito (es una corrección de datos).
 */
return new class extends Migration
{
    public function up(): void
    {
        app(SecurityCatalog::class)->sync();
    }

    public function down(): void
    {
        // Corrección de datos: no se revierte automáticamente. Para reajustar
        // el esquema edita config/security.php y ejecuta `security:sync`.
    }
};
