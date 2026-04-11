<?php
/**
 * Panel de gestión de comentarios
 */
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/login/session.php';

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filter by status
$filter = $_GET['filter'] ?? 'all';
$where = "WHERE c.borrado = 0";
$params = [];

if ($filter === 'pending') {
    $where .= " AND c.estado = 'pending'";
} elseif ($filter === 'approved') {
    $where .= " AND c.estado = 'approved'";
} elseif ($filter === 'hidden') {
    $where .= " AND c.estado = 'hidden'";
}

// Count total
$stmt = db()->query("SELECT COUNT(*) FROM comments c $where");
$totalComments = $stmt->fetchColumn();
$totalPages = max(1, ceil($totalComments / $perPage));

// Get comments
$stmt = db()->prepare("
    SELECT c.*, p.title AS post_title, p.slug AS post_slug, u.nombre AS user_nombre, u.apellido AS user_apellido
    FROM comments c
    LEFT JOIN blog_posts p ON p.id = c.post_id
    LEFT JOIN usuarios u ON u.id = c.user_id
    $where
    ORDER BY c.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$comments = $stmt->fetchAll();

// Stats
$statsStmt = db()->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado = 'pending' AND borrado = 0 THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN estado = 'approved' AND borrado = 0 THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN estado = 'hidden' AND borrado = 0 THEN 1 ELSE 0 END) as hidden
    FROM comments
");
$stats = $statsStmt->fetch();

$page_title = 'Gestión de Comentarios';
include __DIR__ . '/inc/header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-comments text-primary me-2"></i><?= $page_title ?>
                    </h4>
                    <span class="badge bg-primary"><?= $stats['total'] ?> comentarios</span>
                </div>
                
                <div class="card-body">
                    <!-- Stats cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3><?= $stats['total'] ?></h3>
                                    <p class="mb-0">Total</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3><?= $stats['pending'] ?></h3>
                                    <p class="mb-0">Pendientes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3><?= $stats['approved'] ?></h3>
                                    <p class="mb-0">Aprobados</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h3><?= $stats['hidden'] ?></h3>
                                    <p class="mb-0">Ocultos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <ul class="nav nav-pills mb-4">
                        <li class="nav-item">
                            <a class="nav-link <?= $filter === 'all' ? 'active' : '' ?>" href="?filter=all">
                                Todos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $filter === 'pending' ? 'active' : '' ?>" href="?filter=pending">
                                <span class="badge bg-warning"><?= $stats['pending'] ?></span> Pendientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $filter === 'approved' ? 'active' : '' ?>" href="?filter=approved">
                                <span class="badge bg-success"><?= $stats['approved'] ?></span> Aprobados
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $filter === 'hidden' ? 'active' : '' ?>" href="?filter=hidden">
                                <span class="badge bg-secondary"><?= $stats['hidden'] ?></span> Ocultos
                            </a>
                        </li>
                    </ul>

                    <!-- Comments list -->
                    <?php if (empty($comments)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p class="mb-0">No hay comentarios para mostrar</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Autor</th>
                                    <th>Artículo</th>
                                    <th>Comentario</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comments as $comment): ?>
                                <tr id="comment-<?= $comment['id'] ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($comment['nombre']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($comment['email']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($comment['post_title']): ?>
                                        <a href="<?= URLBASE ?>/<?= htmlspecialchars($comment['post_slug']) ?>/" target="_blank">
                                            <?= truncate_text($comment['post_title'], 40) ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">Artículo eliminado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px;">
                                            <?= truncate_text(htmlspecialchars($comment['contenido']), 100) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($comment['estado'] === 'pending'): ?>
                                        <span class="badge bg-warning">Pendiente</span>
                                        <?php elseif ($comment['estado'] === 'approved'): ?>
                                        <span class="badge bg-success">Aprobado</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Oculto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($comment['estado'] !== 'approved'): ?>
                                        <button class="btn btn-sm btn-success btn-approve" data-id="<?= $comment['id'] ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($comment['estado'] !== 'hidden'): ?>
                                        <button class="btn btn-sm btn-secondary btn-hide" data-id="<?= $comment['id'] ?>">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $comment['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&filter=<?= $filter ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Approve comment
    document.querySelectorAll('.btn-approve').forEach(btn => {
        btn.addEventListener('click', function() {
            moderateComment(this.dataset.id, 'approve', this.closest('tr'));
        });
    });

    // Hide comment
    document.querySelectorAll('.btn-hide').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('¿Ocultar este comentario?')) {
                moderateComment(this.dataset.id, 'hide', this.closest('tr'));
            }
        });
    });

    // Delete comment
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('¿Eliminar permanentemente este comentario?')) {
                moderateComment(this.dataset.id, 'delete', this.closest('tr'));
            }
        });
    });

    function moderateComment(commentId, action, row) {
        const formData = new FormData();
        formData.append('comment_id', commentId);
        formData.append('action', action);

        fetch('<?= URLBASE ?>/actions/moderate_comment.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
                
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
                alert.style.zIndex = '9999';
                alert.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alert);
                
                setTimeout(() => alert.remove(), 3000);
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            alert('Error al procesar');
            console.error(err);
        });
    }
});
</script>

<?php include __DIR__ . '/inc/footer_admin.php'; ?>
