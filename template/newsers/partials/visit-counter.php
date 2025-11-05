<?php
/**
 * Widget Contador de Visitas
 * Archivo: widgets/visit-counter.php
 */
require_once __DIR__ . '/../../../inc/config.php';

// ============================================
// CLASE CONTADOR DE VISITAS
// ============================================
class VisitCounter {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->initTable();
    }
    
    private function initTable() {
        $sql = "CREATE TABLE IF NOT EXISTS visit_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_hash VARCHAR(64) NOT NULL,
            user_agent TEXT,
            page_url VARCHAR(255),
            visit_date DATE NOT NULL,
            visit_time DATETIME NOT NULL,
            is_unique TINYINT(1) DEFAULT 1,
            INDEX idx_date (visit_date),
            INDEX idx_ip (ip_hash),
            UNIQUE KEY unique_visit (ip_hash, visit_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creando tabla visit_stats: " . $e->getMessage());
        }
    }
    
    private function getClientIP() {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                return $ip;
            }
        }
        return '0.0.0.0';
    }
    
    private function isGoogleBot() {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $bots = ['Googlebot', 'AdsBot-Google', 'Mediapartners-Google', 'Google-InspectionTool'];
        foreach ($bots as $bot) {
            if (stripos($ua, $bot) !== false) return true;
        }
        return false;
    }
    
    public function track() {
        if ($this->isGoogleBot() || isset($_COOKIE['visitor_tracked'])) {
            return false;
        }
        
        $ip = $this->getClientIP();
        $hash = hash('sha256', $ip . date('Y-m-d'));
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO visit_stats (ip_hash, user_agent, page_url, visit_date, visit_time) 
                VALUES (?, ?, ?, CURDATE(), NOW())
                ON DUPLICATE KEY UPDATE visit_time = NOW(), is_unique = 0
            ");
            $stmt->execute([$hash, $ua, $url]);
            setcookie('visitor_tracked', '1', time() + 86400, '/');
            return true;
        } catch (PDOException $e) {
            error_log("Error tracking visit: " . $e->getMessage());
            return false;
        }
    }
    
    public function getStats() {
        try {
            $today = $this->pdo->query("
                SELECT COUNT(*) FROM visit_stats 
                WHERE visit_date = CURDATE() AND is_unique = 1
            ")->fetchColumn();
            
            $month = $this->pdo->query("
                SELECT COUNT(*) FROM visit_stats 
                WHERE MONTH(visit_date) = MONTH(CURDATE()) 
                AND YEAR(visit_date) = YEAR(CURDATE())
                AND is_unique = 1
            ")->fetchColumn();
            
            $total = $this->pdo->query("
                SELECT COUNT(*) FROM visit_stats WHERE is_unique = 1
            ")->fetchColumn();
            
            $last7days = $this->pdo->query("
                SELECT visit_date, COUNT(*) as visits 
                FROM visit_stats 
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND is_unique = 1
                GROUP BY visit_date 
                ORDER BY visit_date DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            return compact('today', 'month', 'total', 'last7days');
        } catch (PDOException $e) {
            error_log("Error obteniendo stats: " . $e->getMessage());
            return ['today' => 0, 'month' => 0, 'total' => 0, 'last7days' => []];
        }
    }
}

// ============================================
// INICIALIZAR Y TRACKEAR
// ============================================
$visitCounter = new VisitCounter($pdo);
$visitCounter->track();
$stats = $visitCounter->getStats();
?>

<!-- WIDGET DE ESTADÍSTICAS DE VISITAS -->
<section class="py-4">
  <div class="container">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <!-- ENCABEZADO -->
      <div class="card-header bg-gradient text-white py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <h5 class="mb-0 fw-bold text-center">
          <i class="bi bi-graph-up-arrow me-2"></i>Estadísticas de Visitas
        </h5>
      </div>
      
      <!-- ESTADÍSTICAS PRINCIPALES -->
      <div class="card-body p-4">
        <div class="row g-3 mb-4">
          <!-- HOY -->
          <div class="col-12 col-md-4">
            <div class="stat-card text-center p-3 rounded-3 bg-light hover-lift">
              <div class="stat-icon mb-2">
                <i class="bi bi-calendar-check fs-2 text-primary"></i>
              </div>
              <h6 class="text-uppercase text-secondary fw-semibold small mb-1">Hoy</h6>
              <h2 class="fw-bold text-primary mb-0"><?= number_format($stats['today']) ?></h2>
            </div>
          </div>
          
          <!-- ESTE MES -->
          <div class="col-12 col-md-4">
            <div class="stat-card text-center p-3 rounded-3 bg-light hover-lift">
              <div class="stat-icon mb-2">
                <i class="bi bi-calendar-month fs-2 text-success"></i>
              </div>
              <h6 class="text-uppercase text-secondary fw-semibold small mb-1">Este Mes</h6>
              <h2 class="fw-bold text-success mb-0"><?= number_format($stats['month']) ?></h2>
            </div>
          </div>
          
          <!-- TOTAL -->
          <div class="col-12 col-md-4">
            <div class="stat-card text-center p-3 rounded-3 bg-light hover-lift">
              <div class="stat-icon mb-2">
                <i class="bi bi-globe fs-2 text-danger"></i>
              </div>
              <h6 class="text-uppercase text-secondary fw-semibold small mb-1">Total</h6>
              <h2 class="fw-bold text-danger mb-0"><?= number_format($stats['total']) ?></h2>
            </div>
          </div>
        </div>
        
        <!-- ÚLTIMOS 7 DÍAS -->
        <?php if (!empty($stats['last7days'])): ?>
        <div class="mt-4 pt-3 border-top">
          <h6 class="text-uppercase text-secondary fw-semibold mb-3 small">
            <i class="bi bi-bar-chart-line me-2"></i>Últimos 7 Días
          </h6>
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead class="table-light">
                <tr>
                  <th class="fw-semibold">Fecha</th>
                  <th class="fw-semibold text-end">Visitas</th>
                  <th class="fw-semibold">Barra</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $max = max(array_column($stats['last7days'], 'visits'));
                foreach ($stats['last7days'] as $day): 
                  $percent = $max > 0 ? ($day['visits'] / $max) * 100 : 0;
                ?>
                <tr>
                  <td class="small fw-medium"><?= date('d M', strtotime($day['visit_date'])) ?></td>
                  <td class="text-end fw-bold"><?= number_format($day['visits']) ?></td>
                  <td style="width: 60%;">
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar bg-gradient" 
                           style="width: <?= $percent ?>%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);" 
                           role="progressbar"></div>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php endif; ?>
        
        <!-- INFO ADICIONAL -->
        <div class="mt-3 pt-3 border-top">
          <p class="small text-muted mb-0 text-center">
            <i class="bi bi-shield-check me-1"></i>
            Visitas únicas por IP • Excluye bots de Google
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ESTILOS DEL WIDGET -->
<style>
.hover-lift {
  transition: transform .3s ease, box-shadow .3s ease;
}
.hover-lift:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.stat-card {
  border: 1px solid rgba(0,0,0,0.05);
}
.bg-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>

