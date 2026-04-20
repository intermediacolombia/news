<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';

$permisopage = 'Editar Configuraciones';
require_once __DIR__ . '/../login/restriction.php';

if (!headers_sent()) header('Content-Type: text/html; charset=UTF-8');
db()->exec("SET NAMES utf8mb4");

$stmt = db()->query("SHOW TABLES LIKE 'popups'");
if ($stmt->rowCount() == 0) {
    db()->exec("
        CREATE TABLE IF NOT EXISTS popups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            image VARCHAR(500),
            position ENUM('center', 'bottom-right', 'bottom-left', 'top-right', 'top-left') DEFAULT 'center',
            popup_type ENUM('modal', 'notification') DEFAULT 'modal',
            width VARCHAR(10) DEFAULT '500px',
            show_on_homepage TINYINT(1) DEFAULT 0,
            show_on_all_pages TINYINT(1) DEFAULT 1,
            show_once_per_visit TINYINT(1) DEFAULT 1,
            delay_seconds INT DEFAULT 3,
            background_color VARCHAR(20) DEFAULT '#ffffff',
            text_color VARCHAR(20) DEFAULT '#333333',
            button_text VARCHAR(100) DEFAULT 'Cerrar',
            button_color VARCHAR(20) DEFAULT '#007bff',
            button_text_color VARCHAR(20) DEFAULT '#ffffff',
            action_type ENUM('none', 'link', 'redirect') DEFAULT 'none',
            action_url VARCHAR(500),
            action_new_tab TINYINT(1) DEFAULT 0,
            auto_close_seconds INT DEFAULT 0,
            overlay_enabled TINYINT(1) DEFAULT 1,
            show_title TINYINT(1) DEFAULT 1,
            status ENUM('active', 'inactive') DEFAULT 'inactive',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "Tabla creada. <a href='index.php'>Continuar</a>";
    exit;
}

$popups = db()->query("SELECT * FROM popups ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Gestionar Popups</title>
    <?php require_once __DIR__ . '/../inc/header.php'; ?>
    <style>
        .popup-preview { max-width: 100%; height: 200px; object-fit: cover; border-radius: 8px; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .page-header {
            background: #fff;
            border-radius: 12px;
            padding: 1.2rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .5rem;
        }
        .page-header h4 {
            margin: 0;
            font-weight: 700;
            color: #1e293b;
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="page-wrapper">
    <?php require_once __DIR__ . '/../inc/flash_simple.php'; ?>

    <!-- Page header -->
    <div class="page-header">
        <h4><i class="fa fa-bullhorn me-2" style="color:var(--primary-color)"></i>Gestionar Popups</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#popupModal" onclick="resetForm()">
            <i class="fa fa-plus"></i> Nuevo Popup
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover bg-white rounded">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Posición</th>
                    <th>Mostrar en</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($popups as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['title']) ?></td>
                    <td><?= $p['popup_type'] === 'modal' ? 'Modal' : 'Notificación' ?></td>
                    <td><?= str_replace('-', ' ', ucfirst($p['position'])) ?></td>
                    <td><?= $p['show_on_all_pages'] ? 'Todas las páginas' : 'Solo inicio' ?></td>
                    <td>
                        <span class="status-badge <?= $p['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                            <?= $p['status'] === 'active' ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editPopup(<?= $p['id'] ?>)">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deletePopup(<?= $p['id'] ?>)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($popups)): ?>
                <tr><td colspan="7" class="text-center text-muted">No hay popups configurados</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="popupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="popupForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><?= isset($editPopup) ? 'Editar' : 'Nuevo' ?> Popup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="popupId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Título</label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Popup</label>
                            <select class="form-select" name="popup_type" id="popup_type">
                                <option value="modal">Modal (ventana)</option>
                                <option value="notification">Notificación flotante</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contenido (HTML)</label>
                        <textarea class="form-control summernote" name="content" id="content" rows="4"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Imagen</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <input type="hidden" name="existing_image" id="existing_image">
                            <img id="previewImage" class="popup-preview mt-2" style="display:none;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Posición</label>
                            <select class="form-select" name="position" id="position">
                                <option value="center">Centro</option>
                                <option value="bottom-right">Esquina inferior derecha</option>
                                <option value="bottom-left">Esquina inferior izquierda</option>
                                <option value="top-right">Esquina superior derecha</option>
                                <option value="top-left">Esquina superior izquierda</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ancho</label>
                            <input type="text" class="form-control" name="width" id="width" value="500px">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Color de fondo</label>
                            <input type="color" class="form-control" name="background_color" id="background_color" value="#ffffff">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Color de texto</label>
                            <input type="color" class="form-control" name="text_color" id="text_color" value="#333333">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mostrar en</label>
                            <select class="form-select" name="show_on_pages" id="show_on_pages">
                                <option value="all">Todas las páginas</option>
                                <option value="homepage">Solo página de inicio</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Frecuencia</label>
                            <select class="form-select" name="show_once_per_visit" id="show_once_per_visit">
                                <option value="1">Una vez por visita</option>
                                <option value="0">Cada vez que visite</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Retraso (segundos)</label>
                            <input type="number" class="form-control" name="delay_seconds" id="delay_seconds" value="3" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Auto-cerrar (segundos, 0=desactivado)</label>
                            <input type="number" class="form-control" name="auto_close_seconds" id="auto_close_seconds" value="0" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="status" id="status">
                                <option value="inactive">Inactivo</option>
                                <option value="active">Activo</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3">Botón cerrar</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Texto del botón</label>
                            <input type="text" class="form-control" name="button_text" id="button_text" value="Cerrar">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Color del botón</label>
                            <input type="color" class="form-control" name="button_color" id="button_color" value="#007bff">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Color de texto</label>
                            <input type="color" class="form-control" name="button_text_color" id="button_text_color" value="#ffffff">
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3">Acción al hacer clic</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo de acción</label>
                            <select class="form-select" name="action_type" id="action_type">
                                <option value="none">Sin acción</option>
                                <option value="link">Abrir enlace</option>
                                <option value="redirect">Redireccionar</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" name="action_url" id="action_url" placeholder="https://...">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Nueva pestaña</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="action_new_tab" id="action_new_tab" value="1">
                                <label class="form-check-label">Sí</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="overlay_enabled" id="overlay_enabled" value="1" checked>
                            <label class="form-check-label">Mostrar fondo oscurecido (overlay)</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_title" id="show_title" value="1" checked>
                            <label class="form-check-label">Mostrar título</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSave">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (!empty($_SESSION['flash'])): $flashes = $_SESSION['flash']; unset($_SESSION['flash']); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const queue = <?= json_encode($flashes, JSON_UNESCAPED_UNICODE) ?>;
    const iconMap = { success:'success', error:'error', warning:'warning', info:'info' };
    (async () => { for (const f of queue) await Swal.fire({ icon: iconMap[f.type]||'info', title: f.msg, confirmButtonText:'OK' }); })();
});
</script>
<?php endif; ?>

<script>
function resetForm() {
    document.getElementById('popupForm').reset();
    document.getElementById('popupId').value = '';
    document.getElementById('previewImage').style.display = 'none';
    document.getElementById('previewImage').src = '';
}

function editPopup(id) {
    fetch('ajax_popup.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const p = data.popup;
                document.getElementById('popupId').value = p.id;
                document.getElementById('title').value = p.title;
                document.getElementById('popup_type').value = p.popup_type;
                document.getElementById('position').value = p.position;
                document.getElementById('width').value = p.width;
                document.getElementById('background_color').value = p.background_color;
                document.getElementById('text_color').value = p.text_color;
                document.getElementById('delay_seconds').value = p.delay_seconds;
                document.getElementById('auto_close_seconds').value = p.auto_close_seconds;
                document.getElementById('button_text').value = p.button_text;
                document.getElementById('button_color').value = p.button_color;
                document.getElementById('button_text_color').value = p.button_text_color;
                document.getElementById('action_type').value = p.action_type;
                document.getElementById('action_url').value = p.action_url || '';
                document.getElementById('action_new_tab').checked = p.action_new_tab == '1';
                document.getElementById('overlay_enabled').checked = p.overlay_enabled == '1';
                document.getElementById('show_title').checked = p.show_title == '1';
                document.getElementById('status').value = p.status;
                document.getElementById('show_on_pages').value = p.show_on_all_pages == '1' ? 'all' : 'homepage';
                document.getElementById('show_once_per_visit').value = p.show_once_per_visit;
                document.getElementById('existing_image').value = p.image || '';
                
                if (p.image) {
                    const imgName = p.image.split('/').pop();
                    document.getElementById('previewImage').src = '<?= URLBASE ?>/public/uploads/popups/' + imgName;
                    document.getElementById('previewImage').style.display = 'block';
                } else {
                    document.getElementById('previewImage').style.display = 'none';
                }
                
                if (window.editorGetContent) {
                    editorSetContent('content', p.content || '');
                } else {
                    document.getElementById('content').value = p.content || '';
                }
                
                new bootstrap.Modal(document.getElementById('popupModal')).show();
            }
        });
}

function deletePopup(id) {
    Swal.fire({
        title: '¿Eliminar popup?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch('ajax_popup.php?delete=1&id=' + id, { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.success) location.reload();
                else Swal.fire({ icon: 'error', title: data.message });
            });
    });
}

document.getElementById('popupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';
    
    try {
        const res = await fetch('ajax_popup.php', {
            method: 'POST',
            body: new FormData(this)
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: data.message, timer: 1500 }).then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error', text: err.message });
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Guardar';
    }
});
</script>

<?php require_once __DIR__ . '/../inc/summernote.php'; ?>
</body>
</html>
