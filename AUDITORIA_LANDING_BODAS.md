# Auditoría de la landing de bodas

Fecha de revisión: 12 de noviembre de 2026.

## Arquitectura encontrada

El proyecto utiliza PHP 8.3 y Laravel 13.34.0. La presentación utiliza Blade, CSS y JavaScript del proyecto. `package.json` incluye Vite 8 y Tailwind CSS 4; el editor de landings y su página pública cargan sus archivos desde `public/css` y `public/js`. No hay componentes Livewire instalados ni un editor paralelo que deba sustituirse.

Las campañas se administran mediante `LandingPageController`. Las rutas públicas existentes son `GET /lp/{slug}` y `POST /lp/{slug}/leads`; las rutas bajo `/admin/landings` utilizan los controles de autenticación y el permiso `manage-site`. La aplicación conserva por separado el JSON de borrador y el JSON publicado en `landing_pages`. Un contador de revisión evita sobrescribir cambios hechos desde otra ventana. La publicación es independiente de la publicación del sitio principal.

`LandingContent` obtiene los campos editables desde `config/landing.php`, completa los campos nuevos que faltan en campañas anteriores, normaliza los valores y valida los tipos permitidos. `resources/views/admin/landing-editor.blade.php` reutiliza `admin.field` para renderizar los grupos y elementos repetibles. `public/js/landing-editor.js` solicita una vista previa privada de los cambios sin publicarlos. La vista previa desactiva formularios y medición comercial.

Las secciones actuales se almacenan en un arreglo ordenado dentro del contenido de la campaña. Los beneficios, fotografías, testimonios y preguntas también utilizan elementos repetibles. Esta arquitectura permite ampliar los tipos de sección y sus colecciones sin crear tablas de contenido duplicadas ni reescribir las landings existentes.

`landing_leads` conserva las solicitudes confirmadas por el backend, el consentimiento y los parámetros UTM. Un identificador único de envío evita duplicados; el formulario incluye protección CSRF, campo trampa, validación y límite de solicitudes. Los eventos de Meta `ViewContent` y `Lead` utilizan soles peruanos (`PEN`); el evento de prospecto depende de un registro real en el sistema.

El modelo `LandingPage` usa eliminación recuperable mediante `deleted_at`. La migración incremental correspondiente debe ejecutarse en el mismo entorno y base de datos de la aplicación; subir solamente vistas o controladores provoca incompatibilidades.

## Multimedia y configuración general

`MediaAsset`, `MediaController`, `CloudflareImages` y `CloudflareR2` permiten reutilizar la biblioteca actual. Las imágenes originales están en `public/assets/images`. Los archivos subidos conservan una URL `/media/{id}` que resuelve el almacenamiento local o el proveedor configurado. No es necesario sobrescribir imágenes originales ni cambiar el mecanismo de almacenamiento.

El sitio principal utiliza `SiteContent`, `SiteSetting` y `config/editor.php`. Sus ajustes también distinguen borrador y contenido publicado. Los datos del sitio principal deben importarse desde sus valores publicados; un valor predeterminado del repositorio no acredita por sí solo una cifra, testimonio o reconocimiento real.

Las opciones de tipografía proceden de `config/editor.php`; la landing actual ya incluye tipografías configurables, colores, tamaños, animaciones, portada de foto/flyer/carrusel/video, miniatura, botón flotante y modelos de botones. El rediseño debe conservar estas capacidades y permitir elegir la composición editorial de bodas sin imponerla sobre otras campañas.

## Fuentes públicas revisadas

Se verificó una respuesta HTTP 200 y se leyó el HTML público de estas páginas del propio negocio:

- [Landing de bodas publicada](https://renzonunezmelgar.com/public/lp/bodas).
- [Inicio](https://renzonunezmelgar.com/public/).
- [Sobre mí](https://renzonunezmelgar.com/public/sobre-mi).
- [Portafolio](https://renzonunezmelgar.com/public/portafolio).
- [Servicios](https://renzonunezmelgar.com/public/servicios).
- [Contacto](https://renzonunezmelgar.com/public/contacto).

El lector web no pudo acceder a esas páginas; la comprobación se realizó con una petición HTTP de solo lectura. No se enviaron formularios ni se modificó el servidor publicado.

## Información y fotografías reutilizables

| Contenido | Ubicación del proyecto | Resultado de la revisión |
| --- | --- | --- |
| Marca y contacto | `branding` en `config/editor.php` | El sitio publica el correo `info@renzonunezmelgar.com` y base en Arequipa. El teléfono e Instagram predeterminados son genéricos. |
| Trayectoria | `home_experience` | El principal muestra +150 bodas y 8 años, idénticos a los defaults del repositorio. Importar cifras automáticamente solo cuando exista contenido publicado explícito en `SiteSetting`. |
| Filosofía y biografía | `home_about_philosophy`, `about_about_hero`, `about_about_story` | Existe una presentación documental/editorial y descripción del trabajo en Arequipa y destinos. Se pueden conservar como contenido editable. |
| Servicios | `service_wedding`, `service_prewedding`, `service_postwedding` | Existen Documental de Boda, Sesiones Preboda y Elopements & Destino. No se encontraron tarifas publicadas. |
| Portafolio | `portfolio_items.items` | Hay seis parejas con nombre, categoría, ubicación y una portada por entrada. No existen galerías completas ni fechas por historia. |
| Opiniones | `testimonials.items` | El principal publica tres opiniones que coinciden con los defaults. Importarlas automáticamente solo si el administrador ya guardó contenido publicado explícito. No hay valoraciones ni enlaces a reseñas originales. |
| Recursos visuales | `public/assets/images/{hero,portfolio,services,details}` | Hay 17 imágenes WebP; no se localizaron videos originales ni un retrato verificado de Renzo en esos recursos. |

Las entradas de portafolio existentes son Camila & Sebastián (Valle Sagrado, Cusco), Mariana & Diego (Arequipa), Lucía & Mateo (Máncora, Piura), Valentina & Alejandro (Lima), Sofía & Gabriel (Colca, Arequipa) e Isabella & Thomas (Paracas, Ica). Se debe conservar la correspondencia entre nombre y fotografía y evitar completar una historia con imágenes de otra pareja.

## Advertencias de contenido

1. **Retrato incorrecto en el sitio principal.** `about_about_hero.field_1` apunta por defecto a `assets/images/portfolio/isabella-thomas.webp`. La inspección visual muestra una novia en un campo de flores. No representa a Renzo y no debe importarse como su retrato. El campo del nuevo perfil debe quedar vacío hasta seleccionar una fotografía válida de la biblioteca.
2. **Contacto provisional.** `51900000000`, `+51 900 000 000` y `https://instagram.com` son placeholders. No deben promoverse a destinos comerciales verificados. Se necesita configurar el WhatsApp y el perfil de Instagram reales.
3. **Cifras y opiniones predeterminadas.** Aunque aparecen en el sitio principal, coinciden con el contenido inicial del proyecto. Su importación automática exige un valor publicado explícitamente por el administrador. No añadir premios, publicaciones, clientes, opiniones ni cifras nuevas.
4. **Galerías incompletas.** Cada entrada del portafolio tiene una portada. El administrador deberá completar las galerías reales de cada boda; una única foto no debe presentarse como un reportaje completo.
5. **Tarifas y políticas.** No existen importes configurados para construir un estimado fiable. Una calculadora debe permanecer oculta hasta disponer de tarifas y opciones reales. No añadir plazos de entrega, condiciones de pago ni confirmaciones automáticas de disponibilidad.
6. **Sin acceso a la base de datos publicada.** No se inspeccionaron datos privados del servidor. Boost no pudo consultar MySQL local porque la conexión fue rechazada; su conexión SQLite heredaba un nombre de base de datos que no corresponde a un archivo. No se cambiaron las credenciales ni la configuración para realizar esta auditoría.

## Dirección del rediseño

La composición de bodas debe utilizar fotografías de gran formato, blanco cálido, carbón y acentos champagne discretos. La jerarquía comercial propuesta es impacto emocional, confianza verificable, historias reales, enfoque del fotógrafo, experiencias, testimonios y consulta. El CTA consistente será consultar disponibilidad; un envío exitoso confirmará la recepción de la consulta, sin afirmar que una fecha está libre o reservada.

Las secciones y sus colecciones deben permanecer editables, ordenables y ocultables. El nuevo preset de bodas debe prepararse como borrador, conservar los contenidos existentes al aplicarlo y dejar la versión publicada intacta hasta que el administrador decida publicar. Los contenidos ausentes deben omitirse sin producir espacios vacíos. Las galerías completas deben cargar sus fotografías al abrir la historia, no descargar todos sus recursos durante la primera visita.

## URL y despliegue

La ruta Laravel actual es `/lp/bodas`. El prefijo `/public` de la URL publicada depende de la configuración del hosting. No se debe eliminar desde rutas ni introducir una redirección global sin revisar antes el document root del dominio. Si posteriormente se acuerda una URL pública más corta, se debe verificar la configuración y conservar una redirección 301 desde los enlaces usados por campañas.

Antes de publicar, el administrador deberá revisar el borrador de bodas, completar retrato, WhatsApp, galerías reales y cualquier dato comercial pendiente; después podrá guardar y publicar desde el sistema existente. El despliegue debe incluir todos los archivos del cambio y las migraciones incrementales aplicables, y limpiar la caché de configuración y vistas del entorno correspondiente.

## Ampliación implementada

La dirección visual de bodas se selecciona mediante `visual_theme=wedding`. El sistema conserva el tema editorial original para campañas anteriores. Se incorporan los tipos administrables `trust`, `stories`, `about`, `experiences`, `process` y `closing`, junto con navegación, portada móvil independiente, biografía, formulario en dos pasos y contacto del pie de página.

Las colecciones nuevas son `trust_metrics`, `stories` (con `images` anidado), `experiences`, `process_steps` y `calculator_rates`. Cada colección permite reordenar sus elementos y controlar su visibilidad. Los testimonios admiten fotografía, lugar, fecha, valoración real y enlace original. Una colección vacía u oculta no produce una sección pública vacía.

El método `LandingContent::weddingDraft()` importa las fuentes existentes cuando faltan esos contenidos, conserva los textos y recursos de la campaña y prepara el cambio como borrador. La acción administrativa utiliza el contador de revisión existente. El administrador puede revisar los cambios en tiempo real, guardar y publicar por separado.

La migración incremental `2026_11_12_000003_add_wedding_inquiries_and_landing_aliases.php` añade dos columnas JSON opcionales:

- `landing_leads.inquiry_details`: origen, slug vigente de la landing, ciudad y una copia del nombre de la experiencia publicada seleccionada. El navegador envía el índice original de una experiencia habilitada; el backend obtiene su nombre desde el contenido publicado.
- `landing_pages.previous_slugs`: enlaces anteriores conservados al editar el slug. Un GET anterior redirige con estado 301 y conserva los parámetros de campaña; un formulario abierto con el enlace anterior sigue guardando la solicitud. Los enlaces actuales e históricos siguen reservados mientras la landing está en la papelera.

El endpoint `/landings-sitemap.xml` incluye las URL vigentes de las campañas publicadas fuera de la papelera. La medición incorpora GA4 configurable y mantiene los eventos de prospecto ligados a una recepción real confirmada. La notificación opcional al correo del administrador se envía una sola vez por solicitud nueva si el servidor tiene un canal activo; un fallo de entrega no pierde la consulta guardada.

El editor añade protección frente a formularios truncados por los límites de PHP: si falta el indicador final del formulario, conserva el borrador guardado y devuelve un error de validación. El guardado JavaScript envía el contenido estructurado y recibe el destino del editor desde el backend.

## Preparación y uso

1. Desplegar el conjunto completo de archivos y ejecutar las migraciones incrementales con la conexión del entorno correspondiente. Si la columna `deleted_at` todavía no existe, también debe aplicarse la migración anterior de papelera.
2. Limpiar las cachés de configuración, rutas y vistas del servidor tras actualizar los archivos.
3. Abrir la landing de bodas en el administrador y utilizar la acción de preparar el diseño editorial. La versión publicada permanece disponible mientras se revisa el borrador.
4. Configurar WhatsApp real, enlaces sociales y legales, retrato, fotografías móviles y galerías por pareja. Revisar métricas, opiniones y condiciones comerciales antes de hacerlas visibles.
5. Completar servicios y tarifas verificadas. El estimador solo se muestra si se activa y existe una cobertura con tarifa positiva.
6. Revisar la vista de escritorio y móvil, guardar y publicar la landing. Realizar una consulta de prueba y verificar que aparece en el panel.

También existe un comando operacional para preparar el borrador:

```bash
php artisan landing:wedding bodas
```

Para una landing existente, conserva previamente sus atributos en un JSON dentro de `storage/app/landing-backups` y mantiene la versión publicada. Cuando se desea crear una campaña nueva se puede usar `php artisan landing:wedding nuevo-slug --create`; nace privada. El comando rechaza slugs inválidos o reservados por otra landing, sus enlaces anteriores o la papelera.

El correo de avisos necesita un canal de correo real configurado en el servidor. Sin correo configurado, las consultas siguen guardándose y pueden revisarse desde el administrador. Meta Pixel y GA4 requieren los identificadores del negocio; las reservas no se registran como confirmadas mediante el simple envío del formulario.

## Validación añadida

`WeddingLandingTest` cubre la creación del preset, importación de datos publicados, conservación de contenidos al aplicar el diseño, permisos, edición y orden de colecciones anidadas, publicación, visibilidad, SEO, portada móvil, galerías diferidas y rechazo de enlaces o configuraciones inválidas.

`WeddingInquiryTest` cubre los campos de boda y su copia de la experiencia publicada, atribución UTM, reintentos sin duplicados, validación y spam, notificaciones y fallos de entrega, enlaces anteriores y parámetros de campaña, reserva de slugs incluidos los de la papelera, sitemap, comando operacional con respaldo y protección frente a formularios incompletos. Las pruebas utilizan datos ficticios identificados como pruebas y una base SQLite temporal en memoria.

Para mejorar la calidad comercial, conviene completar reportajes auténticos de cada pareja, utilizar testimonios con autorización y origen verificable y medir la proporción de visitas que inician y completan la consulta. El panel y los eventos permiten evaluar esos cambios; la calidad visual por sí sola no garantiza reservas.
