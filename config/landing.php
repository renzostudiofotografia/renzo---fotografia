<?php

$sectionFields = [
    'type' => ['label' => 'Tipo de sección', 'type' => 'select', 'options' => ['text' => 'Texto e imagen', 'hero' => 'Portada', 'benefits' => 'Beneficios', 'gallery' => 'Galería', 'offer' => 'Propuesta y precio', 'testimonials' => 'Testimonios', 'faq' => 'Preguntas frecuentes', 'lead' => 'Formulario de contacto']],
    'enabled' => ['label' => 'Mostrar sección', 'type' => 'boolean', 'default' => true],
    'eyebrow' => ['label' => 'Texto pequeño sobre el título', 'type' => 'text'],
    'title' => ['label' => 'Título', 'type' => 'text'],
    'text' => ['label' => 'Descripción', 'type' => 'textarea'],
    'image' => ['label' => 'Fotografía', 'type' => 'image'],
    'alt' => ['label' => 'Descripción accesible de la fotografía', 'type' => 'text'],
    'button_label' => ['label' => 'Texto del botón (opcional)', 'type' => 'text'],
    'button_url' => [
        'label' => 'Destino del botón', 'type' => 'link', 'default' => '#contacto',
        'help' => 'Escribe #contacto para el formulario de esta página o pega un enlace completo, por ejemplo https://wa.me/51987654321. El destino de la portada también se aplica al botón del encabezado y al botón fijo móvil.',
    ],
    'custom_background' => ['label' => 'Personalizar fondo de esta sección', 'type' => 'boolean', 'default' => false],
    'background' => ['label' => 'Color de fondo de la sección', 'type' => 'color', 'default' => '#f5f1e9'],
    'padding' => ['label' => 'Espaciado vertical (px)', 'type' => 'number', 'min' => 16, 'max' => 160, 'default' => 64],
];
$section = static fn (string $type, string $eyebrow, string $title, string $text, string $image = ''): array => [
    'type' => $type, 'enabled' => true, 'eyebrow' => $eyebrow, 'title' => $title, 'text' => $text,
    'image' => $image, 'alt' => '', 'button_label' => '', 'button_url' => '#contacto',
    'custom_background' => false, 'background' => '#f5f1e9', 'padding' => 64,
];

return [
    'groups' => [
        'campaign' => ['label' => 'Campaña y buscadores', 'description' => 'Identidad y presentación de esta landing.', 'fields' => [
            'brand_name' => ['label' => 'Nombre de la marca', 'type' => 'text'],
            'page_title' => ['label' => 'Título de la pestaña y buscadores', 'type' => 'text'],
            'meta_description' => ['label' => 'Descripción para buscadores', 'type' => 'textarea'],
            'share_image' => ['label' => 'Miniatura al compartir en Facebook y otros enlaces', 'type' => 'image', 'default' => '', 'help' => 'Elige una fotografía o el flyer para la vista previa del enlace. Si no eliges una, se usará la miniatura del video o la imagen de portada.'],
        ]],
        'design' => ['label' => 'Diseño y estilo', 'description' => 'Personaliza el aspecto completo de tu campaña.', 'fields' => [
            'heading_font' => ['label' => 'Tipografía de títulos', 'type' => 'font'],
            'body_font' => ['label' => 'Tipografía de textos', 'type' => 'font'],
            'heading_size' => ['label' => 'Tamaño del título principal (px)', 'type' => 'number', 'min' => 32, 'max' => 100],
            'body_size' => ['label' => 'Tamaño de los textos (px)', 'type' => 'number', 'min' => 14, 'max' => 24],
            'background_color' => ['label' => 'Fondo de la página', 'type' => 'color'],
            'text_color' => ['label' => 'Color del texto', 'type' => 'color'],
            'accent_color' => ['label' => 'Color de acento y botones', 'type' => 'color'],
            'button_text_color' => ['label' => 'Color del texto de los botones', 'type' => 'color'],
            'button_radius' => ['label' => 'Bordes de botones (px)', 'type' => 'number', 'min' => 0, 'max' => 50],
            'button_style' => ['label' => 'Estilo de botones', 'type' => 'select', 'options' => ['solid' => 'Relleno', 'outline' => 'Contorno']],
            'animation' => ['label' => 'Animaciones al aparecer', 'type' => 'select', 'options' => ['none' => 'Sin animación', 'fade' => 'Desvanecer', 'rise' => 'Aparecer desde abajo']],
            'hero_layout' => ['label' => 'Diseño de portada', 'type' => 'select', 'options' => ['split' => 'Texto y fotografía', 'full' => 'Fotografía a pantalla completa'], 'help' => 'En los modos flyer y video se conserva una composición dividida para mostrar el contenido completo y sus controles.'],
            'section_spacing' => ['label' => 'Separación general (px)', 'type' => 'number', 'min' => 24, 'max' => 120],
            'button_label' => ['label' => 'Texto de la llamada a la acción principal', 'type' => 'text'],
            'sticky_cta' => ['label' => 'Botón flotante mientras navegas (móvil y escritorio)', 'type' => 'boolean'],
        ]],
        'motion' => ['label' => 'Movimiento, luces y botones', 'description' => 'Efectos suaves que acompañan las fotografías y mantienen visible la acción principal.', 'fields' => [
            'sticky_header' => ['label' => 'Mantener el encabezado visible al navegar', 'type' => 'boolean', 'default' => true],
            'button_animation' => ['label' => 'Animación de los botones', 'type' => 'select', 'options' => ['none' => 'Sin animación', 'lift' => 'Elevación al pasar el cursor', 'pulse' => 'Pulso suave', 'shine' => 'Reflejo de luz'], 'default' => 'shine'],
            'light_effect' => ['label' => 'Efecto de luces en la portada', 'type' => 'select', 'options' => ['none' => 'Sin luces', 'soft' => 'Luz cálida suave', 'aurora' => 'Luces en movimiento'], 'default' => 'soft'],
            'light_color' => ['label' => 'Color de la luz', 'type' => 'color', 'default' => '#e4bd78'],
            'light_intensity' => ['label' => 'Intensidad de la luz (%)', 'type' => 'number', 'min' => 0, 'max' => 100, 'default' => 35],
            'image_motion' => ['label' => 'Movimiento de las fotografías', 'type' => 'select', 'options' => ['none' => 'Sin movimiento', 'zoom' => 'Acercamiento suave', 'pan' => 'Desplazamiento suave'], 'default' => 'zoom'],
        ]],
        'media' => ['label' => 'Portada multimedia', 'description' => 'Elige foto, flyer, carrusel o video. La imagen principal y el flyer se seleccionan en Secciones y contenido → Portada → Fotografía.', 'fields' => [
            'hero_media' => ['label' => 'Contenido de la portada', 'type' => 'select', 'options' => ['image' => 'Una fotografía', 'flyer' => 'Flyer completo, sin recortar', 'carousel' => 'Carrusel de fotografías', 'video' => 'Video con miniatura'], 'default' => 'image'],
            'hero_image_fit' => ['label' => 'Encuadre de la imagen', 'type' => 'select', 'options' => ['cover' => 'Llenar el espacio', 'contain' => 'Mostrar imagen completa'], 'default' => 'cover'],
            'hero_focal_point' => ['label' => 'Punto de enfoque de la imagen y miniatura', 'type' => 'select', 'options' => ['center' => 'Centro', 'top' => 'Parte superior', 'bottom' => 'Parte inferior'], 'default' => 'center'],
            'hero_images' => ['label' => 'Fotografías del carrusel de portada', 'type' => 'repeater', 'min' => 0, 'max' => 10, 'fields' => [
                'image' => ['label' => 'Fotografía', 'type' => 'image'], 'alt' => ['label' => 'Descripción accesible', 'type' => 'text'],
            ]],
            'hero_video_url' => ['label' => 'Enlace del video', 'type' => 'video', 'default' => '', 'help' => 'Pega el enlace HTTPS directo de un archivo MP4 o WebM, o sube el video desde este editor.'],
            'hero_video_poster' => ['label' => 'Miniatura del video', 'type' => 'image', 'default' => ''],
            'hero_video_autoplay' => ['label' => 'Reproducir el video automáticamente sin sonido', 'type' => 'boolean', 'default' => false],
            'carousel_autoplay' => ['label' => 'Avance automático de los carruseles', 'type' => 'boolean', 'default' => true],
            'carousel_interval' => ['label' => 'Segundos entre fotografías', 'type' => 'number', 'min' => 3, 'max' => 12, 'default' => 5],
        ]],
        'sections' => ['label' => 'Secciones y contenido', 'description' => 'Añade, ordena u oculta secciones. Los textos e imágenes se actualizan en la vista previa.', 'fields' => [
            'sections' => ['label' => 'Secciones de la landing', 'type' => 'repeater', 'min' => 1, 'max' => 20, 'fields' => $sectionFields],
        ]],
        'benefits' => ['label' => 'Beneficios', 'description' => 'Explica lo que hace especial tu propuesta.', 'fields' => [
            'benefits' => ['label' => 'Beneficios de la experiencia', 'type' => 'repeater', 'min' => 0, 'max' => 12, 'fields' => [
                'title' => ['label' => 'Título', 'type' => 'text'], 'text' => ['label' => 'Descripción', 'type' => 'textarea'],
            ]],
            'trust_points' => ['label' => 'Mensajes breves junto a la acción principal', 'type' => 'repeater', 'min' => 0, 'max' => 4, 'fields' => [
                'text' => ['label' => 'Mensaje de confianza', 'type' => 'text'],
            ]],
        ]],
        'gallery' => ['label' => 'Galería', 'description' => 'Selecciona fotografías de la biblioteca del estudio.', 'fields' => [
            'gallery_layout' => ['label' => 'Presentación de la galería', 'type' => 'select', 'options' => ['grid' => 'Galería editorial', 'carousel' => 'Carrusel con controles'], 'default' => 'carousel'],
            'gallery' => ['label' => 'Fotografías', 'type' => 'repeater', 'min' => 0, 'max' => 18, 'fields' => [
                'image' => ['label' => 'Fotografía', 'type' => 'image'], 'alt' => ['label' => 'Descripción accesible', 'type' => 'text'],
            ]],
        ]],
        'offer' => ['label' => 'Propuesta y precio', 'description' => 'Todos los importes se muestran en soles peruanos (S/).', 'fields' => [
            'offer_label' => ['label' => 'Nombre de la propuesta', 'type' => 'text'],
            'price' => ['label' => 'Precio en soles (0 para consultar)', 'type' => 'decimal', 'min' => 0, 'max' => 1000000],
            'price_note' => ['label' => 'Condiciones o detalle del precio', 'type' => 'textarea'],
        ]],
        'testimonials' => ['label' => 'Testimonios', 'description' => 'Añade únicamente opiniones reales con autorización. Esta sección se oculta mientras no haya opiniones.', 'fields' => [
            'testimonials' => ['label' => 'Opiniones de clientes', 'type' => 'repeater', 'min' => 0, 'max' => 12, 'fields' => [
                'name' => ['label' => 'Nombre de quien opina', 'type' => 'text'], 'quote' => ['label' => 'Testimonio', 'type' => 'textarea'],
                'image' => ['label' => 'Fotografía de quien opina', 'type' => 'image', 'default' => ''],
                'role' => ['label' => 'Detalle de la experiencia (opcional)', 'type' => 'text', 'default' => ''],
            ]],
        ]],
        'faqs' => ['label' => 'Preguntas frecuentes', 'description' => 'Resuelve dudas antes de que el visitante solicite información.', 'fields' => [
            'faqs' => ['label' => 'Preguntas y respuestas', 'type' => 'repeater', 'min' => 0, 'max' => 15, 'fields' => [
                'question' => ['label' => 'Pregunta', 'type' => 'text'], 'answer' => ['label' => 'Respuesta', 'type' => 'textarea'],
            ]],
        ]],
        'conversion' => ['label' => 'Contacto y Meta Pixel', 'description' => 'Conecta el ID de tu píxel. ViewContent mide visitas; Lead se envía después de guardar un contacto.', 'fields' => [
            'pixel_id' => ['label' => 'ID de Meta Pixel (solo números)', 'type' => 'pixel'],
            'track_view_content' => ['label' => 'Medir visitas con ViewContent', 'type' => 'boolean'],
            'track_lead' => ['label' => 'Medir clientes potenciales con Lead', 'type' => 'boolean'],
            'lead_value' => ['label' => 'Valor de un cliente potencial en soles', 'type' => 'decimal', 'min' => 0, 'max' => 1000000],
            'whatsapp' => ['label' => 'WhatsApp con código de país (ej. 51987654321)', 'type' => 'phone'],
            'submit_label' => ['label' => 'Texto del botón del formulario', 'type' => 'text'],
            'privacy_text' => ['label' => 'Texto de autorización de contacto', 'type' => 'textarea'],
            'success_message' => ['label' => 'Mensaje después de enviar', 'type' => 'textarea'],
        ]],
    ],
    'defaults' => [
        'brand_name' => 'RENZO · FOTOGRAFÍA', 'page_title' => 'Su historia, en fotografías',
        'share_image' => '',
        'meta_description' => 'Fotografía de bodas y sesiones para conservar lo que más importa. Cuéntanos tu historia y consulta una propuesta personalizada.',
        'heading_font' => 'Playfair Display', 'body_font' => 'DM Sans', 'heading_size' => 72, 'body_size' => 16,
        'background_color' => '#fcfaf6', 'text_color' => '#262b26', 'accent_color' => '#465344', 'button_text_color' => '#ffffff',
        'button_radius' => 4, 'button_style' => 'solid', 'animation' => 'rise', 'hero_layout' => 'split',
        'section_spacing' => 64, 'sticky_cta' => true, 'button_label' => 'Quiero contar mi historia',
        'sticky_header' => true, 'button_animation' => 'shine', 'light_effect' => 'soft', 'light_color' => '#e4bd78', 'light_intensity' => 35, 'image_motion' => 'zoom',
        'hero_media' => 'image', 'hero_image_fit' => 'cover', 'hero_focal_point' => 'center',
        'hero_video_url' => '', 'hero_video_poster' => '', 'hero_video_autoplay' => false,
        'carousel_autoplay' => true, 'carousel_interval' => 5, 'gallery_layout' => 'carousel',
        'hero_images' => [
            ['image' => 'assets/images/hero/hero-01.webp', 'alt' => 'Una historia de boda en fotografías'],
            ['image' => 'assets/images/hero/hero-02.webp', 'alt' => 'Un encuentro para recordar'],
            ['image' => 'assets/images/hero/hero-03.webp', 'alt' => 'La emoción de una celebración'],
        ],
        'offer_label' => 'Una propuesta para ustedes', 'price' => 0, 'price_note' => 'La propuesta se prepara de acuerdo con la fecha, el lugar y los detalles de su celebración.',
        'pixel_id' => '', 'track_view_content' => true, 'track_lead' => true, 'lead_value' => 0, 'whatsapp' => '',
        'submit_label' => 'Solicitar mi propuesta', 'privacy_text' => 'Autorizo al estudio a utilizar mis datos para responder a esta solicitud y contactarme sobre el servicio consultado.',
        'success_message' => 'Gracias por escribirnos. Recibimos tu solicitud y pronto nos pondremos en contacto.',
        'sections' => [
            $section('hero', 'FOTOGRAFÍA CON INTENCIÓN', 'Hay instantes que merecen quedarse para siempre.', 'Vivan su historia. Nosotros la convertimos en recuerdos que podrán sentir una y otra vez.', 'assets/images/hero/hero-01.webp'),
            $section('benefits', 'SU HISTORIA, A SU MANERA', 'Más que fotografías. Una experiencia para recordar.', 'Conversemos sobre lo que imaginan para crear una propuesta que conecte con ustedes.'),
            $section('gallery', 'UNA MIRADA AL TRABAJO', 'Historias que se sienten.', 'Luz, emoción y los pequeños detalles de cada encuentro.'),
            $section('testimonials', 'SUS PALABRAS', 'Historias que también se cuentan con palabras.', ''),
            $section('offer', 'SU PRÓXIMO RECUERDO', 'Todo comienza con una conversación.', 'Cuéntennos su fecha y lo que tienen en mente. Revisaremos la disponibilidad y los acompañaremos a elegir la cobertura.'),
            $section('faq', 'ANTES DE DAR EL SÍ', 'Lo que quizá se estén preguntando.', ''),
            $section('lead', 'HAGAMOS ESPACIO PARA SU HISTORIA', 'Cuéntennos lo que están soñando.', 'Dejen sus datos y conversemos sobre su celebración. Sin compromiso.'),
        ],
        'benefits' => [
            ['title' => 'Una mirada natural', 'text' => 'Espacio para vivir el momento, sin dejar de ser ustedes.'],
            ['title' => 'Atención a los detalles', 'text' => 'Una historia contada también en los gestos y encuentros pequeños.'],
            ['title' => 'Una propuesta a su medida', 'text' => 'La fecha, el lugar y sus ideas son el punto de partida.'],
        ],
        'gallery' => [
            ['image' => 'assets/images/portfolio/sofia-gabriel.webp', 'alt' => 'Historia de boda fotografiada por el estudio'],
            ['image' => 'assets/images/portfolio/valentina-alejandro.webp', 'alt' => 'Instantes de una celebración'],
            ['image' => 'assets/images/portfolio/mariana-diego.webp', 'alt' => 'Fotografía de pareja'],
        ],
        'testimonials' => [],
        'trust_points' => [
            ['text' => 'Propuesta personalizada'],
            ['text' => 'Consulta sin compromiso'],
            ['text' => 'Atención directa'],
        ],
        'faqs' => [
            ['question' => '¿Cómo consultamos la disponibilidad?', 'answer' => 'Envíen el formulario con la fecha y el lugar de su celebración. El estudio les responderá para revisar los detalles.'],
            ['question' => '¿Podemos personalizar la propuesta?', 'answer' => 'Sí. Cuéntennos el tipo de celebración y lo que necesitan para preparar una propuesta acorde a su historia.'],
            ['question' => '¿Enviar el formulario confirma una reserva?', 'answer' => 'El formulario es una solicitud de información. La disponibilidad y las condiciones de reserva se confirman directamente con el estudio.'],
        ],
    ],
];
