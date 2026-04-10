# Sociogram

Aplicacion web para gestionar grupos, asignar tests sociometricos y generar analisis de relaciones dentro del aula.

## Stack

- PHP 8.2+
- Laravel 11
- Livewire 3
- Pest
- Spatie Laravel Permission
- DomPDF
- Vite

## Modulos principales

- Gestion de grupos y estudiantes
- Asignacion de tests a grupos
- Acceso publico por clave para responder tests
- Analisis sociometrico con sociograma, centralidades, comunidades y reciprocidad
- Exportacion de analisis en PDF

## Puesta en marcha

1. Instala dependencias:

```bash
composer install
npm install
```

2. Crea el entorno:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configura la base de datos en `.env`.

4. Ejecuta migraciones y seeders:

```bash
php artisan migrate --seed
```

5. Inicia la aplicacion:

```bash
php artisan serve
npm run dev
```

## Datos demo

El `DatabaseSeeder` carga roles y datos de desarrollo.

Credenciales:

- `demo@vps-example.test / password`
- `analisis@vps-example.test / password`

Claves de test:

- `CLASE123` para un test pendiente
- `DEMO2026` para un test con analisis ya generado

## Testing

Ejecuta la suite con:

```bash
php vendor/bin/pest
```

Si prefieres:

```bash
php artisan test
```

## Estructura util

- `app/Livewire`: flujo de dashboard y analisis
- `app/Services/AnalisisGrupalService.php`: calculo principal del analisis
- `app/Http/Controllers/TestController.php`: flujo publico de acceso por clave y envio
- `resources/views/livewire`: vistas del panel y del analisis
- `public/js/analisis.js`: logica del sociograma y graficas
- `database/seeders`: roles y datos demo

## Estado actual

La app ya cubre el flujo principal, pero todavia hay trabajo pendiente en:

- simplificacion del frontend
- modularizacion de JS del analisis
- mejoras de UX del formulario publico
- ampliacion de la cobertura de tests y limpieza de deuda tecnica
