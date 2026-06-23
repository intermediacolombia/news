<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Radio';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

$id = (int)($_GET['id'] ?? 0);
$program = null;
try {
    $stmt = db()->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->execute([$id]);
    $program = $stmt->fetch();
} catch (Throwable $e) {}

if (!$program) {
    setFlash('error', 'Programa no encontrado');
    header('Location: ' . URLBASE . '/admin/radio/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = trim($_POST['title'] ?? '');
    $slug   = trim($_POST['slug']  ?? $program['slug']);
    $errors = [];

    if (empty($title)) $errors[] = 'El título es obligatorio';
    $slug = mb_substr(strtolower(preg_replace('/[^a-z0-9-]/i', '', $slug)), 0, 200);

    if (!$errors) {
        $check = db()->prepare("SELECT COUNT(*) FROM programs WHERE slug = ? AND id != ?");
        $check->execute([$slug, $id]);
        if ($check->fetchColumn() > 0) $errors[] = 'El slug ya existe';
    }

    $imagePath = $program['image'];
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $errors[] = 'Imagen: solo JPG, PNG, WebP';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Imagen: máximo 5MB';
        } else {
            $dir = __DIR__ . '/../../public/images/programs/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $filename)) {
                if ($program['image'] && file_exists(__DIR__ . '/../../' . $program['image'])) {
                    @unlink(__DIR__ . '/../../' . $program['image']);
                }
                $fullPath  = convert_image_to_webp($dir . $filename);
                $filename  = basename($fullPath);
                $imagePath = 'public/images/programs/' . $filename;
            }
        }
    }

    if (empty($errors)) {
        $stmt = db()->prepare("UPDATE programs SET title=?, slug=?, description=?, image=?, category=?, hosts=?, schedule_info=?, status=? WHERE id=?");
        $stmt->execute([
            mb_substr($title, 0, 255),
            $slug,
            mb_substr(trim($_POST['description'] ?? ''), 0, 5000),
            $imagePath,
            mb_substr(trim($_POST['category'] ?? ''), 0, 100),
            mb_substr(trim($_POST['hosts'] ?? ''), 0, 255),
            mb_substr(trim($_POST['schedule_info'] ?? ''), 0, 255),
            in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active',
            $id,
        ]);
        log_system_action('Editar Programa', $title, 'programs', $id);
        setFlash('success', 'Programa actualizado');
        header('Location: ' . URLBASE . '/admin/radio/');
        exit;
    }
    setFlash('error', implode('<br>', $errors));
    $program = array_merge($program, $_POST);
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Editar Programa</title>
<?php include('../inc/header.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="page-wrapper">
    <div class="container-fluid">
        <div class="page-header">
            <h4><i class="fas fa-broadcast-tower me-2" style="color:var(--primary-color)"></i>Editar: <?= htmlspecialchars($program['title']) ?></h4>
            <a href="<?= URLBASE ?>/admin/radio/" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
        <?php renderFlashMessages(); ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Título *</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($program['title']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($program['slug']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($program['description'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Categoría</label>
                                <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($program['category'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Conductores</label>
                                <input type="text" name="hosts" class="form-control" value="<?= htmlspecialchars($program['hosts'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Horario general</label>
                        <input type="text" name="schedule_info" class="form-control" value="<?= htmlspecialchars($program['schedule_info'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Imagen</label>
                        <?php if (!empty($program['image'])): ?>
                            <div class="mb-2">
                                <img src="<?= URLBASE . '/' . htmlspecialchars($program['image']) ?>" style="max-height:120px; border-radius:8px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="active"   <?= $program['status'] === 'active'   ? 'selected' : '' ?>>Activo</option>
                            <option value="inactive" <?= $program['status'] === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Guardar cambios
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>
</body>
</html>
