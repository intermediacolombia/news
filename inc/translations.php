<?php

function get_translations(string $lang = 'es', bool $reset = false): array {
    static $cache = [];

    if ($reset) {
        $cache = [];
        return [];
    }

    if (isset($cache[$lang])) {
        return $cache[$lang];
    }

    try {
        if (!function_exists('db') || !db()) {
            return [];
        }
        $stmt = db()->prepare("SELECT trans_key, trans_value FROM system_translations WHERE lang_code = ?");
        $stmt->execute([$lang]);
        $translations = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $translations[$row['trans_key']] = $row['trans_value'];
        }
        $cache[$lang] = $translations;
        return $translations;
    } catch (Throwable $e) {
        return [];
    }
}

function clear_translations_cache(): void {
    get_translations('es', true);
}

function t(string $key, string $lang = null): string {
    global $sys;
    
    if ($lang === null) {
        $lang = 'es';
        if (isset($sys['site_language']) && !empty($sys['site_language'])) {
            $lang = $sys['site_language'];
        }
    }
    
    $translations = get_translations($lang);
    
    if (isset($translations[$key]) && !empty($translations[$key])) {
        return $translations[$key];
    }
    
    $spanishDefaults = [
        // Admin menu
        'menu_inicio' => 'Inicio',
        'menu_blog' => 'Blog',
        'menu_entradas' => 'Entradas',
        'menu_categorias' => 'Categorías',
        'menu_multimedia' => 'Multimedia',
        'menu_marca' => 'Marca',
        'menu_publicidad' => 'Publicidad',
        'menu_usuarios' => 'Usuarios',
        'menu_todos' => 'Todos',
        'menu_roles' => 'Roles',
        'menu_configuraciones' => 'Configuraciones',
        'menu_popups' => 'Popups',
        'menu_perfil' => 'Perfil',
        'menu_herramientas' => 'Herramientas',
        'menu_migrar_wp' => 'Migrar desde WordPress',
        'menu_logs' => 'Logs del Sistema',
        'menu_salir' => 'Salir',
        'config_titulo' => 'Configuraciones del Sistema',
        'config_generales' => 'Generales',
        'config_identidad' => 'Identidad',
        'config_codigo' => 'Codigo HTML',
        'config_seo' => 'SEO',
        'config_email' => 'Email',
        'config_apariencia' => 'Apariencia',
        'config_idioma' => 'Editor de Idioma',
        'config_nombre_tienda' => 'Nombre de la tienda / sitio',
        'config_logo' => 'Logo',
        'config_favicon' => 'Favicon',
        'config_banner_inferior' => 'Banner Inferior',
        'config_correo' => 'Correo de contacto',
        'config_direccion' => 'Direccion de la Tienda',
        'config_telefono' => 'Telefono de la Tienda',
        'config_info_footer' => 'Info Footer',
        'config_habilitar_tts' => 'Habilitar Text-to-Speech',
        'config_redes_sociales' => 'Redes Sociales',
        'btn_guardar' => 'Guardar cambios',
        'btn_cancelar' => 'Cancelar',
        'btn_eliminar' => 'Eliminar',
        'btn_editar' => 'Editar',
        'btn_crear' => 'Crear',
        'btn_buscar' => 'Buscar',

        // Theme translations
        'theme_tendencias' => 'Tendencias',
        'theme_seguinos' => 'Síguenos:',
        'theme_ultimas_noticias' => 'Últimas Noticias',
        'theme_por' => 'por',
        'theme_categorias' => 'Categorías',
        'theme_las_mas_leidas' => 'Las Más Leídas',
        'theme_ver_mas' => 'Ver Más',
        'theme_tags_tendencias' => 'Tags Tendencias',
        'theme_resultados_para' => 'Resultados para:',
        'theme_ver_todas_las_noticias' => 'Ver todas las noticias',
        'theme_no_se_encontraron' => 'No se encontraron resultados',
        'theme_no_hay_publicaciones' => 'No hay publicaciones que coincidan con',
        'theme_volver_noticias' => 'Volver a Noticias',
        'theme_no_hay_noticias' => 'No hay noticias en esta categoría.',
        'theme_vistas' => 'vistas',
        'theme_compartir' => 'Compartir:',
        'theme_tags' => 'Tags:',
        'theme_comentarios' => 'Comentarios',
        'theme_tambien_interesar' => 'También te puede interesar',
        'theme_facebook' => 'Facebook',
        'theme_twitter' => 'X (Twitter)',
        'theme_youtube' => 'YouTube',
        'theme_instagram' => 'Instagram',
        'theme_tiktok' => 'TikTok',
        'theme_buscar' => 'Buscar',
        'theme_leer_mas' => 'Leer más',
        'theme_escuchar_articulo' => 'Escuchar artículo',
        'theme_que_hay_de_nuevo' => '¿Qué hay de nuevo?',
        'theme_minutos' => 'Minutos',
        'theme_las_mas_leidas_2' => 'Las Más Leídas',
        'theme_no_hay_noticias_recientes' => 'No hay noticias recientes.',
        'theme_informacion_institucional' => 'Información Institucional',
        'theme_conoce_mas_nuestra_org' => 'Conoce más sobre nuestra organización, nuestra historia y nuestros valores.',
        'theme_no_hay_info_institucional' => 'No hay información institucional disponible en este momento.',
        'theme_general' => 'General',
        'theme_quienes_somos' => 'Quiénes Somos',
        'theme_mision_vision' => 'Misión y Visión',
        'theme_historia' => 'Historia',
        'theme_organigrama' => 'Organigrama',
        'theme_junta_directiva' => 'Junta Directiva',
        'theme_equipo' => 'Equipo',
        'theme_valores' => 'Valores',
        'theme_politicas' => 'Políticas',
        'theme_inicio' => 'Inicio',
        'theme_paginas' => 'Páginas',
        'theme_pagina_no_encontrada' => 'Página No Encontrada',
        'theme_pagina_no_existe' => 'Lo sentimos, la página que estás buscando no existe en nuestro sitio web. Puedes regresar a la página de inicio o intentar usar el buscador.',
        'theme_volver_inicio' => 'Volver al Inicio',
        'theme_contactanos' => 'Contáctanos',
        'theme_tu_nombre' => 'Tu nombre',
        'theme_tu_correo' => 'Tu correo electrónico',
        'theme_tu_telefono' => 'Tu teléfono',
        'theme_asunto' => 'Asunto',
        'theme_tu_mensaje' => 'Tu mensaje',
        'theme_enviar_mensaje' => 'Enviar mensaje',
        'theme_direccion' => 'Dirección',
        'theme_correo' => 'Correo',
        'theme_telefono' => 'Teléfono',
        'theme_siguenos' => 'Síguenos',
        'theme_destacados' => 'Destacados',
        'theme_minutos_de_lectura' => 'Minutos de Lectura',
        'theme_minute_read' => 'minute read',
        'theme_views' => 'Views',
        'theme_volver_listado' => 'Volver al listado',
        'theme_ultima_actualizacion' => 'Última actualización:',
        'theme_compartir_pagina' => 'Compartir esta página',
        'theme_ver_perfil' => 'Ver Perfil',
        'theme_nuestro_equipo' => 'Nuestro Equipo',
        'theme_conoce_nuestros_columnistas' => 'Conoce a Nuestros Columnistas',
        'theme_columnistas_descripcion' => 'Voces expertas con perspectivas únicas sobre los temas que más importan',
        'theme_no_hay_columnistas' => 'No hay columnistas disponibles',
        'theme_prox_columnistas' => 'Próximamente agregaremos nuevos columnistas a nuestro equipo.',
        'theme_columnista_role' => 'Columnista',
        'theme_quieres_unirte' => '¿Quieres ser parte de nuestro equipo?',
        'theme_quieres_unirte_desc' => 'Si tienes experiencia y pasión por escribir, únete a nuestro equipo de columnistas.',
        'theme_columna' => 'columna',
        'theme_privacidad' => 'Política de Privacidad',
        'theme_terminos' => 'Términos y Condiciones',
        'theme_todos_derechos' => 'Todos los derechos reservados',
        'theme_newsletter' => 'Newsletter',
        'theme_suscribete' => 'Suscríbete',
        'theme_tu_email' => 'Tu email',
        'theme_autor' => 'Autor',
        'theme_diseno_por' => 'Diseño por',
        'theme_hosting_disenno' => 'Hosting & Diseño por',
        'theme_youtube' => 'YouTube',
        'theme_view_all' => 'Ver Todo',
        'theme_sign_up' => 'Registrarse',
        'theme_home' => 'Inicio',
        'theme_nuestros_columnistas' => 'Nuestros Columnistas',
        'theme_ver_todas' => 'Ver todas',
        'theme_ver_todas_noticias' => 'Ver todas las noticias',
        'theme_intenta_diferente' => 'Intenta con palabras clave diferentes',
        'theme_intenta_general' => 'Intenta con palabras clave más generales',
        'theme_intenta_menos' => 'Intenta con menos palabras clave',
        'theme_conoce_mas_organizacion' => 'Conoce más sobre nuestra organización',
        'theme_proximamente_info' => 'Próximamente tendremos más información institucional.',
        'theme_bienvenido_espacio' => 'Bienvenido al espacio de opinión de',
        'theme_bienvenido' => 'Bienvenido a',
        'theme_aqui_encontraras' => 'Aquí encontrarás sus análisis y perspectivas más recientes.',
        'theme_ver_todos_columnistas' => 'Ver Todos los Columnistas',
        'theme_siguiente' => 'Siguiente',
        'theme_anterior' => 'Anterior',
        'theme_siguiente_articulo' => 'Siguiente artículo',
        'theme_articulo' => 'artículo',
        'theme_articulos' => 'artículos',
        'theme_whatsapp' => 'WhatsApp',
        'theme_sugerencias' => 'Sugerencias:',
        'theme_verifica_palabras' => 'Verifica que todas las palabras estén escritas correctamente',
        'theme_cerrar' => 'Cerrar',
        'theme_contactanos_descripcion' => 'Si necesitas publicidad, saber más sobre nosotros o simplemente quieres contactarnos, déjanos un mensaje y nos pondremos en contacto contigo lo antes posible.',
        'theme_columnas_publicadas' => 'Columnas publicadas',
        'theme_columnas_opinion' => 'Columnas de Opinión',
        'theme_no_ha_publicado' => 'Este columnista aún no ha publicado artículos',
        'theme_busca_noticias' => 'Busca noticias, artículos y contenido en',
        'theme_utiliza_buscador' => 'Utiliza el formulario de búsqueda para encontrar noticias, artículos y contenido.',
        'theme_pagina_busqueda' => 'pagina de busqueda de',
        'theme_error_pagina' => 'Error en encontrar la pagina',
        'theme_leer_columna' => 'LEER COLUMNA',
        'theme_no_hay_disponibles' => 'No hay columnistas disponibles',
        'theme_volver_listado' => 'Volver al listado',
        'theme_volver_inicio' => 'Volver al inicio',
        'theme_no_hay_noticias_disponibles' => 'No hay noticias disponibles',
        'theme_pronto_tendremos' => 'Pronto tendremos nuevo contenido para ti.',
        'theme_tienes_pregunta' => '¿Tienes alguna pregunta? Escríbenos',
        'theme_siguenos' => 'Síguenos',
        'theme_populares' => 'Populares',
        'theme_tags' => 'Tags',
        'theme_resultados_busqueda' => 'Resultados de Búsqueda',
        'theme_resultado' => 'resultado',
        'theme_resultados' => 'resultados',
        'theme_no_encontramos' => 'No pudimos encontrar ningún resultado para',
        'theme_que_buscas' => '¿Qué estás buscando?',
        'theme_admin' => 'Admin',
        'theme_noticia' => 'Noticia',
        'theme_comentarios' => 'Comentarios',
        'theme_perfil_columnista' => 'Perfil del Columnista',
        'theme_categorias' => 'Categorías',
        'theme_quick_links' => 'Quick Links',
        'theme_iniciar_sesion' => 'Iniciar Sesión',
        'theme_usuario_correo' => 'Usuario o correo electrónico',
        'theme_contrasena' => 'Contraseña',
        'theme_recordarme' => 'Recordarme',
        'theme_cancelar' => 'Cancelar',
        'theme_escuchar_articulo' => 'Escuchar artículo',
        'theme_sugerencias' => 'Sugerencias:',
        'theme_verifica_palabras' => 'Verifica que todas las palabras estén escritas correctamente',
        'theme_olvidaste_contrasena' => '¿Olvidaste tu contraseña?',
        'theme_todos' => 'Todos',
        'theme_logo' => 'Logo',
        'theme_publicidad' => 'Publicidad',
        'theme_pagina_no_encontrada' => 'Página no encontrada',
    ];

    return $spanishDefaults[$key] ?? $key;
}

function get_admin_language(): string {
    global $sys;
    return $sys['admin_language'] ?? 'es';
}

function save_translation(string $lang, string $key, string $value): bool {
    try {
        if (!function_exists('db') || !db()) {
            return false;
        }
        $stmt = db()->prepare("
            INSERT INTO system_translations (lang_code, trans_key, trans_value)
            VALUES (:lang, :key, :value)
            ON DUPLICATE KEY UPDATE trans_value = VALUES(trans_value), updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([
            ':lang' => $lang,
            ':key' => $key,
            ':value' => $value
        ]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function get_all_translation_keys(): array {
    try {
        if (!function_exists('db') || !db()) {
            return [];
        }
        $stmt = db()->query("SELECT DISTINCT trans_key FROM system_translations ORDER BY trans_key");
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'trans_key');
    } catch (Throwable $e) {
        return [];
    }
}

function init_default_theme_translations(): void {
    $defaultKeys = [
        'theme_tendencias' => 'Tendencias',
        'theme_seguinos' => 'Síguenos:',
        'theme_ultimas_noticias' => 'Últimas Noticias',
        'theme_por' => 'por',
        'theme_categorias' => 'Categorías',
        'theme_las_mas_leidas' => 'Las Más Leídas',
        'theme_ver_mas' => 'Ver Más',
        'theme_tags_tendencias' => 'Tags Tendencias',
        'theme_resultados_para' => 'Resultados para:',
        'theme_ver_todas_las_noticias' => 'Ver todas las noticias',
        'theme_no_se_encontraron' => 'No se encontraron resultados',
        'theme_no_hay_publicaciones' => 'No hay publicaciones que coincidan con',
        'theme_volver_noticias' => 'Volver a Noticias',
        'theme_no_hay_noticias' => 'No hay noticias en esta categoría.',
        'theme_vistas' => 'vistas',
        'theme_compartir' => 'Compartir:',
        'theme_tags' => 'Tags:',
        'theme_comentarios' => 'Comentarios',
        'theme_tambien_interesar' => 'También te puede interesar',
        'theme_facebook' => 'Facebook',
        'theme_twitter' => 'X (Twitter)',
        'theme_youtube' => 'YouTube',
        'theme_instagram' => 'Instagram',
        'theme_tiktok' => 'TikTok',
        'theme_buscar' => 'Buscar',
        'theme_leer_mas' => 'Leer más',
        'theme_escuchar_articulo' => 'Escuchar artículo',
        'theme_que_hay_de_nuevo' => '¿Qué hay de nuevo?',
        'theme_minutos' => 'Minutos',
        'theme_las_mas_leidas_2' => 'Las Más Leídas',
        'theme_no_hay_noticias_recientes' => 'No hay noticias recientes.',
        'theme_informacion_institucional' => 'Información Institucional',
        'theme_conoce_mas_nuestra_org' => 'Conoce más sobre nuestra organización, nuestra historia y nuestros valores.',
        'theme_no_hay_info_institucional' => 'No hay información institucional disponible en este momento.',
        'theme_general' => 'General',
        'theme_quienes_somos' => 'Quiénes Somos',
        'theme_mision_vision' => 'Misión y Visión',
        'theme_historia' => 'Historia',
        'theme_organigrama' => 'Organigrama',
        'theme_junta_directiva' => 'Junta Directiva',
        'theme_equipo' => 'Equipo',
        'theme_valores' => 'Valores',
        'theme_politicas' => 'Políticas',
        'theme_inicio' => 'Inicio',
        'theme_paginas' => 'Páginas',
        'theme_pagina_no_encontrada' => 'Página No Encontrada',
        'theme_pagina_no_existe' => 'Lo sentimos, la página que estás buscando no existe en nuestro sitio web. Puedes regresar a la página de inicio o intentar usar el buscador.',
        'theme_volver_inicio' => 'Volver al Inicio',
        'theme_contactanos' => 'Contáctanos',
        'theme_tu_nombre' => 'Tu nombre',
        'theme_tu_correo' => 'Tu correo electrónico',
        'theme_tu_telefono' => 'Tu teléfono',
        'theme_asunto' => 'Asunto',
        'theme_tu_mensaje' => 'Tu mensaje',
        'theme_enviar_mensaje' => 'Enviar mensaje',
        'theme_direccion' => 'Dirección',
        'theme_correo' => 'Correo',
        'theme_telefono' => 'Teléfono',
        'theme_siguenos' => 'Síguenos',
        'theme_destacados' => 'Destacados',
        'theme_minutos_de_lectura' => 'Minutos de Lectura',
        'theme_minute_read' => 'minute read',
        'theme_views' => 'Views',
        'theme_noticias' => 'Noticias',
        'theme_columnistas' => 'Columnistas',
        'theme_nosotros' => 'Nosotros',
        'theme_ver_todas' => 'Ver todas',
        'theme_contacto' => 'Contacto',
        'theme_buscar_noticias' => 'Buscar noticias',
        'theme_buscar_placeholder' => 'Escribe una palabra clave...',
        'theme_ultimas' => 'Últimas',
        'theme_buscar_descripcion' => 'Escribe lo que necesitas y presiona "Buscar".',
        'theme_volver_listado' => 'Volver al listado',
        'theme_ultima_actualizacion' => 'Última actualización:',
        'theme_compartir_pagina' => 'Compartir esta página',
        'theme_ver_perfil' => 'Ver Perfil',
        'theme_nuestro_equipo' => 'Nuestro Equipo',
        'theme_conoce_nuestros_columnistas' => 'Conoce a Nuestros Columnistas',
        'theme_columnistas_descripcion' => 'Voces expertas con perspectivas únicas sobre los temas que más importan',
        'theme_no_hay_columnistas' => 'No hay columnistas disponibles',
        'theme_prox_columnistas' => 'Próximamente agregaremos nuevos columnistas a nuestro equipo.',
        'theme_columnista_role' => 'Columnista',
        'theme_quieres_unirte' => '¿Quieres ser parte de nuestro equipo?',
        'theme_quieres_unirte_desc' => 'Si tienes experiencia y pasión por escribir, únete a nuestro equipo de columnistas.',
        'theme_columna' => 'columna',
        'theme_privacidad' => 'Política de Privacidad',
        'theme_terminos' => 'Términos y Condiciones',
        'theme_todos_derechos' => 'Todos los derechos reservados',
        'theme_newsletter' => 'Newsletter',
        'theme_suscribete' => 'Suscríbete',
        'theme_tu_email' => 'Tu email',
        'theme_autor' => 'Autor',
        'theme_diseno_por' => 'Diseño por',
        'theme_hosting_disenno' => 'Hosting & Diseño por',
        'theme_youtube' => 'YouTube',
        'theme_view_all' => 'Ver Todo',
        'theme_sign_up' => 'Registrarse',
        'theme_home' => 'Inicio',
        'theme_nuestros_columnistas' => 'Nuestros Columnistas',
    ];

    $defaultEnKeys = [
        'theme_tendencias'              => 'Trending',
        'theme_seguinos'                => 'Follow us:',
        'theme_ultimas_noticias'        => 'Latest News',
        'theme_por'                     => 'by',
        'theme_categorias'              => 'Categories',
        'theme_las_mas_leidas'          => 'Most Read',
        'theme_ver_mas'                 => 'View More',
        'theme_tags_tendencias'         => 'Trending Tags',
        'theme_resultados_para'         => 'Results for:',
        'theme_ver_todas_las_noticias'  => 'View all news',
        'theme_no_se_encontraron'       => 'No results found',
        'theme_no_hay_publicaciones'    => 'No posts matching',
        'theme_volver_noticias'         => 'Back to News',
        'theme_no_hay_noticias'         => 'No news in this category.',
        'theme_vistas'                  => 'views',
        'theme_compartir'               => 'Share:',
        'theme_tags'                    => 'Tags:',
        'theme_comentarios'             => 'Comments',
        'theme_tambien_interesar'       => 'You might also like',
        'theme_facebook'                => 'Facebook',
        'theme_twitter'                 => 'X (Twitter)',
        'theme_youtube'                 => 'YouTube',
        'theme_instagram'               => 'Instagram',
        'theme_tiktok'                  => 'TikTok',
        'theme_buscar'                  => 'Search',
        'theme_leer_mas'                => 'Read more',
        'theme_escuchar_articulo'       => 'Listen to article',
        'theme_que_hay_de_nuevo'        => "What's new?",
        'theme_minutos'                 => 'Minutes',
        'theme_las_mas_leidas_2'        => 'Most Read',
        'theme_no_hay_noticias_recientes' => 'No recent news.',
        'theme_informacion_institucional' => 'Institutional Information',
        'theme_conoce_mas_nuestra_org'  => 'Learn more about our organization, our history and our values.',
        'theme_no_hay_info_institucional' => 'No institutional information available at this time.',
        'theme_general'                 => 'General',
        'theme_quienes_somos'           => 'Who We Are',
        'theme_mision_vision'           => 'Mission & Vision',
        'theme_historia'                => 'History',
        'theme_organigrama'             => 'Organization Chart',
        'theme_junta_directiva'         => 'Board of Directors',
        'theme_equipo'                  => 'Team',
        'theme_valores'                 => 'Values',
        'theme_politicas'               => 'Policies',
        'theme_inicio'                  => 'Home',
        'theme_paginas'                 => 'Pages',
        'theme_pagina_no_encontrada'    => 'Page Not Found',
        'theme_pagina_no_existe'        => 'Sorry, the page you are looking for does not exist on our website. You can return to the homepage or try using the search bar.',
        'theme_volver_inicio'           => 'Back to Home',
        'theme_contactanos'             => 'Contact Us',
        'theme_tu_nombre'               => 'Your name',
        'theme_tu_correo'               => 'Your email',
        'theme_tu_telefono'             => 'Your phone',
        'theme_asunto'                  => 'Subject',
        'theme_tu_mensaje'              => 'Your message',
        'theme_enviar_mensaje'          => 'Send message',
        'theme_direccion'               => 'Address',
        'theme_correo'                  => 'Email',
        'theme_telefono'                => 'Phone',
        'theme_siguenos'                => 'Follow us',
        'theme_destacados'              => 'Featured',
        'theme_minutos_de_lectura'      => 'Minutes to Read',
        'theme_minute_read'             => 'minute read',
        'theme_views'                   => 'Views',
        'theme_noticias'                => 'News',
        'theme_columnistas'             => 'Columnists',
        'theme_nosotros'                => 'About Us',
        'theme_ver_todas'               => 'View all',
        'theme_contacto'                => 'Contact',
        'theme_buscar_noticias'         => 'Search News',
        'theme_buscar_placeholder'      => 'Type a keyword...',
        'theme_ultimas'                 => 'Latest',
        'theme_buscar_descripcion'      => 'Type what you need and press "Search".',
        'theme_volver_listado'          => 'Back to list',
        'theme_ultima_actualizacion'    => 'Last updated:',
        'theme_compartir_pagina'        => 'Share this page',
        'theme_ver_perfil'              => 'View Profile',
        'theme_nuestro_equipo'          => 'Our Team',
        'theme_conoce_nuestros_columnistas' => 'Meet Our Columnists',
        'theme_columnistas_descripcion' => 'Expert voices with unique perspectives on the topics that matter most',
        'theme_no_hay_columnistas'      => 'No columnists available',
        'theme_prox_columnistas'        => 'We will soon add new columnists to our team.',
        'theme_columnista_role'         => 'Columnist',
        'theme_quieres_unirte'          => 'Want to join our team?',
        'theme_quieres_unirte_desc'     => 'If you have experience and a passion for writing, join our team of columnists.',
        'theme_columna'                 => 'column',
        'theme_privacidad'             => 'Privacy Policy',
        'theme_terminos'                => 'Terms and Conditions',
        'theme_todos_derechos'          => 'All rights reserved',
        'theme_newsletter'             => 'Newsletter',
        'theme_suscribete'              => 'Subscribe',
        'theme_tu_email'               => 'Your email',
        'theme_autor'                  => 'Author',
        'theme_diseno_por'             => 'Designed by',
        'theme_hosting_disenno'        => 'Hosting & Design by',
        'theme_youtube'                 => 'YouTube',
        'theme_view_all'               => 'View All',
        'theme_sign_up'                => 'Sign Up',
        'theme_home'                  => 'Home',
        'theme_nuestros_columnistas'  => 'Our Columnists',
        'theme_ver_todas'            => 'View all',
        'theme_ver_todas_noticias'   => 'View all news',
        'theme_intenta_diferente'    => 'Try different keywords',
        'theme_intenta_general'     => 'Try more general keywords',
        'theme_intenta_menos'        => 'Try fewer keywords',
        'theme_conoce_mas_organizacion' => 'Learn more about our organization',
        'theme_proximamente_info'    => 'We will soon have more institutional information.',
        'theme_bienvenido_espacio'  => 'Welcome to the opinion section of',
        'theme_bienvenido'          => 'Welcome to',
        'theme_aqui_encontraras'    => 'Here you will find their most recent analysis and perspectives.',
        'theme_ver_todos_columnistas' => 'View All Columnists',
        'theme_siguiente' => 'Next',
        'theme_anterior' => 'Previous',
        'theme_siguiente_articulo' => 'Next article',
        'theme_articulo' => 'article',
        'theme_articulos' => 'articles',
        'theme_whatsapp' => 'WhatsApp',
        'theme_sugerencias' => 'Suggestions:',
        'theme_verifica_palabras' => 'Make sure all words are spelled correctly',
        'theme_cerrar' => 'Close',
        'theme_contactanos_descripcion' => 'If you need advertising, information about our services or simply want to contact us, leave us a message and we will respond as soon as possible.',
        'theme_columnas_publicadas' => 'Published columns',
        'theme_columnas_opinion' => 'Opinion Columns',
        'theme_no_ha_publicado' => 'This columnist has not published any articles yet',
        'theme_busca_noticias' => 'Search news, articles and content in',
        'theme_utiliza_buscador' => 'Use the search form to find news, articles and content.',
        'theme_pagina_busqueda' => 'search page of',
        'theme_error_pagina' => 'Error finding the page',
        'theme_leer_columna' => 'READ COLUMN',
        'theme_no_hay_disponibles' => 'No columnists available',
        'theme_volver_listado' => 'Back to list',
        'theme_volver_inicio' => 'Back to home',
        'theme_no_hay_noticias_disponibles' => 'No news available',
        'theme_pronto_tendremos' => 'We will soon have new content for you.',
        'theme_tienes_pregunta' => 'Have a question? Write to us',
        'theme_siguenos' => 'Follow us',
        'theme_populares' => 'Popular',
        'theme_tags' => 'Tags',
        'theme_resultados_busqueda' => 'Search Results',
        'theme_resultado' => 'result',
        'theme_resultados' => 'results',
        'theme_no_encontramos' => 'We could not find any results for',
        'theme_que_buscas' => 'What are you looking for?',
        'theme_admin' => 'Admin',
        'theme_noticia' => 'News',
        'theme_comentarios' => 'Comments',
        'theme_perfil_columnista' => 'Columnist Profile',
        'theme_categorias' => 'Categories',
        'theme_quick_links' => 'Quick Links',
        'theme_iniciar_sesion' => 'Login',
        'theme_usuario_correo' => 'Username or email',
        'theme_contrasena' => 'Password',
        'theme_recordarme' => 'Remember me',
        'theme_cancelar' => 'Cancel',
        'theme_escuchar_articulo' => 'Listen to article',
        'theme_sugerencias' => 'Suggestions:',
        'theme_verifica_palabras' => 'Make sure all words are spelled correctly',
        'theme_olvidaste_contrasena' => 'Forgot your password?',
        'theme_todos' => 'All',
        'theme_logo' => 'Logo',
        'theme_publicidad' => 'Advertising',
        'theme_pagina_no_encontrada' => 'Page not found',
    ];

    try {
        $insertStmt = db()->prepare("
            INSERT IGNORE INTO system_translations (lang_code, trans_key, trans_value)
            VALUES (:lang, :key, :value)
        ");

        foreach ($defaultKeys as $key => $value) {
            // Solo insertar español si no existe o está vacío
            $checkStmt = db()->prepare("SELECT trans_value FROM system_translations WHERE lang_code = 'es' AND trans_key = ?");
            $checkStmt->execute([$key]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existing || empty($existing['trans_value'])) {
                $insertStmt->execute([':lang' => 'es', ':key' => $key, ':value' => $value]);
            }
        }

        // Insertar inglés solo si no existe (no sobreescribir traducciones personalizadas)
        foreach ($defaultEnKeys as $key => $value) {
            $insertStmt->execute([':lang' => 'en', ':key' => $key, ':value' => $value]);
        }
    } catch (Throwable $e) {
        // Silently fail if table doesn't exist or other error
    }
}

function get_translations_by_key(string $key): array {
    try {
        if (!function_exists('db') || !db()) {
            return [];
        }
        $stmt = db()->prepare("SELECT lang_code, trans_value FROM system_translations WHERE trans_key = ?");
        $stmt->execute([$key]);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['lang_code']] = $row['trans_value'];
        }
        return $result;
    } catch (Throwable $e) {
        return [];
    }
}

function t_theme(string $key): string {
    $lang = defined('SITE_LANGUAGE') ? SITE_LANGUAGE : 'es';
    return t($key, $lang);
}
