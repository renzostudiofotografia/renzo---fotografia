# Renzo Nuñes Melgar — Fotografía de Bodas

Réplica en Laravel 13 del sitio editorial de fotografía de bodas, con una estructura Blade modular, assets locales optimizados y navegación responsive.

## Requisitos

- PHP 8.3 o superior
- Composer 2

## Inicio rápido

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan serve --host=127.0.0.1 --port=8010
```

Abrir `http://127.0.0.1:8010`.

## Estructura principal

- `app/Http/Controllers/PageController.php`: páginas públicas y validación del formulario.
- `config/site.php`: servicios, portafolio, testimonios y datos de contacto.
- `resources/views/layouts`: layout global.
- `resources/views/components/site`: encabezado y pie reutilizables.
- `resources/views/pages`: portada y páginas internas.
- `public/assets/images`: fotografías locales organizadas por uso.
- `public/css/styles.css`: sistema visual responsive.
- `public/js/main.js`: carruseles, menú móvil, filtros y lightbox.

## Verificación

```bash
php artisan test
vendor/bin/pint --test
```

El formulario de contacto valida y confirma la consulta localmente. No envía correos hasta que se configure un proveedor de email.
