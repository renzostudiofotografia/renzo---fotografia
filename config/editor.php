<?php

return [
    'sections' => [
        'home_hero' => [
            'label' => 'Portada y fotografías',
            'group' => 'Inicio',
            'page' => 'home',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'phrases' => [
                    'label' => 'Frases románticas · una por línea',
                    'type' => 'lines',
                ],
                'field_2' => [
                    'label' => 'Texto del botón o enlace',
                    'type' => 'text',
                ],
                'button_url' => [
                    'label' => 'Destino del botón',
                    'type' => 'link',
                ],
                'interval' => [
                    'label' => 'Segundos entre fotografías',
                    'type' => 'number',
                    'min' => 4,
                    'max' => 20,
                ],
                'images' => [
                    'label' => 'Fotografías del carrusel',
                    'type' => 'repeater',
                    'fields' => [
                        'image' => [
                            'label' => 'Fotografía',
                            'type' => 'image',
                        ],
                    ],
                    'min' => 1,
                    'max' => 15,
                ],
                'page_title' => [
                    'label' => 'Título de la pestaña y buscadores',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 0,
                'field_2' => 'Ver Portafolio',
                'images' => [
                    0 => [
                        'image' => 'assets/images/hero/hero-01.webp',
                    ],
                    1 => [
                        'image' => 'assets/images/hero/hero-02.webp',
                    ],
                    2 => [
                        'image' => 'assets/images/hero/hero-03.webp',
                    ],
                    3 => [
                        'image' => 'assets/images/hero/hero-04.webp',
                    ],
                    4 => [
                        'image' => 'assets/images/hero/hero-05.webp',
                    ],
                ],
                'phrases' => [
                    0 => 'Donde comienza su para siempre',
                    1 => 'Dos almas, una misma historia',
                    2 => 'El amor vive en los pequeños instantes',
                    3 => 'Su historia merece ser recordada',
                    4 => 'Un sí, una vida, mil recuerdos',
                    5 => 'Juntos, en cada nuevo amanecer',
                    6 => 'Miradas que lo dicen todo',
                    7 => 'Su amor, su día, su historia',
                ],
                'interval' => 7,
                'button_url' => '#portafolio',
                'page_title' => 'Fotografía de Bodas',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'home_experience' => [
            'label' => 'Experiencia y trayectoria',
            'group' => 'Inicio',
            'page' => 'home',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Bodas Documentadas',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Años de Trayectoria',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Texto · “Fotografiar es colocar la cabeza, el ojo y e…',
                    'type' => 'text',
                ],
                'field_4' => [
                    'label' => '— Henri Cartier-Bresson',
                    'type' => 'text',
                ],
                'weddings' => [
                    'label' => 'Número de bodas documentadas',
                    'type' => 'text',
                ],
                'years' => [
                    'label' => 'Años de trayectoria',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 1,
                'field_1' => 'Bodas Documentadas',
                'field_2' => 'Años de Trayectoria',
                'field_3' => '“Fotografiar es colocar la cabeza, el ojo y el corazón en el mismo eje.”',
                'field_4' => '— Henri Cartier-Bresson',
                'weddings' => '+150',
                'years' => '8',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'home_about_philosophy' => [
            'label' => 'Mi filosofía',
            'group' => 'Inicio',
            'page' => 'home',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Fotografía',
                    'type' => 'image',
                ],
                'field_2' => [
                    'label' => 'Fotografía',
                    'type' => 'image',
                ],
                'field_3' => [
                    'label' => 'Fotografía',
                    'type' => 'image',
                ],
                'field_4' => [
                    'label' => 'Descripción de la imagen',
                    'type' => 'text',
                ],
                'field_5' => [
                    'label' => 'Descripción de la imagen',
                    'type' => 'text',
                ],
                'field_6' => [
                    'label' => 'Descripción de la imagen',
                    'type' => 'text',
                ],
                'field_7' => [
                    'label' => 'Mi Filosofía',
                    'type' => 'text',
                ],
                'field_8' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_9' => [
                    'label' => 'Texto · Creo en la belleza de lo espontáneo, en las m…',
                    'type' => 'textarea',
                ],
                'field_10' => [
                    'label' => 'Texto · Mi enfoque combina la estética editorial con …',
                    'type' => 'textarea',
                ],
                'field_11' => [
                    'label' => 'Conoce más sobre mí',
                    'type' => 'text',
                ],
                'field_12' => [
                    'label' => 'Destino del enlace',
                    'type' => 'link',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 2,
                'field_1' => 'assets/images/details/rings.webp',
                'field_2' => 'assets/images/details/dress.webp',
                'field_3' => 'assets/images/details/flowers.webp',
                'field_4' => 'Detalle de anillos de boda',
                'field_5' => 'Detalle de vestido de boda',
                'field_6' => 'Detalle de flores de boda',
                'field_7' => 'Mi Filosofía',
                'field_8' => 'Documentando emociones con alma y elegancia',
                'field_9' => 'Creo en la belleza de lo espontáneo, en las miradas cómplices que pasan desapercibidas y en los pequeños detalles que hacen de tu boda algo irrepetible.',
                'field_10' => 'Mi enfoque combina la estética editorial con la sutileza del fotoperiodismo. Sin poses forzadas, simplemente contando tu historia tal como se siente: auténtica, atemporal y llena de vida.',
                'field_11' => 'Conoce más sobre mí',
                'field_12' => '/sobre-mi',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'home_services_featured' => [
            'label' => 'Servicios destacados',
            'group' => 'Inicio',
            'page' => 'home',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Fine Art',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Texto del botón o enlace',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 3,
                'field_1' => 'Fine Art',
                'field_2' => 'Sesiones, Bodas y Destino',
                'field_3' => 'Descubrir experiencia',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'home_portfolio_featured' => [
            'label' => 'Portafolio destacado',
            'group' => 'Inicio',
            'page' => 'home',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Galería Seleccionada',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Explorar Reportaje de Bodas',
                    'type' => 'text',
                ],
                'field_4' => [
                    'label' => 'Destino del enlace',
                    'type' => 'link',
                ],
                'field_5' => [
                    'label' => 'Destino del enlace',
                    'type' => 'link',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 4,
                'field_1' => 'Galería Seleccionada',
                'field_2' => 'Experiencias',
                'field_3' => 'Explorar Reportaje de Bodas',
                'field_4' => '/portafolio',
                'field_5' => '/portafolio',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'home_testimonials' => [
            'label' => 'Testimonios',
            'group' => 'Inicio',
            'page' => 'home',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Palabras del Corazón',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 5,
                'field_1' => 'Palabras del Corazón',
                'field_2' => 'Dedicatorias y Comentarios',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'home_cta_section' => [
            'label' => 'Invitación final',
            'group' => 'Inicio',
            'page' => 'home',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Planifiquemos tu Boda',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Texto · Las fechas para la temporada suelen reservars…',
                    'type' => 'textarea',
                ],
                'field_4' => [
                    'label' => 'Verificar Disponibilidad',
                    'type' => 'text',
                ],
                'field_5' => [
                    'label' => 'Contáctanos al WhatsApp',
                    'type' => 'text',
                ],
                'field_6' => [
                    'label' => 'Destino del enlace',
                    'type' => 'link',
                ],
                'background' => [
                    'label' => 'Fotografía de fondo',
                    'type' => 'image',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 6,
                'field_1' => 'Planifiquemos tu Boda',
                'field_2' => 'Hagamos que tus recuerdos sean eternos',
                'field_3' => 'Las fechas para la temporada suelen reservarse con meses de anticipación. Cuéntame sobre tu boda, la locación y la visión que tienen juntos.',
                'field_4' => 'Verificar Disponibilidad',
                'field_5' => 'Contáctanos al WhatsApp',
                'field_6' => '/contacto',
                'background' => 'assets/images/portfolio/sofia-gabriel.webp',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'about_about_hero' => [
            'label' => 'Presentación',
            'group' => 'Sobre mí',
            'page' => 'about',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Fotografía',
                    'type' => 'image',
                ],
                'field_2' => [
                    'label' => 'Detrás del Lente',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Título principal',
                    'type' => 'text',
                ],
                'field_4' => [
                    'label' => 'Texto · Fotógrafo de bodas enfocado en capturar emoci…',
                    'type' => 'textarea',
                ],
                'field_5' => [
                    'label' => 'Texto · Con base en Arequipa y disponible para docume…',
                    'type' => 'textarea',
                ],
                'page_title' => [
                    'label' => 'Título de la pestaña y buscadores',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 0,
                'field_1' => 'assets/images/portfolio/isabella-thomas.webp',
                'field_2' => 'Detrás del Lente',
                'field_3' => 'Hola, soy Renzo',
                'field_4' => 'Fotógrafo de bodas enfocado en capturar emociones reales con un lenguaje visual honesto, atemporal y profundamente elegante.',
                'field_5' => 'Con base en Arequipa y disponible para documentar historias de amor por todo el Perú y destinos internacionales.',
                'page_title' => 'Sobre Mí',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'about_about_story' => [
            'label' => 'Mi historia',
            'group' => 'Sobre mí',
            'page' => 'about',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Texto · Entiendo la fotografía de boda no como una li…',
                    'type' => 'textarea',
                ],
                'field_3' => [
                    'label' => 'Texto · Busco la luz natural, la complicidad en las m…',
                    'type' => 'textarea',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 1,
                'field_1' => 'Mi Filosofía Fotográfica',
                'field_2' => 'Entiendo la fotografía de boda no como una lista de posados obligatorios, sino como un testimonio visual con alma. Mi objetivo es que dentro de 20 o 30 años, cuando vuelvan a abrir su álbum, no solo recuerden cómo se veían, sino exactamente cómo se sentían.',
                'field_3' => 'Busco la luz natural, la complicidad en las miradas fugaces y el valor de los instantes no planificados: el abrazo apretado de un abuelo, las lágrimas contenidas antes de caminar al altar o las risas espontáneas a mitad del baile.',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'about_about_pillars' => [
            'label' => 'Forma de trabajo',
            'group' => 'Sobre mí',
            'page' => 'about',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Forma de Trabajo',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_4' => [
                    'label' => 'Texto · Trabajo de forma discreta, integrándome como …',
                    'type' => 'textarea',
                ],
                'field_5' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_6' => [
                    'label' => 'Texto · Evito modas pasajeras. Apuesto por colores fi…',
                    'type' => 'textarea',
                ],
                'field_7' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_8' => [
                    'label' => 'Texto · Construyo una relación cercana antes del gran…',
                    'type' => 'textarea',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 2,
                'field_1' => 'Forma de Trabajo',
                'field_2' => 'Los Pilares de mi Enfoque',
                'field_3' => 'Sutileza',
                'field_4' => 'Trabajo de forma discreta, integrándome como un invitado más para que vivan su día con serenidad y sin la presión de la cámara.',
                'field_5' => 'Atemporalidad',
                'field_6' => 'Evito modas pasajeras. Apuesto por colores fieles, blancos y negros expresivos y tonos cálidos que trasciendan las décadas.',
                'field_7' => 'Conexión',
                'field_8' => 'Construyo una relación cercana antes del gran día para que frente al lente exista confianza genuina y fluidez absoluta.',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'about_about_personal' => [
            'label' => 'Fuera del lente',
            'group' => 'Sobre mí',
            'page' => 'about',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Fotografía',
                    'type' => 'image',
                ],
                'field_2' => [
                    'label' => 'Descripción de la imagen',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Un Poco Más Sobre Mí',
                    'type' => 'text',
                ],
                'field_4' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_5' => [
                    'label' => 'Amante de los viajes:',
                    'type' => 'text',
                ],
                'field_6' => [
                    'label' => 'Texto · recorrer paisajes andinos y costas buscando n…',
                    'type' => 'text',
                ],
                'field_7' => [
                    'label' => 'Detallista por naturaleza:',
                    'type' => 'text',
                ],
                'field_8' => [
                    'label' => 'Texto · encuentro belleza en las texturas, la arquite…',
                    'type' => 'text',
                ],
                'field_9' => [
                    'label' => 'Buen café:',
                    'type' => 'text',
                ],
                'field_10' => [
                    'label' => 'Texto · nada supera una conversación tranquila antes …',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 3,
                'field_1' => 'assets/images/portfolio/sofia-gabriel.webp',
                'field_2' => 'Paisaje y fotografía de boda',
                'field_3' => 'Un Poco Más Sobre Mí',
                'field_4' => 'Fuera del Lente',
                'field_5' => 'Amante de los viajes:',
                'field_6' => 'recorrer paisajes andinos y costas buscando nuevas perspectivas de luz.',
                'field_7' => 'Detallista por naturaleza:',
                'field_8' => 'encuentro belleza en las texturas, la arquitectura clásica y el diseño limpio.',
                'field_9' => 'Buen café:',
                'field_10' => 'nada supera una conversación tranquila antes de iniciar una sesión.',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'about_portfolio_cta' => [
            'label' => 'Llamado a la acción',
            'group' => 'Sobre mí',
            'page' => 'about',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Texto · Escríbanme y conversemos sobre los detalles d…',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Iniciar Conversación',
                    'type' => 'text',
                ],
                'field_4' => [
                    'label' => 'Destino del enlace',
                    'type' => 'link',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 4,
                'field_1' => '¿Sienten que conectamos con esta visión?',
                'field_2' => 'Escríbanme y conversemos sobre los detalles de su boda con un buen café o videollamada.',
                'field_3' => 'Iniciar Conversación',
                'field_4' => '/contacto',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'portfolio_page_header' => [
            'label' => 'Encabezado',
            'group' => 'Portafolio',
            'page' => 'portfolio',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Galería & Historias',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Título principal',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Texto · Una mirada honesta a instantes irrepetibles. …',
                    'type' => 'textarea',
                ],
                'page_title' => [
                    'label' => 'Título de la pestaña y buscadores',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 0,
                'field_1' => 'Galería & Historias',
                'field_2' => 'Portafolio Seleccionado',
                'field_3' => 'Una mirada honesta a instantes irrepetibles. Cada galería refleja la emoción genuina, la estética atemporal y la luz natural de cada historia.',
                'page_title' => 'Portafolio',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'portfolio_portfolio_page_section' => [
            'label' => 'Galería',
            'group' => 'Portafolio',
            'page' => 'portfolio',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Todos',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Bodas',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Preboda & Postboda',
                    'type' => 'text',
                ],
                'field_4' => [
                    'label' => 'Elopements & Destino',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 1,
                'field_1' => 'Todos',
                'field_2' => 'Bodas',
                'field_3' => 'Preboda & Postboda',
                'field_4' => 'Elopements & Destino',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'portfolio_portfolio_cta' => [
            'label' => 'Llamado a la acción',
            'group' => 'Portafolio',
            'page' => 'portfolio',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Texto · Cuéntenme sobre su fecha y locación para revi…',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Consultar Fecha',
                    'type' => 'text',
                ],
                'field_4' => [
                    'label' => 'Destino del enlace',
                    'type' => 'link',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 2,
                'field_1' => '¿Les gustaría que documente su gran día?',
                'field_2' => 'Cuéntenme sobre su fecha y locación para revisar disponibilidad en mi agenda.',
                'field_3' => 'Consultar Fecha',
                'field_4' => '/contacto',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'services_page_header' => [
            'label' => 'Encabezado',
            'group' => 'Servicios',
            'page' => 'services',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Experiencias Fotográficas',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Título principal',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Texto · Cada experiencia está diseñada para preservar…',
                    'type' => 'text',
                ],
                'page_title' => [
                    'label' => 'Título de la pestaña y buscadores',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 0,
                'field_1' => 'Experiencias Fotográficas',
                'field_2' => 'Servicios con Intención',
                'field_3' => 'Cada experiencia está diseñada para preservar su historia con calma, dirección sutil y una mirada editorial.',
                'page_title' => 'Servicios',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'services_services_featured' => [
            'label' => 'Experiencias',
            'group' => 'Servicios',
            'page' => 'services',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Texto del botón o enlace',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 1,
                'field_1' => 'Ver experiencia',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'services_portfolio_cta' => [
            'label' => 'Llamado a la acción',
            'group' => 'Servicios',
            'page' => 'services',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Texto · Compartan la fecha, locación y visión de su c…',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Solicitar Propuesta',
                    'type' => 'text',
                ],
                'field_4' => [
                    'label' => 'Destino del enlace',
                    'type' => 'link',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 2,
                'field_1' => 'Una propuesta creada alrededor de su historia',
                'field_2' => 'Compartan la fecha, locación y visión de su celebración para preparar una cobertura a medida.',
                'field_3' => 'Solicitar Propuesta',
                'field_4' => '/contacto',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'contact_page_header' => [
            'label' => 'Encabezado',
            'group' => 'Contacto',
            'page' => 'contact',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Disponibilidad & Cotizaciones',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Título principal',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Texto · Cada boda es un universo único. Tómense un mo…',
                    'type' => 'textarea',
                ],
                'page_title' => [
                    'label' => 'Título de la pestaña y buscadores',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 0,
                'field_1' => 'Disponibilidad & Cotizaciones',
                'field_2' => 'Inmortalicemos su Historia',
                'field_3' => 'Cada boda es un universo único. Tómense un momento para compartir sus planes y me pondré en contacto con ustedes en menos de 24 horas.',
                'page_title' => 'Contacto',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'contact_contact_section' => [
            'label' => 'Formulario, contacto y preguntas',
            'group' => 'Contacto',
            'page' => 'contact',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar esta sección',
                    'type' => 'boolean',
                ],
                'field_1' => [
                    'label' => 'Texto de ejemplo',
                    'type' => 'text',
                ],
                'field_2' => [
                    'label' => 'Texto de ejemplo',
                    'type' => 'text',
                ],
                'field_3' => [
                    'label' => 'Texto de ejemplo',
                    'type' => 'text',
                ],
                'field_4' => [
                    'label' => 'Texto de ejemplo',
                    'type' => 'text',
                ],
                'field_6' => [
                    'label' => 'Texto · Revisa los campos marcados antes de enviar la…',
                    'type' => 'text',
                ],
                'field_7' => [
                    'label' => 'Nombres de la pareja *',
                    'type' => 'text',
                ],
                'field_8' => [
                    'label' => 'Correo electrónico *',
                    'type' => 'text',
                ],
                'field_9' => [
                    'label' => 'Teléfono / WhatsApp *',
                    'type' => 'text',
                ],
                'field_10' => [
                    'label' => 'Fecha de la boda / evento *',
                    'type' => 'text',
                ],
                'field_11' => [
                    'label' => 'Ciudad / Lugar de la celebración *',
                    'type' => 'text',
                ],
                'field_12' => [
                    'label' => 'Tipo de experiencia *',
                    'type' => 'text',
                ],
                'field_13' => [
                    'label' => 'Selecciona una opción',
                    'type' => 'text',
                ],
                'field_14' => [
                    'label' => 'Cobertura Completa de Boda',
                    'type' => 'text',
                ],
                'field_15' => [
                    'label' => 'Sesión Preboda / Postboda',
                    'type' => 'text',
                ],
                'field_16' => [
                    'label' => 'Elopement / Boda Íntima',
                    'type' => 'text',
                ],
                'field_17' => [
                    'label' => 'Boda de Destino',
                    'type' => 'text',
                ],
                'field_18' => [
                    'label' => 'Cuéntame sobre ustedes y su visión de ese día *',
                    'type' => 'text',
                ],
                'field_19' => [
                    'label' => 'Enviar Consulta de Reserva',
                    'type' => 'text',
                ],
                'field_20' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_21' => [
                    'label' => 'Texto · Si prefieren una atención inmediata o tienen …',
                    'type' => 'text',
                ],
                'field_22' => [
                    'label' => 'Email',
                    'type' => 'text',
                ],
                'field_23' => [
                    'label' => 'WhatsApp',
                    'type' => 'text',
                ],
                'field_24' => [
                    'label' => 'Ubicación Base',
                    'type' => 'text',
                ],
                'field_25' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_26' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_27' => [
                    'label' => 'Texto · Se recomienda reservar entre 6 y 12 meses ant…',
                    'type' => 'text',
                ],
                'field_28' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_29' => [
                    'label' => 'Texto · Sí. Estoy disponible para viajar por todo el …',
                    'type' => 'text',
                ],
                'field_30' => [
                    'label' => 'Título de la sección',
                    'type' => 'text',
                ],
                'field_31' => [
                    'label' => 'Texto · Entrego un adelanto de 20 a 30 fotografías en…',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'position' => 1,
                'field_1' => 'Ej. Lucía & Mateo',
                'field_2' => 'hola@ejemplo.com',
                'field_3' => 'Ej. Valle Sagrado, Cusco',
                'field_4' => 'Estilo de la boda, qué valoran de la fotografía o cualquier detalle especial...',
                'field_6' => 'Revisa los campos marcados antes de enviar la consulta.',
                'field_7' => 'Nombres de la pareja *',
                'field_8' => 'Correo electrónico *',
                'field_9' => 'Teléfono / WhatsApp *',
                'field_10' => 'Fecha de la boda / evento *',
                'field_11' => 'Ciudad / Lugar de la celebración *',
                'field_12' => 'Tipo de experiencia *',
                'field_13' => 'Selecciona una opción',
                'field_14' => 'Cobertura Completa de Boda',
                'field_15' => 'Sesión Preboda / Postboda',
                'field_16' => 'Elopement / Boda Íntima',
                'field_17' => 'Boda de Destino',
                'field_18' => 'Cuéntame sobre ustedes y su visión de ese día *',
                'field_19' => 'Enviar Consulta de Reserva',
                'field_20' => 'Contacto Directo',
                'field_21' => 'Si prefieren una atención inmediata o tienen dudas generales, pueden comunicarse directamente:',
                'field_22' => 'Email',
                'field_23' => 'WhatsApp',
                'field_24' => 'Ubicación Base',
                'field_25' => 'Preguntas Frecuentes',
                'field_26' => '¿Con cuánta anticipación debemos reservar?',
                'field_27' => 'Se recomienda reservar entre 6 y 12 meses antes, especialmente para fechas de temporada alta.',
                'field_28' => '¿Realizas coberturas fuera de Arequipa?',
                'field_29' => 'Sí. Estoy disponible para viajar por todo el Perú y documentar bodas de destino en el extranjero.',
                'field_30' => '¿Cuándo entregas las fotografías?',
                'field_31' => 'Entrego un adelanto de 20 a 30 fotografías en 48 horas y la galería completa en 4 a 6 semanas.',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
        'branding' => [
            'label' => 'Identidad y datos de contacto',
            'group' => 'Configuración',
            'page' => null,
            'fields' => [
                'brand' => [
                    'label' => 'Nombre del estudio',
                    'type' => 'text',
                ],
                'studio' => [
                    'label' => 'Texto junto al nombre',
                    'type' => 'text',
                ],
                'logo' => [
                    'label' => 'Logotipo · opcional',
                    'type' => 'image',
                ],
                'favicon' => [
                    'label' => 'Icono del navegador · opcional',
                    'type' => 'image',
                ],
                'email' => [
                    'label' => 'Correo de contacto',
                    'type' => 'email',
                ],
                'phone_display' => [
                    'label' => 'Teléfono visible',
                    'type' => 'text',
                ],
                'phone_link' => [
                    'label' => 'WhatsApp · solo números',
                    'type' => 'text',
                ],
                'location' => [
                    'label' => 'Ciudad y país',
                    'type' => 'text',
                ],
                'instagram' => [
                    'label' => 'Enlace de Instagram',
                    'type' => 'url',
                ],
                'description' => [
                    'label' => 'Descripción para buscadores',
                    'type' => 'textarea',
                ],
                'title' => [
                    'label' => 'Título de la página de inicio',
                    'type' => 'text',
                ],
                'gold' => [
                    'label' => 'Color de acento',
                    'type' => 'color',
                ],
                'background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'text' => [
                    'label' => 'Color del texto',
                    'type' => 'color',
                ],
            ],
            'defaults' => [
                'brand' => 'Renzo Nuñes Melgar',
                'email' => 'info@renzonunezmelgar.com',
                'phone_display' => '+51 900 000 000',
                'phone_link' => '51900000000',
                'location' => 'Arequipa, Perú',
                'studio' => 'Studio',
                'logo' => '',
                'favicon' => '',
                'instagram' => 'https://instagram.com',
                'description' => 'Fotografía editorial y documental de bodas en Arequipa, Perú y destinos internacionales.',
                'title' => 'Fotografía de Bodas',
                'gold' => '#b89756',
                'background' => '#fcfbf9',
                'text' => '#555555',
            ],
        ],
        'typography' => [
            'label' => 'Tipografías y estilo',
            'group' => 'Configuración',
            'page' => null,
            'fields' => [
                'heading_font' => [
                    'label' => 'Tipografía de títulos',
                    'type' => 'font',
                ],
                'body_font' => [
                    'label' => 'Tipografía de textos',
                    'type' => 'font',
                ],
                'base_size' => [
                    'label' => 'Tamaño base del texto (px)',
                    'type' => 'number',
                    'min' => 14,
                    'max' => 22,
                ],
                'title_scale' => [
                    'label' => 'Escala de títulos (%)',
                    'type' => 'number',
                    'min' => 80,
                    'max' => 140,
                ],
                'hero_size' => [
                    'label' => 'Tamaño máximo de las frases (px)',
                    'type' => 'number',
                    'min' => 20,
                    'max' => 52,
                ],
                'hero_color' => [
                    'label' => 'Dorado de las frases',
                    'type' => 'color',
                ],
            ],
            'defaults' => [
                'heading_font' => 'Playfair Display',
                'body_font' => 'Cormorant Garamond',
                'base_size' => 16,
                'title_scale' => 100,
                'hero_size' => 32,
                'hero_color' => '#e6c47e',
            ],
        ],
        'navigation' => [
            'label' => 'Menú principal',
            'group' => 'Configuración',
            'page' => null,
            'fields' => [
                'home' => [
                    'label' => 'Inicio',
                    'type' => 'text',
                ],
                'portfolio' => [
                    'label' => 'Portafolio',
                    'type' => 'text',
                ],
                'services' => [
                    'label' => 'Servicios',
                    'type' => 'text',
                ],
                'about' => [
                    'label' => 'Sobre mí',
                    'type' => 'text',
                ],
                'contact' => [
                    'label' => 'Contacto',
                    'type' => 'text',
                ],
                'booking' => [
                    'label' => 'Citas',
                    'type' => 'text',
                ],
            ],
            'defaults' => [
                'home' => 'Inicio',
                'portfolio' => 'Portafolio',
                'services' => 'Servicios',
                'about' => 'Sobre mí',
                'contact' => 'Contacto',
                'booking' => 'Agendar cita',
            ],
        ],
        'footer' => [
            'label' => 'Pie de página',
            'group' => 'Configuración',
            'page' => null,
            'fields' => [
                'tagline' => [
                    'label' => 'Frase del estudio',
                    'type' => 'text',
                ],
                'bio' => [
                    'label' => 'Descripción',
                    'type' => 'textarea',
                ],
                'availability' => [
                    'label' => 'Disponibilidad para viajar',
                    'type' => 'text',
                ],
                'copyright' => [
                    'label' => 'Texto de derechos',
                    'type' => 'text',
                ],
                'credits' => [
                    'label' => 'Créditos',
                    'type' => 'text',
                ],
                'navigation_heading' => [
                    'label' => 'Título de navegación',
                    'type' => 'text',
                ],
                'contact_heading' => [
                    'label' => 'Título de contacto',
                    'type' => 'text',
                ],
            ],
            'defaults' => [
                'tagline' => 'Fotografía Editorial de Bodas',
                'bio' => 'Capturando la esencia espontánea, atemporal y emotiva de tu historia de amor en Perú y destinos internacionales.',
                'availability' => 'Disponible Worldwide',
                'copyright' => 'Photography. Todos los derechos reservados.',
                'credits' => 'Grupo JHALE',
                'navigation_heading' => 'Navegación',
                'contact_heading' => 'Contacto',
            ],
        ],
        'portfolio_items' => [
            'label' => 'Historias y fotografías',
            'group' => 'Colecciones',
            'page' => null,
            'fields' => [
                'items' => [
                    'label' => 'Historias del portafolio',
                    'type' => 'repeater',
                    'fields' => [
                        'names' => [
                            'label' => 'Nombres de la pareja',
                            'type' => 'text',
                        ],
                        'category' => [
                            'label' => 'Categoría',
                            'type' => 'select',
                            'options' => [
                                'wedding' => 'Bodas',
                                'prewedding' => 'Preboda y postboda',
                                'elopement' => 'Elopements y destino',
                            ],
                        ],
                        'tag' => [
                            'label' => 'Etiqueta',
                            'type' => 'text',
                        ],
                        'location' => [
                            'label' => 'Lugar',
                            'type' => 'text',
                        ],
                        'image' => [
                            'label' => 'Fotografía',
                            'type' => 'image',
                        ],
                    ],
                    'min' => 1,
                    'max' => 60,
                ],
            ],
            'defaults' => [
                'items' => [
                    0 => [
                        'category' => 'wedding',
                        'tag' => 'Boda Editorial',
                        'names' => 'Camila & Sebastián',
                        'location' => 'Valle Sagrado, Cusco',
                        'image' => 'assets/images/portfolio/camila-sebastian.webp',
                    ],
                    1 => [
                        'category' => 'prewedding',
                        'tag' => 'Sesión Postboda',
                        'names' => 'Mariana & Diego',
                        'location' => 'Arequipa, Perú',
                        'image' => 'assets/images/portfolio/mariana-diego.webp',
                    ],
                    2 => [
                        'category' => 'elopement',
                        'tag' => 'Elopement',
                        'names' => 'Lucía & Mateo',
                        'location' => 'Máncora, Piura',
                        'image' => 'assets/images/portfolio/lucia-mateo.webp',
                    ],
                    3 => [
                        'category' => 'wedding',
                        'tag' => 'Boda Documental',
                        'names' => 'Valentina & Alejandro',
                        'location' => 'Lima, Perú',
                        'image' => 'assets/images/portfolio/valentina-alejandro.webp',
                    ],
                    4 => [
                        'category' => 'prewedding',
                        'tag' => 'Preboda al Atardecer',
                        'names' => 'Sofía & Gabriel',
                        'location' => 'Colca, Arequipa',
                        'image' => 'assets/images/portfolio/sofia-gabriel.webp',
                    ],
                    5 => [
                        'category' => 'elopement',
                        'tag' => 'Boda Destino',
                        'names' => 'Isabella & Thomas',
                        'location' => 'Paracas, Ica',
                        'image' => 'assets/images/portfolio/isabella-thomas.webp',
                    ],
                ],
            ],
        ],
        'testimonials' => [
            'label' => 'Dedicatorias y comentarios',
            'group' => 'Colecciones',
            'page' => null,
            'fields' => [
                'items' => [
                    'label' => 'Testimonios',
                    'type' => 'repeater',
                    'fields' => [
                        'names' => [
                            'label' => 'Nombres de la pareja',
                            'type' => 'text',
                        ],
                        'quote' => [
                            'label' => 'Comentario',
                            'type' => 'textarea',
                        ],
                        'detail' => [
                            'label' => 'Boda o sesión',
                            'type' => 'text',
                        ],
                    ],
                    'min' => 1,
                    'max' => 30,
                ],
            ],
            'defaults' => [
                'items' => [
                    0 => [
                        'quote' => 'Capturó cada lágrima, cada risa y la magia genuina de nuestro día. Ver las fotos por primera vez fue revivir exactamente cómo nos sentíamos. Sin poses forzadas, simplemente puro sentimiento.',
                        'names' => 'Camila & Sebastián',
                        'detail' => 'Boda en Valle Sagrado, Cusco',
                    ],
                    1 => [
                        'quote' => 'Su calma y elegancia durante toda la boda nos hicieron sentir cómodos desde el primer instante. Las fotografías parecen sacadas de una revista de arte. Superó todas nuestras expectativas.',
                        'names' => 'Mariana & Diego',
                        'detail' => 'Sesión Preboda & Boda, Arequipa',
                    ],
                    2 => [
                        'quote' => 'Logró retratar la esencia salvaje e íntima de nuestro elopement a la perfección. Cada imagen nos devuelve a ese paisaje y a todo lo que sentimos.',
                        'names' => 'Lucía & Mateo',
                        'detail' => 'Elopement en Máncora',
                    ],
                ],
            ],
        ],
        'service_wedding' => [
            'label' => 'Documental de Boda',
            'group' => 'Experiencias',
            'page' => null,
            'fields' => [
                'title' => [
                    'label' => 'Nombre de la experiencia',
                    'type' => 'text',
                ],
                'tag' => [
                    'label' => 'Etiqueta',
                    'type' => 'text',
                ],
                'number' => [
                    'label' => 'Número visible',
                    'type' => 'text',
                ],
                'summary' => [
                    'label' => 'Resumen de la tarjeta',
                    'type' => 'textarea',
                ],
                'hero' => [
                    'label' => 'Presentación',
                    'type' => 'textarea',
                ],
                'intro_title' => [
                    'label' => 'Título de la historia',
                    'type' => 'text',
                ],
                'image' => [
                    'label' => 'Fotografía',
                    'type' => 'image',
                ],
                'image_alt' => [
                    'label' => 'Descripción de la fotografía',
                    'type' => 'text',
                ],
                'includes_title' => [
                    'label' => 'Título de lo que incluye',
                    'type' => 'text',
                ],
                'cta_title' => [
                    'label' => 'Título de invitación',
                    'type' => 'text',
                ],
                'cta_text' => [
                    'label' => 'Texto de invitación',
                    'type' => 'textarea',
                ],
                'cta_label' => [
                    'label' => 'Texto del botón',
                    'type' => 'text',
                ],
                'paragraphs' => [
                    'label' => 'Historia · un párrafo por línea',
                    'type' => 'lines',
                ],
                'includes' => [
                    'label' => 'Lo que incluye',
                    'type' => 'repeater',
                    'fields' => [
                        'label' => [
                            'label' => 'Beneficio',
                            'type' => 'text',
                        ],
                        'text' => [
                            'label' => 'Descripción',
                            'type' => 'textarea',
                        ],
                    ],
                    'min' => 1,
                    'max' => 20,
                ],
            ],
            'defaults' => [
                'number' => '01',
                'tag' => 'Experiencia Principal',
                'title' => 'Documental de Boda',
                'summary' => 'Cobertura completa e íntima del gran día. Capturamos la narrativa real desde los preparativos hasta el último baile.',
                'hero' => 'Una narrativa completa, honesta y emotiva del día más importante. Desde los silencios de la mañana hasta la euforia de la fiesta.',
                'intro_title' => 'Tu historia contada sin poses forzadas',
                'paragraphs' => [
                    0 => 'Mi objetivo el día de la boda es registrar la atmósfera real. Me enfoco en la luz natural, la espontaneidad y los detalles que hacen único su compromiso: las miradas de nerviosismo previo, los abrazos sinceros y la complicidad de la pareja.',
                    1 => 'Combino una visión editorial para los retratos con la sutileza del fotoperiodismo para capturar instantes imprevistos con absoluta elegancia.',
                ],
                'image' => 'assets/images/services/wedding-documentary.webp',
                'image_alt' => 'Fotografía documental de boda',
                'includes_title' => '¿Qué incluye la experiencia de boda?',
                'includes' => [
                    0 => [
                        'label' => 'Cobertura continua',
                        'text' => 'Preparativos, ceremonia, cóctel, retratos de pareja y recepción.',
                    ],
                    1 => [
                        'label' => 'Adelanto exclusivo',
                        'text' => 'Selección de 25 a 30 fotografías editadas en alta calidad, entregadas en 48 horas.',
                    ],
                    2 => [
                        'label' => 'Galería privada online',
                        'text' => 'Espacio protegido para ver, compartir y descargar las fotografías en máxima resolución.',
                    ],
                    3 => [
                        'label' => 'Edición editorial',
                        'text' => 'Revelado profesional y corrección de color individual para cada imagen.',
                    ],
                    4 => [
                        'label' => 'Planificación',
                        'text' => 'Reuniones previas para revisar la cronología y afinar cada detalle.',
                    ],
                ],
                'cta_title' => '¿Tienen definida la fecha de su boda?',
                'cta_text' => 'Las fechas suelen reservarse con meses de anticipación. Verifiquemos si su día está libre en mi agenda.',
                'cta_label' => 'Consultar disponibilidad',
            ],
        ],
        'service_prewedding' => [
            'label' => 'Sesiones Preboda',
            'group' => 'Experiencias',
            'page' => null,
            'fields' => [
                'title' => [
                    'label' => 'Nombre de la experiencia',
                    'type' => 'text',
                ],
                'tag' => [
                    'label' => 'Etiqueta',
                    'type' => 'text',
                ],
                'number' => [
                    'label' => 'Número visible',
                    'type' => 'text',
                ],
                'summary' => [
                    'label' => 'Resumen de la tarjeta',
                    'type' => 'textarea',
                ],
                'hero' => [
                    'label' => 'Presentación',
                    'type' => 'textarea',
                ],
                'intro_title' => [
                    'label' => 'Título de la historia',
                    'type' => 'text',
                ],
                'image' => [
                    'label' => 'Fotografía',
                    'type' => 'image',
                ],
                'image_alt' => [
                    'label' => 'Descripción de la fotografía',
                    'type' => 'text',
                ],
                'includes_title' => [
                    'label' => 'Título de lo que incluye',
                    'type' => 'text',
                ],
                'cta_title' => [
                    'label' => 'Título de invitación',
                    'type' => 'text',
                ],
                'cta_text' => [
                    'label' => 'Texto de invitación',
                    'type' => 'textarea',
                ],
                'cta_label' => [
                    'label' => 'Texto del botón',
                    'type' => 'text',
                ],
                'paragraphs' => [
                    'label' => 'Historia · un párrafo por línea',
                    'type' => 'lines',
                ],
                'includes' => [
                    'label' => 'Lo que incluye',
                    'type' => 'repeater',
                    'fields' => [
                        'label' => [
                            'label' => 'Beneficio',
                            'type' => 'text',
                        ],
                        'text' => [
                            'label' => 'Descripción',
                            'type' => 'textarea',
                        ],
                    ],
                    'min' => 1,
                    'max' => 20,
                ],
            ],
            'defaults' => [
                'number' => '02',
                'tag' => 'Conexión & Autenticidad',
                'title' => 'Sesiones Preboda',
                'summary' => 'Una experiencia relajada y auténtica antes del gran día, perfecta para conectar con la cámara en un entorno especial.',
                'hero' => 'Una experiencia relajada e íntima previa al gran día. Perfecta para romper el hielo con la cámara y crear recuerdos naturales en su lugar favorito.',
                'intro_title' => 'Conectando antes de decir “Acepto”',
                'paragraphs' => [
                    0 => 'La sesión preboda es el pretexto perfecto para pasar un momento a solas, caminar tranquilos y disfrutar sin el itinerario estricto del día de la boda.',
                    1 => 'Esta experiencia nos ayuda a generar la confianza ideal para que, en la boda, se sientan completamente cómodos frente al lente.',
                ],
                'image' => 'assets/images/services/prewedding.webp',
                'image_alt' => 'Sesión preboda editorial',
                'includes_title' => '¿Qué incluye la sesión preboda?',
                'includes' => [
                    0 => [
                        'label' => 'Duración',
                        'text' => 'Entre 2 y 3 horas de sesión al atardecer o al amanecer.',
                    ],
                    1 => [
                        'label' => 'Locación a elección',
                        'text' => 'Paisajes naturales, entorno urbano o lugares con valor sentimental.',
                    ],
                    2 => [
                        'label' => 'Cambios de vestuario',
                        'text' => 'Asesoría de estilo y paleta de color para lograr armonía visual.',
                    ],
                    3 => [
                        'label' => 'Entrega digital',
                        'text' => 'Galería online con más de 60 fotografías optimizadas en alta calidad.',
                    ],
                ],
                'cta_title' => '¿Quieren planear su sesión preboda?',
                'cta_text' => 'Escojamos la locación perfecta y capturemos su esencia juntos.',
                'cta_label' => 'Agendar sesión',
            ],
        ],
        'service_postwedding' => [
            'label' => 'Elopements & Destino',
            'group' => 'Experiencias',
            'page' => null,
            'fields' => [
                'title' => [
                    'label' => 'Nombre de la experiencia',
                    'type' => 'text',
                ],
                'tag' => [
                    'label' => 'Etiqueta',
                    'type' => 'text',
                ],
                'number' => [
                    'label' => 'Número visible',
                    'type' => 'text',
                ],
                'summary' => [
                    'label' => 'Resumen de la tarjeta',
                    'type' => 'textarea',
                ],
                'hero' => [
                    'label' => 'Presentación',
                    'type' => 'textarea',
                ],
                'intro_title' => [
                    'label' => 'Título de la historia',
                    'type' => 'text',
                ],
                'image' => [
                    'label' => 'Fotografía',
                    'type' => 'image',
                ],
                'image_alt' => [
                    'label' => 'Descripción de la fotografía',
                    'type' => 'text',
                ],
                'includes_title' => [
                    'label' => 'Título de lo que incluye',
                    'type' => 'text',
                ],
                'cta_title' => [
                    'label' => 'Título de invitación',
                    'type' => 'text',
                ],
                'cta_text' => [
                    'label' => 'Texto de invitación',
                    'type' => 'textarea',
                ],
                'cta_label' => [
                    'label' => 'Texto del botón',
                    'type' => 'text',
                ],
                'paragraphs' => [
                    'label' => 'Historia · un párrafo por línea',
                    'type' => 'lines',
                ],
                'includes' => [
                    'label' => 'Lo que incluye',
                    'type' => 'repeater',
                    'fields' => [
                        'label' => [
                            'label' => 'Beneficio',
                            'type' => 'text',
                        ],
                        'text' => [
                            'label' => 'Descripción',
                            'type' => 'textarea',
                        ],
                    ],
                    'min' => 1,
                    'max' => 20,
                ],
            ],
            'defaults' => [
                'number' => '03',
                'tag' => 'Arte sin Prisas',
                'title' => 'Elopements & Destino',
                'summary' => 'Fotografía para bodas íntimas o celebraciones en destinos únicos, enfocada en la esencia salvaje y personal de la pareja.',
                'hero' => 'Vuelvan a vestirse de novios sin la presión del tiempo ni el protocolo. Una oportunidad para crear retratos artísticos en escenarios espectaculares.',
                'intro_title' => 'Magia pura en destinos épicos',
                'paragraphs' => [
                    0 => 'Una sesión postboda nos da libertad para viajar a montañas, playas salvajes o lugares históricos con la mejor luz del atardecer.',
                    1 => 'Sin miedo a ensuciar el vestido o despeinarse, nos enfocamos por completo en la parte más visual y artística de su unión.',
                ],
                'image' => 'assets/images/services/elopement.webp',
                'image_alt' => 'Boda íntima y sesión de destino',
                'includes_title' => '¿Qué incluye la experiencia de destino?',
                'includes' => [
                    0 => [
                        'label' => 'Itinerario flexible',
                        'text' => 'Búsqueda de las mejores horas de luz, desde la hora dorada hasta la hora azul.',
                    ],
                    1 => [
                        'label' => 'Destino a elección',
                        'text' => 'Disponibilidad para viajar dentro del Perú o al extranjero.',
                    ],
                    2 => [
                        'label' => 'Garantía artística',
                        'text' => 'Fotografías con tratamiento de color de nivel editorial.',
                    ],
                    3 => [
                        'label' => 'Galería privada',
                        'text' => 'Entrega completa en plataforma digital para descargas e impresiones.',
                    ],
                ],
                'cta_title' => '¿Listos para una aventura fotográfica?',
                'cta_text' => 'Creemos retratos inolvidables en un lugar que se sienta completamente suyo.',
                'cta_label' => 'Cotizar experiencia',
            ],
        ],
        'booking' => [
            'label' => 'Citas con Calendly',
            'group' => 'Inicio',
            'page' => 'home',
            'fields' => [
                'enabled' => [
                    'label' => 'Mostrar la sección de citas',
                    'type' => 'boolean',
                ],
                'title' => [
                    'label' => 'Título',
                    'type' => 'text',
                ],
                'description' => [
                    'label' => 'Presentación',
                    'type' => 'textarea',
                ],
                'url' => [
                    'label' => 'Enlace de tu evento de Calendly',
                    'type' => 'calendly',
                ],
                'button' => [
                    'label' => 'Texto del botón',
                    'type' => 'text',
                ],
                'empty_text' => [
                    'label' => 'Mensaje mientras conectas Calendly',
                    'type' => 'textarea',
                ],
                'tag' => [
                    'label' => 'Etiqueta de la sección',
                    'type' => 'text',
                ],
                'custom_background' => [
                    'label' => 'Cambiar el fondo de esta sección',
                    'type' => 'boolean',
                ],
                'section_background' => [
                    'label' => 'Color de fondo',
                    'type' => 'color',
                ],
                'custom_spacing' => [
                    'label' => 'Personalizar el espacio vertical',
                    'type' => 'boolean',
                ],
                'padding_top' => [
                    'label' => 'Espacio superior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
                'padding_bottom' => [
                    'label' => 'Espacio inferior (px)',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'defaults' => [
                'enabled' => true,
                'title' => 'Conversemos sobre su historia',
                'description' => 'Elijan un momento para conocernos, resolver sus dudas y empezar a imaginar las fotografías de su boda.',
                'url' => '',
                'button' => 'Agendar una conversación',
                'empty_text' => 'Escríbanme para coordinar una conversación y revisar juntos los detalles de su celebración.',
                'position' => 5.5,
                'tag' => 'Una conversación, el primer paso',
                'custom_background' => false,
                'section_background' => '#fcfbf9',
                'custom_spacing' => false,
                'padding_top' => 64,
                'padding_bottom' => 64,
            ],
        ],
    ],
    'pages' => [
        'home' => [
            0 => 'home_hero',
            1 => 'home_experience',
            2 => 'home_about_philosophy',
            3 => 'home_services_featured',
            4 => 'home_portfolio_featured',
            5 => 'home_testimonials',
            6 => 'booking',
            7 => 'home_cta_section',
        ],
        'about' => [
            0 => 'about_about_hero',
            1 => 'about_about_story',
            2 => 'about_about_pillars',
            3 => 'about_about_personal',
            4 => 'about_portfolio_cta',
        ],
        'portfolio' => [
            0 => 'portfolio_page_header',
            1 => 'portfolio_portfolio_page_section',
            2 => 'portfolio_portfolio_cta',
        ],
        'services' => [
            0 => 'services_page_header',
            1 => 'services_services_featured',
            2 => 'services_portfolio_cta',
        ],
        'contact' => [
            0 => 'contact_page_header',
            1 => 'contact_contact_section',
        ],
    ],
    'fonts' => [
        'Playfair Display' => 'Playfair+Display:wght@400;500;600;700',
        'Cormorant Garamond' => 'Cormorant+Garamond:wght@300;400;500;600;700',
        'Lora' => 'Lora:wght@400;500;600;700',
        'DM Sans' => 'DM+Sans:wght@400;500;600;700',
        'Montserrat' => 'Montserrat:wght@400;500;600;700',
        'Inter' => 'Inter:wght@400;500;600;700',
        'Libre Baskerville' => 'Libre+Baskerville:wght@400;700',
        'Raleway' => 'Raleway:wght@400;500;600;700',
    ],
];
