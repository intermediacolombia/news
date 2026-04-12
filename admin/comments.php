<?php
/**
 * Panel de gestión de comentarios
 */
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/login/session.php';

if (!function_exists('truncate_text')) {
    function truncate_text($text, $limit) {
        if (empty($text)) return '';
        $text = strip_tags($text);
        return (mb_strlen($text) > $limit) ? mb_substr($text, 0, $limit) . '...' : $text;
    }
}

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
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?> - <?= NOMBRE_SITIO ?></title>
  <?php require_once __DIR__ . '/inc/header.php'; ?>
  <style>
    :root {
      --primary-color: <?= COLOR_PRIMARY ?? '#E21F0C' ?>;
      --primary-dark:  <?= COLOR_PRIMARY_HOVER_LINK ?? '#8A0002' ?>;
    }

    .stat-card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.10);
      transition: transform .2s, box-shadow .2s;
    }
    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    .stat-card .card-body {
      padding: 1.5rem 1rem;
    }
    .stat-card h3 {
      font-size: 2.2rem;
      font-weight: 700;
      margin-bottom: .2rem;
    }
    .stat-card p {
      font-size: .95rem;
      opacity: .9;
    }

    .stat-total   { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); }
    .stat-pending { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .stat-approved{ background: linear-gradient(135deg, #22c55e, #16a34a); }
    .stat-hidden  { background: linear-gradient(135deg, #6b7280, #4b5563); }

    .stat-card .stat-icon {
      font-size: 2rem;
      opacity: .35;
      position: absolute;
      right: 1rem;
      top: 1rem;
    }

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

    .nav-pills .nav-link {
      border-radius: 8px;
      color: #64748b;
      font-weight: 500;
    }
    .nav-pills .nav-link.active {
      background-color: var(--primary-color);
      color: #fff;
    }
    .nav-pills .nav-link:not(.active):hover {
      background: #f1f5f9;
      color: #1e293b;
    }

    .table thead th {
      background: #f8fafc;
      color: #475569;
      font-size: .82rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .04em;
      border-bottom: 2px solid #e2e8f0;
    }
    .table tbody tr:hover {
      background: #f8fafc;
    }
    .table td {
      vertical-align: middle;
      border-color: #f1f5f9;
    }

    .btn-approve { background: #22c55e; border-color: #22c55e; color: #fff; }
    .btn-approve:hover { background: #16a34a; border-color: #16a34a; color: #fff; }

    .pagination .page-link {
      color: var(--primary-color);
      border-radius: 6px !important;
      margin: 0 2px;
    }
    .pagination .page-item.active .page-link {
      background: var(--primary-color);
      border-color: var(--primary-color);
      color: #fff;
    }

    .main-card {
      border: none;
      border-radius: 14px;
      box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    }
  </style>
</head>
<body>
<?php require_once __DIR__ . '/inc/menu.php'; ?>

<div class="container-fluid py-4">

  <!-- Page header -->
  <div class="page-header">
    <h4><i class="fas fa-comments me-2" style="color:var(--primary-color)"></i><?= $page_title ?></h4>
    <span class="badge" style="background:var(--primary-color);font-size:.85rem;padding:.45em .9em;border-radius:8px;">
      <?= (int)$stats['total'] ?> comentarios
    </span>
  </div>

  <!-- Stat cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card stat-card stat-total text-white position-relative">
        <i class="fas fa-comments stat-icon"></i>
        <div class="card-body text-center">
          <h3><?= (int)$stats['total'] ?></h3>
          <p class="mb-0">Total</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card stat-pending text-white position-relative">
        <i class="fas fa-clock stat-icon"></i>
        <div class="card-body text-center">
          <h3><?= (int)$stats['pending'] ?></h3>
          <p class="mb-0">Pendientes</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card stat-approved text-white position-relative">
        <i class="fas fa-check-circle stat-icon"></i>
        <div class="card-body text-center">
          <h3><?= (int)$stats['approved'] ?></h3>
          <p class="mb-0">Aprobados</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card stat-hidden text-white position-relative">
        <i class="fas fa-eye-slash stat-icon"></i>
        <div class="card-body text-center">
          <h3><?= (int)$stats['hidden'] ?></h3>
          <p class="mb-0">Ocultos</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Main card -->
  <div class="card main-card">
    <div class="card-body p-4">

      <!-- Filters -->
      <ul class="nav nav-pills mb-4">
        <li class="nav-item">
          <a class="nav-link <?= $filter === 'all' ? 'active' : '' ?>" href="?filter=all">
            Todos
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $filter === 'pending' ? 'active' : '' ?>" href="?filter=pending">
            <span class="badge bg-warning me-1"><?= (int)$stats['pending'] ?></span>Pendientes
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $filter === 'approved' ? 'active' : '' ?>" href="?filter=approved">
            <span class="badge bg-success me-1"><?= (int)$stats['approved'] ?></span>Aprobados
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $filter === 'hidden' ? 'active' : '' ?>" href="?filter=hidden">
            <span class="badge bg-secondary me-1"><?= (int)$stats['hidden'] ?></span>Ocultos
          </a>
        </li>
      </ul>

      <!-- Comments table -->
      <?php if (empty($comments)): ?>
      <div class="alert alert-info text-center py-4">
        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
        No hay comentarios para mostrar
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
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
                <a href="<?= URLBASE ?>/<?= htmlspecialchars($comment['post_slug']) ?>/" target="_blank" class="text-decoration-none">
                  <?= truncate_text($comment['post_title'], 40) ?>
                </a>
                <?php else: ?>
                <span class="text-muted fst-italic">Artículo eliminado</span>
                <?php endif; ?>
              </td>
              <td>
                <div style="max-width:280px;color:#374151;">
                  <?= truncate_text(htmlspecialchars($comment['contenido']), 100) ?>
                </div>
              </td>
              <td>
                <?php if ($comment['estado'] === 'pending'): ?>
                <span class="badge bg-warning text-dark">Pendiente</span>
                <?php elseif ($comment['estado'] === 'approved'): ?>
                <span class="badge bg-success">Aprobado</span>
                <?php else: ?>
                <span class="badge bg-secondary">Oculto</span>
                <?php endif; ?>
              </td>
              <td>
                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
              </td>
              <td class="text-end">
                <?php if ($comment['estado'] !== 'approved'): ?>
                <button class="btn btn-sm btn-approve btn-approve-js" data-id="<?= $comment['id'] ?>" title="Aprobar">
                  <i class="fas fa-check"></i>
                </button>
                <?php endif; ?>
                <?php if ($comment['estado'] !== 'hidden'): ?>
                <button class="btn btn-sm btn-secondary btn-hide-js" data-id="<?= $comment['id'] ?>" title="Ocultar">
                  <i class="fas fa-eye-slash"></i>
                </button>
                <?php endif; ?>
                <button class="btn btn-sm btn-danger btn-delete-js" data-id="<?= $comment['id'] ?>" title="Eliminar">
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
      <nav class="mt-3">
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

<script>
document.addEventListener('DOMContentLoaded', function () {

  document.querySelectorAll('.btn-approve-js').forEach(btn => {
    btn.addEventListener('click', function () {
      moderateComment(this.dataset.id, 'approve', this.closest('tr'));
    });
  });

  document.querySelectorAll('.btn-hide-js').forEach(btn => {
    btn.addEventListener('click', function () {
      Swal.fire({
        title: '¿Ocultar comentario?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, ocultar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6b7280',
      }).then(r => { if (r.isConfirmed) moderateComment(this.dataset.id, 'hide', this.closest('tr')); });
    });
  });

  document.querySelectorAll('.btn-delete-js').forEach(btn => {
    btn.addEventListener('click', function () {
      Swal.fire({
        title: '¿Eliminar comentario?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
      }).then(r => { if (r.isConfirmed) moderateComment(this.dataset.id, 'delete', this.closest('tr')); });
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
        row.style.transition = 'opacity .3s';
        row.style.opacity = '0';
        setTimeout(() => row.remove(), 300);
        Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2500, timerProgressBar: true });
      } else {
        Swal.fire('Error', data.message, 'error');
      }
    })
    .catch(() => Swal.fire('Error', 'Error al procesar la solicitud', 'error'));
  }
});
</script>

<?php require_once __DIR__ . '/inc/menu-footer.php'; ?>
</body>
</html>
