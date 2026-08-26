#!/bin/bash
set -e

echo "----------------------------------------"
echo "🚀 Iniciando despliegue de SIADPRO..."
echo "----------------------------------------"

# 1. Poner la app en modo mantenimiento (opcional, evita errores si hay usuarios en ese momento)
# php artisan down --render="errors::503" || true

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

# 6. Compilar assets de frontend (si npm está disponible)
if command -v npm &> /dev/null; then
    echo "🎨 Compilando frontend (Vite / Tailwind)..."
    npm install --no-audit --no-fund
    npm run build
else
    echo "ℹ️ Node/NPM no disponible en servidor. Recuerda compilar en local si cambiaste JS/CSS."
fi

# 7. Asegurar permisos correctos en carpetas críticas de Laravel
echo "🔒 Verificando permisos de storage y bootstrap/cache..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 8. Desactivar modo mantenimiento
# php artisan up || true

echo "----------------------------------------"
echo "✅ ¡Despliegue completado con éxito!"
echo "----------------------------------------"
