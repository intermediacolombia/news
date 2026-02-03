-- CMS News - Database Schema
-- Generado automáticamente para instalación

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Tabla: ads
CREATE TABLE `ads` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(180) DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `position` int UNSIGNED NOT NULL DEFAULT '1',
  `status` enum('active','inactive') DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ads_position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ads_gallery
CREATE TABLE `ads_gallery` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `section` tinyint NOT NULL DEFAULT '3',
  `title` varchar(180) DEFAULT NULL,
  `type` enum('horizontal','square') DEFAULT 'horizontal',
  `image_url` varchar(255) NOT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: banners
CREATE TABLE `banners` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` enum('home1','home2','category','related') NOT NULL,
  `slot` tinyint UNSIGNED NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_banner` (`type`,`slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: blog_categories
CREATE TABLE `blog_categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(180) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `deleted` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: blog_posts
CREATE TABLE `blog_posts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author` varchar(180) DEFAULT 'Admin',
  `author_user` varchar(100) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `deleted` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `seo_title` varchar(180) DEFAULT NULL,
  `seo_description` varchar(300) DEFAULT NULL,
  `seo_keywords` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: blog_post_category
CREATE TABLE `blog_post_category` (
  `post_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`post_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `blog_post_category_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blog_post_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: blog_post_views
CREATE TABLE `blog_post_views` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` bigint UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_post_ip` (`post_id`,`ip_address`),
  CONSTRAINT `blog_post_views_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Tabla: institutional_pages
CREATE TABLE `institutional_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT 'Título de la página',
  `slug` varchar(255) NOT NULL COMMENT 'URL amigable',
  `content` longtext NOT NULL COMMENT 'Contenido de la página (HTML)',
  `page_type` enum('general','about','mission','history','organization','board','team','values','policies') DEFAULT 'general' COMMENT 'Tipo de página institucional',
  `status` enum('draft','published') DEFAULT 'draft' COMMENT 'Estado de publicación',
  `image` varchar(500) DEFAULT NULL COMMENT 'Ruta de la imagen destacada',
  `display_order` int DEFAULT '0' COMMENT 'Orden de visualización en menús',
  `seo_title` varchar(180) DEFAULT NULL COMMENT 'Título para SEO',
  `seo_description` varchar(300) DEFAULT NULL COMMENT 'Descripción para SEO',
  `seo_keywords` varchar(300) DEFAULT NULL COMMENT 'Keywords para SEO',
  `created_by` int DEFAULT NULL COMMENT 'ID del usuario creador',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `page_type` (`page_type`),
  KEY `display_order` (`display_order`),
  KEY `created_by` (`created_by`),
  FULLTEXT KEY `search_index` (`title`,`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Páginas institucionales del sitio';

-- Tabla: password_resets
CREATE TABLE `password_resets` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_unique` (`token`),
  KEY `idx_pr_user` (`user_id`),
  CONSTRAINT `fk_pr_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: permissions
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_perm_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales: permissions
INSERT INTO `permissions` (`id`, `name`, `category`) VALUES
(1, 'Ver y Editar Usuarios', 'Usuarios'),
(2, 'Gestionar Roles', 'Usuarios'),
(4, 'Ver Blogs', 'Blog'),
(6, 'Crear Entrada', 'Blog'),
(7, 'Editar Entrada', 'Blog'),
(8, 'Borrar Entrada', 'Blog'),
(9, 'Ver Categorias', 'Blog'),
(10, 'Editar Categorias', 'Blog'),
(11, 'Borrar Categorias', 'Blog'),
(12, 'Crear Categorias', 'Blog'),
(13, 'Editar Configuraciones', 'Sistema'),
(15, 'Manejar Publicidad', 'Publicidad'),
(16, 'Ver Otras Entradas', 'Blog'),
(17, 'Editar Institucional', 'Marca'),
(18, 'Crear Institucional', 'Marca'),
(19, 'Eliminar Institucional', 'Marca'),
(21, 'Ver Institucional', 'Marca');

-- Tabla: roles
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `borrado` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: role_permissions
CREATE TABLE `role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `idx_rp_perm` (`permission_id`),
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: sliders
CREATE TABLE `sliders` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `titulo_color` varchar(7) NOT NULL DEFAULT '#0000',
  `subtitulo` varchar(255) DEFAULT NULL,
  `subtitulo_color` varchar(7) NOT NULL DEFAULT '#0000',
  `descripcion` text,
  `descripcion_color` varchar(7) NOT NULL DEFAULT '#0000',
  `boton_texto` varchar(100) DEFAULT 'Shop Now',
  `boton_color` varchar(7) NOT NULL DEFAULT '#ffff',
  `boton_url` varchar(255) DEFAULT NULL,
  `imagen` varchar(255) NOT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `orden` int UNSIGNED DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: system_settings
CREATE TABLE `system_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(100) NOT NULL,
  `value` text,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: user_tokens
CREATE TABLE `user_tokens` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_unique` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: usuarios
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `rol_id` int DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','user') NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `borrado` tinyint(1) NOT NULL DEFAULT '0',
  `intentos` tinyint(1) NOT NULL DEFAULT '0',
  `es_columnista` tinyint(1) DEFAULT '0' COMMENT 'Indica si el usuario es columnista',
  `foto_perfil` varchar(255) DEFAULT NULL COMMENT 'Ruta de la imagen de perfil',
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `username` (`username`),
  KEY `rol_id` (`rol_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: visit_stats
CREATE TABLE `visit_stats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_hash` varchar(64) NOT NULL,
  `user_agent` text,
  `page_url` varchar(255) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `visit_time` datetime NOT NULL,
  `is_unique` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_visit` (`ip_hash`,`visit_date`),
  KEY `idx_date` (`visit_date`),
  KEY `idx_ip` (`ip_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
