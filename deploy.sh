#!/bin/bash
set -e

# Ejecuta siempre desde la carpeta del proyecto, sin importar desde dónde se invoque.
cd "$(dirname "$0")"

echo "----------------------------------------"
echo "🚀 Iniciando despliegue de SIADPRO..."
echo "----------------------------------------"

# Asegura que el sitio vuelva a estar operativo aunque el despliegue falle a mitad.
cleanup() {
    echo "🔓 Desactivando modo mantenimiento..."
    php artisan up 2>/dev/null || true
}
trap cleanup EXIT

# 1. Poner la app en modo mantenimiento durante el despliegue (evita errores a usuarios)
echo "🛠️  Activando modo mantenimiento..."
php artisan down --retry=15 || true

# 2. Descargar los últimos cambios de GitHub
echo "📥 Descargando últimos cambios desde GitHub..."
git pull origin main

# 3. Instalar dependencias de PHP (solo producción)
echo "📦 Verificando dependencias de Composer..."
if command -v composer &> /dev/null; then
    composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
else
    echo "⚠️ Composer no encontrado en PATH, omitiendo instalación de dependencias PHP."
fi

# 4. Ejecutar migraciones pendientes de base de datos
echo "🗄️ Ejecutando migraciones de base de datos..."
php artisan migrate --force

# 5. Limpiar y regenerar cachés de Laravel
echo "⚡ Optimizando configuración y cachés de Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5b. Reiniciar la caché de permisos de Spatie (roles/permisos recién reconciliados)
echo "🔐 Reiniciando caché de permisos..."
php artisan permission:cache-reset || true

# 6. Compilar assets de frontend (si pnpm está disponible)
if command -v pnpm &> /dev/null; then
    echo "🎨 Compilando frontend (Vite / Tailwind) con pnpm..."
    pnpm install --frozen-lockfile
    pnpm run build
else
    echo "ℹ️ pnpm no disponible en servidor. Recuerda compilar en local si cambiaste JS/CSS."
fi

# 7. Asegurar permisos correctos en carpetas críticas de Laravel
echo "🔒 Verificando permisos de storage y bootstrap/cache..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 8. Desactivar modo mantenimiento
echo "🔓 Desactivando modo mantenimiento..."
php artisan up
trap - EXIT

echo "----------------------------------------"
echo "✅ ¡Despliegue completado con éxito!"
echo "----------------------------------------"
