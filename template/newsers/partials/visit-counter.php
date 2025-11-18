<?php
/**
 * Contador de Visitas (BD EXTERNA)
 * Archivo: visit-counter.php
 *
 * Versión sin page_url, guardando solo:
 * - IP
 * - User Agent
 * - Fecha
 * - Hora
 * - Visita única
 */

/* ============================================
   1. CONEXIÓN A LA BD EXTERNA
   ============================================ */

$vc_host   = 'host.intermediahost.co';
$vc_dbname = 'visit_counter';
$vc_user   = 'visit_counter';
$vc_pass   = 'yF37nShNPHRAEKGL';

try {
    $vc_pdo = new PDO(
        "mysql:host=$vc_host;port=3306;dbname=$vc_dbname;charset=utf8mb4",
        $vc_user,
        $vc_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    error_log("Error conexión VisitCounter: " . $e->getMessage());
    return; // Evita romper páginas
}


/* ============================================
   2. TABLA POR DOMINIO
   ============================================ */

/* ============================================
   2. TABLA POR DOMINIO (NORMALIZADO)
   ============================================ */

$user_url = $_SERVER['HTTP_HOST'] ?? 'default_site';

// Normalizar dominio
$user_url = strtolower($user_url);             // todo en minúsculas
$user_url = preg_replace('/^www\./', '', $user_url); // quitar www.


/* ============================================
   3. CLASE CONTADOR
   ============================================ */

class VisitCounter {

    private $pdo;
    private $table;

    public function __construct(PDO $pdo, string $tableName) {
        $this->pdo = $pdo;

        // Nombre de tabla seguro
        $this->table = preg_replace('/[^a-zA-Z0-9_]/', '_', $tableName);

        $this->initTable();
    }

    /**
     * Crear tabla dinámica sin columna URL
     */
    private function initTable() {
        $table = $this->table;

        $sql = "CREATE TABLE IF NOT EXISTS `$table` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_hash VARCHAR(45) NOT NULL,
            user_agent TEXT,
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
            error_log("VisitCounter error creando tabla `$table`: " . $e->getMessage());
        }
    }

    /**
     * Detectar IP real del cliente
     */
    private function getClientIP() {
        $keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return '0.0.0.0';
    }

    /**
     * Ignorar bots de Google
     */
    private function isGoogleBot() {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $bots = [
            'Googlebot',
            'AdsBot-Google',
            'Mediapartners-Google',
            'Google-InspectionTool'
        ];

        foreach ($bots as $bot) {
            if (stripos($ua, $bot) !== false) return true;
        }
        return false;
    }

    /**
     * Registrar visita única por día
     */
    public function track() {
        if ($this->isGoogleBot() || isset($_COOKIE['visitor_tracked'])) {
            return false;
        }

        $table = $this->table;

        $ip   = $this->getClientIP();
        $ua   = $_SERVER['HTTP_USER_AGENT'] ?? '';

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO `$table` (ip_hash, user_agent, visit_date, visit_time)
                VALUES (?, ?, CURDATE(), NOW())
                ON DUPLICATE KEY UPDATE visit_time = NOW(), is_unique = 0
            ");
            $stmt->execute([$ip, $ua]);

            // Evita doble registro el mismo día
            setcookie('visitor_tracked', '1', time() + 86400, '/');
            return true;

        } catch (PDOException $e) {
            error_log("VisitCounter error tracking: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas resumidas
     */
    public function getStats() {
        $table = $this->table;

        try {
            $today = $this->pdo->query("
                SELECT COUNT(*) FROM `$table` WHERE visit_date = CURDATE()
            ")->fetchColumn();

            $month = $this->pdo->query("
                SELECT COUNT(*) FROM `$table`
                WHERE MONTH(visit_date)=MONTH(CURDATE())
                AND YEAR(visit_date)=YEAR(CURDATE())
            ")->fetchColumn();

            $total = $this->pdo->query("
                SELECT COUNT(*) FROM `$table`
            ")->fetchColumn();

            return compact('today','month','total');

        } catch (PDOException $e) {
            error_log("VisitCounter error stats: " . $e->getMessage());
            return ['today'=>0,'month'=>0,'total'=>0];
        }
    }
}


/* ============================================
   4. INICIALIZAR Y REGISTRAR VISITA
   ============================================ */

$vc = new VisitCounter($vc_pdo, $user_url);
$vc->track();
$stats = $vc->getStats();

?>


<!-- WIDGET DE ESTADÍSTICAS DE VISITAS (COMPACTO) -->
<section class="py-3">
  
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-body p-3">
	<!-- TÍTULO -->
        <h6 class="text-uppercase text-secondary fw-semibold mb-3 text-center small">
          <i class="fas fa-eye me-2"></i>Visitas
        </h6>
		  
        <div class="row g-3 align-items-center text-center">
          <!-- HOY -->
          <div class="col-4">
            <div class="small text-secondary mb-1">
              <i class="fas fa-calendar-day me-1"></i>Hoy
            </div>
            <div class="h5 fw-bold text-primary mb-0"><?= number_format($stats['today']) ?></div>
          </div>
          
          <!-- ESTE MES -->
          <div class="col-4 border-start border-end">
            <div class="small text-secondary mb-1">
              <i class="fas fa-calendar-alt me-1"></i>Este Mes
            </div>
            <div class="h5 fw-bold text-success mb-0"><?= number_format($stats['month']) ?></div>
          </div>
          
          <!-- TOTAL -->
          <div class="col-4">
            <div class="small text-secondary mb-1">
              <i class="fas fa-chart-line me-1"></i>Total
            </div>
            <div class="h5 fw-bold text-danger mb-0"><?= number_format($stats['total']) ?></div>
          </div>
        </div>
      </div>
    </div>

</section>

