# Landing de bodas: implementación y entrega

Implementación local terminada el 13 de noviembre de 2026. El servidor publicado no se modificó. El proyecto conserva Laravel 13.34.0, PHP 8.3, Blade y sus archivos CSS/JavaScript existentes; no se instalaron dependencias.

## Resultado

Se añadió una dirección visual editorial para bodas: marfil, carbón, acentos champagne discretos, tipografía serif, fotografías de gran formato y composiciones asimétricas. El tema original continúa disponible para las demás campañas. Se mantienen los siete modelos de botones, sus animaciones, el CTA flotante y las portadas de foto, flyer, carrusel y video.

La portada ocupa todo el ancho, con fotografía móvil independiente y encuadre cuadrado 1:1 en teléfonos. El texto se presenta separado de la fotografía móvil. Un ajuste de ampliación permite ocultar marcos incluidos dentro del archivo sin alterar el original. El encabezado se desplaza normalmente y solo el botón de consulta permanece fijo.

Los botones públicos utilizan SVG de la galería oficial [Heroicons](https://github.com/tailwindlabs/heroicons), con selector de icono y opción sin icono. La licencia se conserva en `public/assets/icons/heroicons/LICENSE.txt`. La alineación general puede respetar cada sección o alinear sus textos a izquierda, centro o derecha; las secciones también tienen su control individual. Los títulos admiten saltos de línea que se conservan al editar otros campos.

El tamaño del título principal y del texto se aplica mediante sus variables configurables, sin los tamaños fijos que antes limitaban su respuesta en el tema de bodas. Se comprobó sin guardar la secuencia título/texto de 56/20 px a 80/16 px; en la vista móvil, el título de 80 px se adapta a 52 px y la fotografía conserva su proporción cuadrada.

Los bloques administrables incorporan confianza, historias de parejas, presentación del fotógrafo, experiencias, proceso y cierre emocional. Las historias permiten ordenar su galería completa y abrirla en un diálogo accesible; sus fotos adicionales se cargan al abrirla. No se atribuyeron fotos de otras parejas a una misma boda.

Las experiencias permiten editar imagen o video, nombre, descripción, inclusiones, horas, precio opcional, CTA y destacado. Historias, sus galerías, testimonios, experiencias y bloques de texto admiten fotografías o videos con miniatura. La calculadora se muestra únicamente cuando está activada y hay una cobertura con tarifa positiva; ciudad y adicionales utilizan tarifas configuradas. Todos los importes permanecen en soles. Las colecciones vacías de historias, testimonios, métricas, experiencias o proceso se omiten sin dejar una sección vacía.

El reproductor de video presenta únicamente reproducir/pausar y reiniciar, sin controles nativos, pantalla completa, Picture-in-Picture ni opción visible de descarga. Se pausa al cambiar de diapositiva, cerrar una historia o salir de pantalla. Ocultar la descarga no constituye DRM: un archivo reproducible en el navegador no puede impedir por completo que se guarde.

## Administración

El footer de las landings incorpora el crédito pequeño «Desarrollado por ODYMCOMPANY», con enlace a https://odymcompany.com/ en una pestaña nueva.

El editor existente se amplió con grupos y navegación rápida. Las secciones y sus elementos se ordenan mediante arrastre o flechas. Las galerías admiten elementos anidados sin confundir los índices de fotografías e historias. Quitar contenido requiere confirmación.

El guardado y la vista previa utilizan JSON para evitar que PHP trunque silenciosamente campañas con muchos campos. Se conservan el indicador de cambios pendientes, las validaciones por campo, la revisión concurrente, los borradores privados, la biblioteca multimedia y la publicación independiente.

Cada campo de video permite elegir de la biblioteca o subir un MP4/WebM desde un diálogo reutilizable. Seleccionar un video para una historia no sustituye el de la portada. La selección de imágenes y videos, los colores, la tipografía, los textos, los iconos y la alineación utilizan el mismo flujo de vista previa. El estado de actualización se confirma cuando el documento mostrado en el iframe coincide con la última edición, evitando que una respuesta antigua figure como actual.

El nombre y el slug son editables. Al cambiar el slug, los enlaces anteriores redirigen con 301 y conservan los parámetros de campaña. Un formulario ya abierto con el enlace anterior sigue funcionando. Los slugs de campañas en papelera continúan reservados.

La acción «Preparar diseño de bodas» modifica exclusivamente el borrador guardado. Conserva los textos, imágenes, configuración comercial y contenido publicado de la landing existente. Los nuevos contenidos iniciales están disponibles al crear una campaña con el preset «Bodas de autor». Una campaña existente mantiene sus títulos personalizados para que puedan revisarse y editarse antes de publicar.

## Consultas y medición

El formulario puede tener uno o dos pasos. Permite configurar obligatoriedad de fecha y ubicación, selector de experiencia, campos opcionales, textos, consentimiento y confirmación. Sin JavaScript mantiene todos los campos visibles. La vista previa desactiva el envío y la analítica.

El backend conserva las consultas en `landing_leads`, incluyendo fecha, ubicación, copia del nombre de la experiencia publicada y atribución UTM. Mantiene CSRF, límite de solicitudes, campo antispam, validaciones y una clave de envío única. Un reintento conserva la consulta original y no vuelve a notificar.

La confirmación del navegador requiere una respuesta JSON válida del backend. Se evita anunciar una fecha disponible o una reserva confirmada. Un fallo del correo no pierde la consulta guardada.

Meta Pixel mantiene ViewContent y Lead. GA4 añade view_landing, view_portfolio, select_wedding_story, view_experience, click_whatsapp, begin_inquiry y generate_lead. Los eventos propios utilizan identificadores públicos, moneda y valores; no envían los datos personales introducidos en el formulario. generate_lead y Lead requieren un envío nuevo confirmado. No se generan reservas ni prospectos calificados ficticios.

Para configurar GA4, revisar el flujo web y desactivar la medición automática de formularios y clics salientes cuando pueda duplicar estos eventos o recolectar enlaces con datos. Esta configuración depende de la cuenta del negocio: [documentación oficial de medición mejorada](https://support.google.com/analytics/answer/9216061?hl=es). Los identificadores se dejan vacíos hasta disponer de los reales.

El aviso opcional por correo requiere destinatario en el editor y un canal real de correo configurado en el servidor. Los canales `log` y `array` no se consideran entrega real. No se enviaron correos reales durante las pruebas.

## SEO, URL y rendimiento

Title, descripción, imagen compartida, Open Graph y datos de contacto son editables. La página incluye canonical, HTML semántico, datos estructurados ProfessionalService y sitemap de landings publicadas en `/landings-sitemap.xml`. Las vistas previas son privadas y no indexables.

Se conserva `/lp/bodas`. El prefijo `/public` del hosting no se eliminó: simplificarlo requiere revisar el document root del servidor. No se sustituyeron ni sobrescribieron fotografías originales. Se reutilizan WebP existentes, carga diferida fuera de la portada, prioridad de la imagen principal, galerías bajo demanda y movimiento reducido.

## Migraciones

Los últimos ajustes de iconos, multimedia, alineación y encuadre utilizan el JSON existente de las landings y no necesitan una migración adicional.

La nueva migración `2026_11_12_000003_add_wedding_inquiries_and_landing_aliases.php` añade únicamente columnas JSON opcionales:

- `landing_leads.inquiry_details`, para los detalles de la consulta.
- `landing_pages.previous_slugs`, para los enlaces anteriores.

La migración previa `2026_11_12_000002_add_soft_deletes_to_landing_pages_table.php` conserva la corrección acumulada para comprobar la existencia de `deleted_at`. Debe estar aplicada en el servidor. Las migraciones incrementales se aplicaron en la base SQLite local. No se ejecutaron acciones destructivas sobre datos existentes.

## Cómo usarlo y actualizar el servidor

1. Subir todos los archivos del paquete respetando sus carpetas. Incluye código, pruebas y documentación; no incluye credenciales, bases de datos ni fotografías originales.
2. Desde la raíz Laravel del servidor y con su conexión de base de datos configurada, ejecutar:

   ```bash
   php artisan optimize:clear
   php artisan migrate --force
   ```

3. Abrir la landing de bodas en el administrador. Guardar cambios pendientes y pulsar «Preparar diseño de bodas». Revisar el borrador; la publicación actual no se reemplaza automáticamente.
4. Completar contenidos y contactos reales, revisar escritorio y móvil, guardar y publicar.
5. Realizar una consulta de prueba en la versión publicada y comprobar su registro en «Ver consultas». Verificar el correo y los eventos en las cuentas reales una vez configuradas.

También se puede preparar el borrador mediante:

```bash
php artisan landing:wedding bodas
```

El comando guarda una copia de los atributos anteriores en `storage/app/landing-backups` y conserva la versión publicada. Para crear una campaña privada nueva: `php artisan landing:wedding nuevo-slug --create`.

En este equipo, la aplicación disponible en `http://127.0.0.1:8010` utiliza SQLite mediante variables del proceso. No se modificó el `.env`, que conserva la configuración MySQL. Para ejecutar comandos contra la misma base local en PowerShell:

```powershell
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = Join-Path (Get-Location) 'database/database.sqlite'
$env:DB_URL = ''
php artisan migrate
php artisan landing:wedding bodas
```

El borrador local preparado se encuentra en [el editor de bodas](http://127.0.0.1:8010/admin/landings/1/edit). Requiere una sesión de administrador. Si todavía no existe un administrador local, `php artisan site:setup` permite elegir las credenciales mediante el flujo existente.

## Contenidos y conexiones pendientes

- Retrato real de Renzo: el recurso usado por «Sobre mí» en el sitio principal muestra una novia y no se reutilizó como retrato.
- Galerías completas, fecha y descripción de cada boda. El portafolio existente aporta una portada por pareja.
- WhatsApp e Instagram reales: los valores genéricos del sitio principal no se importan automáticamente.
- Testimonios autorizados y cifras verificadas. No se promueven las cifras u opiniones de ejemplo del archivo de configuración; la importación automática exige contenido explícitamente publicado en `site_settings`.
- Condiciones, inclusiones, horas y tarifas comerciales confirmadas. No se inventaron precios ni plazos de entrega.
- Enlaces legales, Meta Pixel, GA4, destinatario y canal de correo del negocio.

## Verificación y recomendaciones comerciales

La suite completa terminó con **91 pruebas aprobadas y 1799 aserciones**. Se comprobaron creación, edición, publicación, permisos, preservación de datos, elementos anidados, consultas, validación, reintentos, notificaciones simuladas, slugs anteriores, sitemap, estimador, controles multimedia, SVG, alineación, vista previa sin guardar y compatibilidad con las demás páginas. También pasaron la compilación de todas las vistas Blade, la revisión de sintaxis JavaScript y `git diff --check`.

En el navegador se revisaron escritorio y móvil de 390 px, apertura y cierre con teclado de historias, carga de fotos al abrirlas, navegación entre los dos pasos del formulario, foco visible y creación y reordenación de fotos anidadas en el editor. También se utilizó una instancia Laravel autenticada con una base SQLite aislada generada por pruebas: se comprobaron cambios de texto rápidos, alineación, iconos, selección de fotografía, subida de un MP4 real, reproducción, pausa y reinicio, guardado del borrador y publicación. Se confirmó que la versión pública no cambia durante la edición sin publicar. La fotografía móvil midió 375 × 375 px, sin desbordamiento; el encabezado salió de pantalla al desplazarse y el botón de consulta permaneció fijo. No se tocaron los datos del proyecto durante esa comprobación. No se midieron Core Web Vitals en producción ni se conectaron cuentas reales de analítica o correo.

Para mejorar conversiones, priorizar reportajes completos y auténticos, un retrato correcto, testimonios verificables y una respuesta comercial consistente. Medir visitas, apertura de historias, inicio y envío de consulta, y después relacionar esas consultas con reuniones y reservas reales. La identidad visual ayuda a presentar la propuesta; los resultados deben validarse con tráfico y seguimiento comercial reales.

## Inventario de archivos

Inventario acumulado de 34 archivos con cambios desde el último commit, incluyendo las mejoras anteriores de botones y papelera.

- [app/Http/Controllers/LandingPageController.php](<C:/proyectos/renzo - fotografia/app/Http/Controllers/LandingPageController.php>)
- [app/LandingContent.php](<C:/proyectos/renzo - fotografia/app/LandingContent.php>)
- [app/Models/LandingLead.php](<C:/proyectos/renzo - fotografia/app/Models/LandingLead.php>)
- [app/Models/LandingPage.php](<C:/proyectos/renzo - fotografia/app/Models/LandingPage.php>)
- [app/Notifications/LandingInquiryReceived.php](<C:/proyectos/renzo - fotografia/app/Notifications/LandingInquiryReceived.php>)
- [AUDITORIA_LANDING_BODAS.md](<C:/proyectos/renzo - fotografia/AUDITORIA_LANDING_BODAS.md>)
- [config/landing.php](<C:/proyectos/renzo - fotografia/config/landing.php>)
- [database/migrations/2026_11_12_000002_add_soft_deletes_to_landing_pages_table.php](<C:/proyectos/renzo - fotografia/database/migrations/2026_11_12_000002_add_soft_deletes_to_landing_pages_table.php>)
- [database/migrations/2026_11_12_000003_add_wedding_inquiries_and_landing_aliases.php](<C:/proyectos/renzo - fotografia/database/migrations/2026_11_12_000003_add_wedding_inquiries_and_landing_aliases.php>)
- [IMPLEMENTACION_LANDING_BODAS.md](<C:/proyectos/renzo - fotografia/IMPLEMENTACION_LANDING_BODAS.md>)
- [public/assets/icons/heroicons/LICENSE.txt](<C:/proyectos/renzo - fotografia/public/assets/icons/heroicons/LICENSE.txt>)
- [public/css/landing-editor.css](<C:/proyectos/renzo - fotografia/public/css/landing-editor.css>)
- [public/css/landing.css](<C:/proyectos/renzo - fotografia/public/css/landing.css>)
- [public/js/admin.js](<C:/proyectos/renzo - fotografia/public/js/admin.js>)
- [public/js/landing-editor.js](<C:/proyectos/renzo - fotografia/public/js/landing-editor.js>)
- [public/js/landing.js](<C:/proyectos/renzo - fotografia/public/js/landing.js>)
- [resources/views/admin/field.blade.php](<C:/proyectos/renzo - fotografia/resources/views/admin/field.blade.php>)
- [resources/views/admin/landing-editor.blade.php](<C:/proyectos/renzo - fotografia/resources/views/admin/landing-editor.blade.php>)
- [resources/views/admin/landing-leads.blade.php](<C:/proyectos/renzo - fotografia/resources/views/admin/landing-leads.blade.php>)
- [resources/views/admin/landings.blade.php](<C:/proyectos/renzo - fotografia/resources/views/admin/landings.blade.php>)
- [resources/views/pages/landing-sitemap.blade.php](<C:/proyectos/renzo - fotografia/resources/views/pages/landing-sitemap.blade.php>)
- [resources/views/pages/landing.blade.php](<C:/proyectos/renzo - fotografia/resources/views/pages/landing.blade.php>)
- [resources/views/pages/landing/calculator.blade.php](<C:/proyectos/renzo - fotografia/resources/views/pages/landing/calculator.blade.php>)
- [resources/views/pages/landing/form.blade.php](<C:/proyectos/renzo - fotografia/resources/views/pages/landing/form.blade.php>)
- [resources/views/pages/landing/icon.blade.php](<C:/proyectos/renzo - fotografia/resources/views/pages/landing/icon.blade.php>)
- [resources/views/pages/landing/stories.blade.php](<C:/proyectos/renzo - fotografia/resources/views/pages/landing/stories.blade.php>)
- [resources/views/pages/landing/video.blade.php](<C:/proyectos/renzo - fotografia/resources/views/pages/landing/video.blade.php>)
- [routes/console.php](<C:/proyectos/renzo - fotografia/routes/console.php>)
- [routes/web.php](<C:/proyectos/renzo - fotografia/routes/web.php>)
- [tests/Feature/LandingButtonTest.php](<C:/proyectos/renzo - fotografia/tests/Feature/LandingButtonTest.php>)
- [tests/Feature/LandingMediaExperienceTest.php](<C:/proyectos/renzo - fotografia/tests/Feature/LandingMediaExperienceTest.php>)
- [tests/Feature/LandingMediaTest.php](<C:/proyectos/renzo - fotografia/tests/Feature/LandingMediaTest.php>)
- [tests/Feature/WeddingInquiryTest.php](<C:/proyectos/renzo - fotografia/tests/Feature/WeddingInquiryTest.php>)
- [tests/Feature/WeddingLandingTest.php](<C:/proyectos/renzo - fotografia/tests/Feature/WeddingLandingTest.php>)
