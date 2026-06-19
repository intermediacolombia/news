# Diseño: Nuevos módulos JR FRECUENCIA VITAL
**Fecha:** 2026-06-18  
**Estado:** Aprobado  
**Sistema:** NEWS — PHP puro + PDO + MySQL, tema Artemis

---

## Contexto

Plataforma de noticias/radio existente. El cliente JR FRECUENCIA VITAL requiere 6 módulos nuevos. No se toca ninguna funcionalidad existente.

**Fuera de alcance (ya existe o no aplica):**
- Sobre Nosotros / Misión / Historia → ya cubiertos por `institutional_pages` (sección Institucional)
- Página Escuchar → el player de streaming ya existe en el sistema
- Email de confirmación de suscripción
- CAPTCHA / rate limiting

---

## 1. Base de datos

Todas las tablas se crean via `db_repair.php` para migración automática en deploy.

### `programs`
```sql
CREATE TABLE programs (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(255) NOT NULL,
  slug         VARCHAR(255) NOT NULL UNIQUE,
  description  TEXT,
  image        VARCHAR(500),
  category     VARCHAR(100),
  hosts        VARCHAR(255),
  schedule_info VARCHAR(255),
  status       ENUM('active','inactive') DEFAULT 'active',
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
)
```

### `schedules`
```sql
CREATE TABLE schedules (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  program_id   INT NOT NULL,
  day_of_week  ENUM('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
  start_time   TIME NOT NULL,
  end_time     TIME NOT NULL,
  host         VARCHAR(255),
  status       ENUM('active','inactive') DEFAULT 'active',
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
)
```

### `subscribers`
```sql
CREATE TABLE subscribers (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  name             VARCHAR(255) NOT NULL,
  email            VARCHAR(255) NOT NULL UNIQUE,
  privacy_accepted TINYINT(1) DEFAULT 0,
  status           ENUM('active','inactive') DEFAULT 'active',
  created_at       DATETIME DEFAULT CURRENT_TIMESTAMP
)
```

### `contact_messages`
```sql
CREATE TABLE contact_messages (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(255) NOT NULL,
  email      VARCHAR(255) NOT NULL,
  phone      VARCHAR(50),
  message    TEXT NOT NULL,
  status     ENUM('unread','read') DEFAULT 'unread',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)
```

### `legal_pages`
```sql
CREATE TABLE legal_pages (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  slug       VARCHAR(100) NOT NULL UNIQUE,
  title      VARCHAR(255) NOT NULL,
  content    LONGTEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
-- Seed: dos filas fijas
INSERT IGNORE INTO legal_pages (slug, title, content) VALUES
  ('aviso-legal', 'Aviso Legal', ''),
  ('politica-privacidad', 'Política de Privacidad', '')
```

---

## 2. Rutas nuevas (`index.php`)

Se insertan **antes** del catch-all de páginas estáticas.

| URL | Template |
|-----|----------|
| `/programacion/` | `template/Artemis/programacion.php` |
| `/programas/` | `template/Artemis/programas.php` |
| `/programas/{slug}/` | `template/Artemis/programa-single.php` |
| `/suscripcion/` | `template/Artemis/suscripcion.php` |
| `/contacto/` | `template/Artemis/contacto.php` |
| `/aviso-legal/` | `template/Artemis/aviso-legal.php` |
| `/politica-privacidad/` | `template/Artemis/politica-privacidad.php` |

El template `contact.php` existente se deja intacto. La ruta `/contacto/` apunta al nuevo template.

---

## 3. Panel administrativo

### Nuevas carpetas
```
admin/
├── radio/
│   ├── index.php        (listado de programas)
│   ├── create.php
│   ├── edit.php
│   ├── delete.php
│   └── schedules.php    (parrilla semanal)
├── subscribers/
│   └── index.php
├── contact/
│   └── index.php
└── legal/
    └── index.php        (una página, dos secciones con tabs: Aviso Legal | Política de Privacidad)
```

### Entradas nuevas en `admin/inc/menu.php`
```
📻 Radio         → submenu: Programas (/admin/radio/) | Programación (/admin/radio/schedules.php)
📧 Suscriptores  → /admin/subscribers/
✉️ Mensajes      → /admin/contact/  (badge con no leídos)
⚖️ Páginas Legales → /admin/legal/
```

### Nuevos permisos en `db_repair.php`
```
Gestionar Radio
Gestionar Suscriptores
Gestionar Mensajes
Gestionar Páginas Legales
```

---

## 4. Plantillas públicas (tema Artemis)

Todas usan `var(--dark)`, `var(--text-color)`, `btn-artemis`, `section-title` — sin CSS externo nuevo.

| Template | Descripción |
|----------|-------------|
| `programacion.php` | Tabs por día de la semana. Cards con programa + horario. Badge "EN VIVO" cuando `now()` cae entre `start_time` y `end_time` del día actual. |
| `programas.php` | Grid de cards: imagen, título, categoría, conductores. |
| `programa-single.php` | Hero con imagen, descripción completa, tabla de horarios asociados. |
| `suscripcion.php` | Form: nombre + email + checkbox RGPD obligatorio. Envío AJAX → `actions/subscribe.php`. |
| `contacto.php` | Form: nombre + email + teléfono (opcional) + mensaje. Envío AJAX → `actions/contact.php`. |
| `aviso-legal.php` | Título + contenido HTML desde `legal_pages WHERE slug='aviso-legal'`. |
| `politica-privacidad.php` | Ídem `slug='politica-privacidad'`. |

### Actions nuevas
```
actions/subscribe.php   → POST: valida, honeypot, guarda en subscribers, JSON response
actions/contact.php     → POST: valida, honeypot, guarda en contact_messages, JSON response
```

---

## 5. SEO

Cada template define antes del render:
```php
$page_title       = "Título | " . NOMBRE_SITIO;
$page_description = "Descripción de la página";
$page_canonical   = rtrim(URLBASE,'/') . '/ruta/';
```
H1 único por página. URLs amigables via router.

---

## 6. Seguridad

- Validación server-side en todas las acciones POST
- `htmlspecialchars()` en todos los outputs
- Campo honeypot oculto en formularios de suscripción y contacto
- Constraint `UNIQUE` en `subscribers.email`
- Errores genéricos al usuario (sin exponer stack traces)
- Checkbox RGPD obligatorio, enlaza a `/politica-privacidad`
- Acciones admin protegidas por sesión (patrón existente)

---

## 7. Menú público (Artemis)

Archivo: `template/Artemis/inc/menu-header.php`  
Agregar enlaces nuevos al nav existente:

| Ítem | URL |
|------|-----|
| Programación | `/programacion/` |
| Programas | `/programas/` |
| Suscripción | `/suscripcion/` |
| Contacto | `/contacto/` |

El ítem **Escuchar** enlaza al player ya existente en el sistema (URL configurada previamente). No se implementa página nueva.

**Regla: ocultar ítems sin contenido.** Cada ítem del menú se renderiza condicionalmente:
- **Programación** → solo si existe al menos 1 schedule activo en BD
- **Programas** → solo si existe al menos 1 programa activo en BD
- **Aviso Legal** / **Política de Privacidad** (footer) → solo si `content` no está vacío en `legal_pages`
- Suscripción y Contacto → siempre visibles (son formularios, no dependen de contenido)

---

## 8. Orden de implementación sugerido

1. `db_repair.php` — tablas + permisos
2. Rutas en `index.php`
3. Actions (`subscribe.php`, `contact.php`)
4. Templates públicos (programacion, programas, programa-single, suscripcion, contacto, legales)
5. Admin radio (programas + schedules)
6. Admin subscribers + contact + legal
7. Menú admin actualizado
