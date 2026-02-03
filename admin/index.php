<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/login/session.php';

// =============================================
// CONSULTAS PARA EL DASHBOARD
// =============================================

// 1. Contar posts totales
$totalPosts = db()->query("
    SELECT COUNT(*) FROM blog_posts 
    WHERE deleted = 0
")->fetchColumn();

// 2. Contar posts publicados
$postsPublicados = db()->query("
    SELECT COUNT(*) FROM blog_posts 
    WHERE status = 'published' AND deleted = 0
")->fetchColumn();

// 3. Contar borradores
$postsBorradores = db()->query("
    SELECT COUNT(*) FROM blog_posts 
    WHERE status = 'draft' AND deleted = 0
")->fetchColumn();

// 4. Total de vistas este mes
$vistasEsteMes = db()->query("
    SELECT COUNT(*) FROM blog_post_views 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
      AND YEAR(created_at) = YEAR(CURRENT_DATE())
")->fetchColumn();

// 5. Total de categorías activas
$totalCategorias = db()->query("
    SELECT COUNT(*) FROM blog_categories 
    WHERE deleted = 0
")->fetchColumn();

// 6. Total de columnistas
$totalColumnistas = db()->query("
    SELECT COUNT(*) FROM usuarios 
    WHERE es_columnista = 1 AND estado = 0 AND borrado = 0
")->fetchColumn();

// 7. Últimos 5 posts publicados
$ultimosPosts = db()->query("
    SELECT p.id, p.title, p.slug, p.created_at, p.status,
           c.name AS category_name, c.slug AS category_slug,
           (SELECT COUNT(*) FROM blog_post_views WHERE post_id = p.id) AS views
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.deleted = 0
    ORDER BY p.created_at DESC
    LIMIT 5
")->fetchAll();

// 8. Top 5 posts más vistos
$topPosts = db()->query("
    SELECT p.id, p.title, p.slug, p.image,
           COUNT(v.id) AS total_views,
           c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_views v ON v.post_id = p.id
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status = 'published' AND p.deleted = 0
    GROUP BY p.id
    ORDER BY total_views DESC
    LIMIT 5
")->fetchAll();

// 9. Posts por mes (últimos 6 meses)
$postsPorMes = db()->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS mes,
        COUNT(*) AS total
    FROM blog_posts
    WHERE deleted = 0
      AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
    GROUP BY mes
    ORDER BY mes ASC
")->fetchAll();

// 10. Categorías más populares
$categoriasPopulares = db()->query("
    SELECT c.name, COUNT(pc.post_id) AS total_posts
    FROM blog_categories c
    LEFT JOIN blog_post_category pc ON pc.category_id = c.id
    LEFT JOIN blog_posts p ON p.id = pc.post_id
    WHERE c.deleted = 0 AND p.deleted = 0
    GROUP BY c.id
    ORDER BY total_posts DESC
    LIMIT 5
")->fetchAll();

// 11. Actividad reciente
$actividadReciente = db()->query("
    SELECT 
        p.title,
        p.created_at,
        p.status,
        CONCAT(u.nombre, ' ', u.apellido) AS autor
    FROM blog_posts p
    LEFT JOIN usuarios u ON u.username = p.author_user
    WHERE p.deleted = 0
    ORDER BY p.updated_at DESC
    LIMIT 8
")->fetchAll();

// Helper para imagen
function img_url_dashboard($path) {
    if (empty($path)) return URLBASE . '/template/NewsEdge/img/news/default.jpg';
    if (preg_match('#^https?://#i', $path)) return $path;
    return URLBASE . '/' . ltrim($path, '/');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - <?= NOMBRE_SITIO ?></title>
  <?php require_once __DIR__ . '/inc/header.php'; ?>
  
  <!-- Chart.js para gráficos -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  
  <!-- Font Awesome (si no está en header.php) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <style>
    :root {      
      --primary-dark: <?= COLOR_PRIMARY_HOVER_LINK ?? '#007bff' ?>;
      --accent-gold: #DDC686;
      --bg-light: #f8f9fa;
      --card-shadow: 0 4px 15px rgba(0,0,0,0.08);
      --card-hover: 0 8px 25px rgba(0,0,0,0.12);
    }
    
    body {
      background: var(--bg-light);
    }
    
    /* ===== HEADER BIENVENIDA ===== */
    .dashboard-header {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      border-radius: 16px;
      padding: 40px;
      margin-bottom: 30px;
      color: white;
      box-shadow: var(--card-shadow);
      position: relative;
      overflow: hidden;
    }
    
    .dashboard-header::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 300px;
      height: 300px;
      background: rgba(255,255,255,0.1);
      border-radius: 50%;
    }
    
    .dashboard-header h1 {
      font-size: 2rem;
      font-weight: 700;
      margin: 0;
      position: relative;
      z-index: 2;
    }
    
    .dashboard-header .user-name {
      color: var(--accent-gold);
      font-size: 2.5rem;
    }
    
    .dashboard-header .welcome-date {
      opacity: 0.9;
      font-size: 0.95rem;
      margin-top: 10px;
    }
    
    /* ===== STAT CARDS ===== */
    .stat-card {
      background: white;
      border-radius: 12px;
      padding: 25px;
      box-shadow: var(--card-shadow);
      transition: all 0.3s ease;
      border-left: 4px solid transparent;
      height: 100%;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--card-hover);
    }
    
    .stat-card.primary {
      border-left-color: var(--primary-color);
    }
    
    .stat-card.success {
      border-left-color: #28a745;
    }
    
    .stat-card.warning {
      border-left-color: #ffc107;
    }
    
    .stat-card.info {
      border-left-color: #17a2b8;
    }
    
    .stat-card-icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      margin-bottom: 15px;
    }
    
    .stat-card.primary .stat-card-icon {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      color: white;
    }
    
    .stat-card.success .stat-card-icon {
      background: linear-gradient(135deg, #28a745, #20c997);
      color: white;
    }
    
    .stat-card.warning .stat-card-icon {
      background: linear-gradient(135deg, #ffc107, #fd7e14);
      color: white;
    }
    
    .stat-card.info .stat-card-icon {
      background: linear-gradient(135deg, #17a2b8, #007bff);
      color: white;
    }
    
    .stat-card-value {
      font-size: 2.5rem;
      font-weight: 700;
      color: #2c3e50;
      line-height: 1;
      margin-bottom: 5px;
    }
    
    .stat-card-label {
      color: #6c757d;
      font-size: 0.9rem;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    /* ===== WIDGET CARDS ===== */
    .widget-card {
      background: white;
      border-radius: 12px;
      padding: 25px;
      box-shadow: var(--card-shadow);
      margin-bottom: 25px;
      height: 100%;
    }
    
    .widget-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }
    
    .widget-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: #2c3e50;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .widget-icon {
      color: var(--primary-color);
    }
    
    /* ===== TABLA POSTS ===== */
    .posts-table {
      width: 100%;
      font-size: 0.9rem;
    }
    
    .posts-table thead {
      background: #f8f9fa;
    }
    
    .posts-table th {
      font-weight: 600;
      color: #495057;
      padding: 12px;
      border: none;
    }
    
    .posts-table td {
      padding: 12px;
      vertical-align: middle;
      border-bottom: 1px solid #f0f0f0;
    }
    
    .posts-table tbody tr:hover {
      background: #f8f9fa;
    }
    
    .post-title-link {
      color: #2c3e50;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s;
    }
    
    .post-title-link:hover {
      color: var(--primary-color);
    }
    
    .badge-status {
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .badge-published {
      background: #d4edda;
      color: #155724;
    }
    
    .badge-draft {
      background: #fff3cd;
      color: #856404;
    }
    
    /* ===== TOP POSTS ===== */
    .top-post-item {
      display: flex;
      gap: 15px;
      padding: 15px;
      border-radius: 8px;
      transition: all 0.3s;
      margin-bottom: 12px;
    }
    
    .top-post-item:hover {
      background: #f8f9fa;
    }
    
    .top-post-img {
      width: 80px;
      height: 80px;
      border-radius: 8px;
      object-fit: cover;
      flex-shrink: 0;
    }
    
    .top-post-info {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    
    .top-post-title {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 5px;
      font-size: 0.95rem;
    }
    
    .top-post-views {
      color: #6c757d;
      font-size: 0.85rem;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    /* ===== ACTIVITY TIMELINE ===== */
    .activity-item {
      display: flex;
      gap: 15px;
      padding: 15px 0;
      border-bottom: 1px solid #f0f0f0;
    }
    
    .activity-item:last-child {
      border-bottom: none;
    }
    
    .activity-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 16px;
    }
    
    .activity-icon.published {
      background: #d4edda;
      color: #28a745;
    }
    
    .activity-icon.draft {
      background: #fff3cd;
      color: #ffc107;
    }
    
    .activity-content {
      flex: 1;
    }
    
    .activity-title {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 3px;
      font-size: 0.9rem;
    }
    
    .activity-meta {
      color: #6c757d;
      font-size: 0.8rem;
    }
    
    /* ===== CHART CONTAINERS ===== */
    .chart-container {
      position: relative;
      height: 300px;
    }
    
    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
      .dashboard-header {
        padding: 25px;
      }
      
      .dashboard-header h1 {
        font-size: 1.5rem;
      }
      
      .dashboard-header .user-name {
        font-size: 1.8rem;
      }
      
      .stat-card-value {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/inc/menu.php'; ?>
  
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mt-3 mx-auto" role="alert" style="max-width:600px;">
      <i class="fa fa-exclamation-triangle me-2"></i>
      <?= $_SESSION['error'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>
  
  <div class="container-fluid px-4 py-4">
    
    <!-- ===== HEADER BIENVENIDA ===== -->
    <div class="dashboard-header">
      <h1>
        Bienvenido, <br>
        <span class="user-name"><?= htmlspecialchars($nombre . " " . $apellido) ?></span>
      </h1>
      <div class="welcome-date">
        <i class="fa fa-calendar"></i>
        <?= strftime("%A, %d de %B de %Y", time()) ?>
      </div>
    </div>
    
    <!-- ===== STAT CARDS ===== -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card primary">
          <div class="stat-card-icon">
            <i class="fa fa-newspaper"></i>
          </div>
          <div class="stat-card-value"><?= number_format($totalPosts) ?></div>
          <div class="stat-card-label">Total Posts</div>
        </div>
      </div>
      
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card success">
          <div class="stat-card-icon">
            <i class="fa fa-check-circle"></i>
          </div>
          <div class="stat-card-value"><?= number_format($postsPublicados) ?></div>
          <div class="stat-card-label">Publicados</div>
        </div>
      </div>
      
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card warning">
          <div class="stat-card-icon">
            <i class="fa fa-eye"></i>
          </div>
          <div class="stat-card-value"><?= number_format($vistasEsteMes) ?></div>
          <div class="stat-card-label">Vistas este mes</div>
        </div>
      </div>
      
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card info">
          <div class="stat-card-icon">
            <i class="fa fa-users"></i>
          </div>
          <div class="stat-card-value"><?= number_format($totalColumnistas) ?></div>
          <div class="stat-card-label">Columnistas</div>
        </div>
      </div>
    </div>
    
    <!-- ===== GRÁFICOS ===== -->
    <div class="row mb-4">
      <!-- Gráfico: Posts por Mes -->
      <div class="col-xl-8 col-lg-7 mb-4">
        <div class="widget-card">
          <div class="widget-header">
            <h3 class="widget-title">
              <i class="fa fa-bar-chart widget-icon"></i>
              Posts Publicados (Últimos 6 meses)
            </h3>
          </div>
          <div class="chart-container">
            <canvas id="postsChart"></canvas>
          </div>
        </div>
      </div>
      
      <!-- Gráfico: Categorías Populares -->
      <div class="col-xl-4 col-lg-5 mb-4">
        <div class="widget-card">
          <div class="widget-header">
            <h3 class="widget-title">
              <i class="fa fa-pie-chart widget-icon"></i>
              Categorías Populares
            </h3>
          </div>
          <div class="chart-container">
            <canvas id="categoriesChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    
    <!-- ===== CONTENIDO PRINCIPAL ===== -->
    <div class="row">
      
      <!-- Últimos Posts -->
      <div class="col-xl-8 col-lg-7 mb-4">
        <div class="widget-card">
          <div class="widget-header">
            <h3 class="widget-title">
              <i class="fa fa-clock-o widget-icon"></i>
              Últimos Posts Publicados
            </h3>
            <a href="<?= URLBASE ?>/admin/blog/" class="btn btn-sm btn-outline-primary">
              Ver todos
            </a>
          </div>
          
          <div class="table-responsive">
            <table class="posts-table">
              <thead>
                <tr>
                  <th>Título</th>
                  <th>Categoría</th>
                  <th>Vistas</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($ultimosPosts as $post): ?>
                <tr>
                  <td>
                    <a href="<?= URLBASE ?>/<?= $post['category_slug'] ?>/<?= $post['slug'] ?>/" 
                       class="post-title-link" target="_blank">
                      <?= mb_substr(htmlspecialchars($post['title']), 0, 50) ?>...
                    </a>
                  </td>
                  <td>
                    <span class="badge bg-secondary">
                      <?= htmlspecialchars($post['category_name'] ?? 'Sin categoría') ?>
                    </span>
                  </td>
                  <td>
                    <i class="fa fa-eye text-muted"></i>
                    <?= number_format($post['views']) ?>
                  </td>
                  <td>
                    <span class="badge-status <?= $post['status'] === 'published' ? 'badge-published' : 'badge-draft' ?>">
                      <?= $post['status'] === 'published' ? 'Publicado' : 'Borrador' ?>
                    </span>
                  </td>
                  <td><?= date('d/m/Y', strtotime($post['created_at'])) ?></td>
                  <td>
                    <a href="<?= URLBASE ?>/admin/blog/edit.php?id=<?= $post['id'] ?>" 
                       class="btn btn-sm btn-outline-primary">
                      <i class="fa fa-pencil"></i>
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
      <!-- Sidebar Derecho -->
      <div class="col-xl-4 col-lg-5">
        
        <!-- Top Posts -->
        <div class="widget-card mb-4">
          <div class="widget-header">
            <h3 class="widget-title">
              <i class="fa fa-fire widget-icon"></i>
              Posts Más Vistos
            </h3>
          </div>
          
          <?php foreach($topPosts as $index => $top): ?>
          <div class="top-post-item">
            <img src="<?= img_url_dashboard($top['image']) ?>" 
                 alt="<?= htmlspecialchars($top['title']) ?>" 
                 class="top-post-img">
            <div class="top-post-info">
              <div class="top-post-title">
                #<?= $index + 1 ?>. <?= mb_substr(htmlspecialchars($top['title']), 0, 40) ?>...
              </div>
              <div class="top-post-views">
                <i class="fa fa-eye"></i>
                <?= number_format($top['total_views']) ?> vistas
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        
        <!-- Actividad Reciente -->
        <div class="widget-card">
          <div class="widget-header">
            <h3 class="widget-title">
              <i class="fa fa-line-chart widget-icon"></i>
              Actividad Reciente
            </h3>
          </div>
          
          <?php foreach(array_slice($actividadReciente, 0, 5) as $activity): ?>
          <div class="activity-item">
            <div class="activity-icon <?= $activity['status'] === 'published' ? 'published' : 'draft' ?>">
              <i class="fa <?= $activity['status'] === 'published' ? 'fa-check-circle' : 'fa-file-text-o' ?>"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title">
                <?= mb_substr(htmlspecialchars($activity['title']), 0, 40) ?>...
              </div>
              <div class="activity-meta">
                <i class="fa fa-user"></i> <?= htmlspecialchars($activity['autor'] ?? 'Desconocido') ?>
                • <?= date('d/m H:i', strtotime($activity['created_at'])) ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        
      </div>
    </div>
    
  </div>
  
  <?php require_once __DIR__ . '/inc/menu-footer.php'; ?>
  
  <script>
    // ===== CONFIGURACIÓN DE GRÁFICOS =====
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto';
    Chart.defaults.color = '#6c757d';
    
    // ===== GRÁFICO: POSTS POR MES =====
    const postsData = {
      labels: [
        <?php 
        foreach($postsPorMes as $data) {
          $fecha = DateTime::createFromFormat('Y-m', $data['mes']);
          echo "'" . strftime('%B', $fecha->getTimestamp()) . "',";
        }
        ?>
      ],
      datasets: [{
        label: 'Posts Publicados',
        data: [<?php foreach($postsPorMes as $data) echo $data['total'] . ','; ?>],
        backgroundColor: 'rgba(226, 31, 12, 0.1)',
        borderColor: '#E21F0C',
        borderWidth: 3,
        fill: true,
        tension: 0.4
      }]
    };
    
    new Chart(document.getElementById('postsChart'), {
      type: 'line',
      data: postsData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });
    
    // ===== GRÁFICO: CATEGORÍAS POPULARES =====
    const categoriesData = {
      labels: [
        <?php foreach($categoriasPopulares as $cat) 
          echo "'" . addslashes($cat['name']) . "',"; 
        ?>
      ],
      datasets: [{
        data: [<?php foreach($categoriasPopulares as $cat) echo $cat['total_posts'] . ','; ?>],
        backgroundColor: [
          '#E21F0C',
          '#DDC686',
          '#28a745',
          '#17a2b8',
          '#ffc107'
        ],
        borderWidth: 0
      }]
    };
    
    new Chart(document.getElementById('categoriesChart'), {
      type: 'doughnut',
      data: categoriesData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
  </script>
</body>
</html>
