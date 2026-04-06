<?php

function get_translations(string $lang = 'es'): array {
    static $cache = [];
    
    if (isset($cache[$lang])) {
        return $cache[$lang];
    }
    
    try {
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

function t(string $key, string $lang = null): string {
    global $sys;
    
    if ($lang === null) {
        $lang = $sys['admin_language'] ?? 'es';
    }
    
    $translations = get_translations($lang);
    
    if (isset($translations[$key]) && !empty($translations[$key])) {
        return $translations[$key];
    }
    
    $spanishDefaults = [
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
    ];
    
    return $spanishDefaults[$key] ?? $key;
}

function get_admin_language(): string {
    global $sys;
    return $sys['admin_language'] ?? 'es';
}

function save_translation(string $lang, string $key, string $value): bool {
    try {
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
        $stmt = db()->query("SELECT DISTINCT trans_key FROM system_translations ORDER BY trans_key");
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'trans_key');
    } catch (Throwable $e) {
        return [];
    }
}

function get_translations_by_key(string $key): array {
    try {
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
