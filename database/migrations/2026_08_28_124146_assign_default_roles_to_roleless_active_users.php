<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Corrige un problema preexistente (no relacionado a este feature): 81 usuarios
 * activos no tenían ningún rol asignado, lo que les impedía usar el sistema
 * (403 tras el login por falta de permisos). Se les asigna el nivel más bajo
 * de la jerarquía (Docente, PC, PEC) en vez de intentar adivinar su rol real
 * a partir del campo de texto libre `cargo` (que tiene valores inconsistentes).
 */
return new class extends Migration
{
    private array $defaultRoleNames = ['Docente', 'PC', 'PEC'];

    public function up()
    {
        $roles = Role::whereIn('name', $this->defaultRoleNames)->get();

        User::where('estado', 1)
            ->doesntHave('roles')
            ->each(fn (User $user) => $user->syncRoles($roles));
    }

    public function down()
    {
        // Corrección de datos puntual: no se revierte porque no hay forma de
        // distinguir estos usuarios de otros que legítimamente tengan
        // exactamente Docente+PC+PEC asignados por otra vía.
    }
};
