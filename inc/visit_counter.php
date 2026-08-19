<?php
/**
 * Contador de visitas basado en archivo JSON local.
 * DB actúa como respaldo: se lee solo si el archivo se reinicia,
 * se escribe solo en múltiplos de 100 visitas o tras restauración.
 */

function _vc_data_file(): string {
    $dir = BASE_PATH . '/inc/_data';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $domain = strtolower(preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? 'default'));
    $domain = preg_replace('/[^a-z0-9\-\.]/', '_', $domain);
    return "$dir/visits_$domain.json";
}

function _vc_is_bot(): bool {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return (bool) preg_match('/bot|crawl|spider|slurp|mediapartners|google|bing|yahoo|baidu/i', $ua);
}

function _vc_client_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) return trim(explode(',', $_SERVER[$k])[0]);
    }
    return '0.0.0.0';
}

function _vc_db_read(): array {
    try {
        $r = db()->query("SELECT value FROM system_settings WHERE setting_name='vc_backup' LIMIT 1")->fetchColumn();
        return $r ? (json_decode($r, true) ?: []) : [];
    } catch (Throwable $e) { return []; }
}

function _vc_db_save(array $data, string $today, string $month): void {
    try {
        $backup = json_encode([
            'total' => $data['total'],
            'today' => $data['days'][$today] ?? 0,
            'today_date' => $today,
            'month' => $data['months'][$month] ?? 0,
            'month_key' => $month,
        ]);
        db()->prepare("INSERT INTO system_settings (setting_name, value) VALUES ('vc_backup', ?)
                       ON DUPLICATE KEY UPDATE value = ?")
            ->execute([$backup, $backup]);
    } catch (Throwable $e) {}
}

function visit_counter_track(): array {
    $file  = _vc_data_file();
    $today = date('Y-m-d');
    $month = date('Y-m');

    $fp = fopen($file, 'c+');
    if (!$fp) {
        $bk = _vc_db_read();
        return [
            'today' => ($bk['today_date'] ?? '') === $today ? (int)($bk['today'] ?? 0) : 0,
            'month' => ($bk['month_key']  ?? '') === $month ? (int)($bk['month'] ?? 0) : 0,
            'total' => (int)($bk['total'] ?? 0),
        ];
    }

    flock($fp, LOCK_EX);
    $data = json_decode(stream_get_contents($fp), true) ?: [];

    $data['total']          = $data['total']          ?? 0;
    $data['months']         = $data['months']         ?? [];
    $data['days']           = $data['days']           ?? [];
    $data['ip_date']        = $data['ip_date']        ?? $today;
    $data['ips']            = $data['ips']            ?? [];
    $data['months'][$month] = $data['months'][$month] ?? 0;
    $data['days'][$today]   = $data['days'][$today]   ?? 0;

    // Restaurar desde DB si el archivo se reinició
    $restored = false;
    if ($data['total'] === 0) {
        $bk = _vc_db_read();
        if (!empty($bk['total'])) {
            $data['total'] = (int)$bk['total'];
            if (($bk['today_date'] ?? '') === $today) $data['days'][$today]   = (int)$bk['today'];
            if (($bk['month_key']  ?? '') === $month) $data['months'][$month] = (int)$bk['month'];
            $restored = true;
        }
    }

    if (!_vc_is_bot() && !isset($_COOKIE['vc_tracked'])) {
        if ($data['ip_date'] !== $today) {
            $data['ips']     = [];
            $data['ip_date'] = $today;
        }

        $ipKey = md5(_vc_client_ip() . $today);
        if (empty($data['ips'][$ipKey])) {
            $data['ips'][$ipKey] = 1;
            $data['total']++;
            $data['months'][$month]++;
            $data['days'][$today]++;
        }

        if (!headers_sent()) setcookie('vc_tracked', '1', time() + 86400, '/');
    }

    // ponytail: mantener solo 90 días y 24 meses de historial
    if (count($data['days']) > 90) {
        ksort($data['days']);
        $data['days'] = array_slice($data['days'], -90, null, true);
    }
    if (count($data['months']) > 24) {
        ksort($data['months']);
        $data['months'] = array_slice($data['months'], -24, null, true);
    }

    fseek($fp, 0);
    ftruncate($fp, 0);
    fwrite($fp, json_encode($data));
    flock($fp, LOCK_UN);
    fclose($fp);

    if ($restored || $data['total'] % 100 === 0) {
        _vc_db_save($data, $today, $month);
    }

    return [
        'today' => $data['days'][$today]   ?? 0,
        'month' => $data['months'][$month] ?? 0,
        'total' => $data['total'],
    ];
}

function visit_counter_widget(): void {
    $s = visit_counter_track();
    ?>
    <section class="py-3">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-3">
          <h6 class="text-uppercase text-secondary fw-semibold mb-3 text-center small">
            <i class="fas fa-eye me-2"></i>Visitas
          </h6>
          <div class="row g-3 align-items-center text-center">
            <div class="col-4">
              <div class="small text-secondary mb-1"><i class="fas fa-calendar-day me-1"></i>Hoy</div>
              <div class="h5 fw-bold text-primary mb-0"><?= number_format($s['today']) ?></div>
            </div>
            <div class="col-4 border-start border-end">
              <div class="small text-secondary mb-1"><i class="fas fa-calendar-alt me-1"></i>Este Mes</div>
              <div class="h5 fw-bold text-success mb-0"><?= number_format($s['month']) ?></div>
            </div>
            <div class="col-4">
              <div class="small text-secondary mb-1"><i class="fas fa-chart-line me-1"></i>Total</div>
              <div class="h5 fw-bold text-danger mb-0"><?= number_format($s['total']) ?></div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php
}
