<?php

function get_translations(string $lang = 'es'): array {
    static $cache = [];
    
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
    $cache = [];
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
        'theme_inicio' => 'Inicio',
        'theme_noticias' => 'Noticias',
        'theme_categorias' => 'Categorías',
        'theme_columnistas' => 'Columnistas',
        'theme_nosotros' => 'Nosotros',
        'theme_ver_todas' => 'Ver todas',
        'theme_contacto' => 'Contacto',
        'theme_buscar_noticias' => 'Buscar noticias',
        'theme_buscar_placeholder' => 'Escribe una palabra clave...',
    ];
    
    try {
        foreach ($defaultKeys as $key => $value) {
            $stmt = db()->prepare("
                INSERT IGNORE INTO system_translations (lang_code, trans_key, trans_value)
                VALUES ('es', :key, :value)
            ");
            $stmt->execute([':key' => $key, ':value' => $value]);
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
