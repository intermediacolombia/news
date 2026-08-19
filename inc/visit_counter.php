<?php
/**
 * Contador de visitas basado en archivo JSON local.
 * Sin BD externa — persiste en disco, no se reinicia solo.
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

/** Lee el total guardado en DB como respaldo ante pérdida del archivo JSON. */
function _vc_db_total(): int {
    try {
        $r = db()->query("SELECT value FROM system_settings WHERE setting_name='vc_total' LIMIT 1")->fetchColumn();
        return $r !== false ? (int)$r : 0;
    } catch (Throwable $e) { return 0; }
}

function _vc_db_save_total(int $total): void {
    try {
        db()->prepare("INSERT INTO system_settings (setting_name, value) VALUES ('vc_total', ?)
                       ON DUPLICATE KEY UPDATE value = GREATEST(value, ?)")
            ->execute([$total, $total]);
    } catch (Throwable $e) {}
}

function visit_counter_track(): array {
    $file  = _vc_data_file();
    $today = date('Y-m-d');
    $month = date('Y-m');

    $fp = fopen($file, 'c+');
    if (!$fp) return ['today' => 0, 'month' => 0, 'total' => _vc_db_total()];

    flock($fp, LOCK_EX);
    $data = json_decode(stream_get_contents($fp), true) ?: [];

    $data['total']          = $data['total']          ?? 0;
    $data['months']         = $data['months']         ?? [];
    $data['days']           = $data['days']           ?? [];
    $data['ip_date']        = $data['ip_date']        ?? $today;
    $data['ips']            = $data['ips']            ?? [];
    $data['months'][$month] = $data['months'][$month] ?? 0;
    $data['days'][$today]   = $data['days'][$today]   ?? 0;

    // Si el archivo se reinició (total = 0), restaurar desde DB una sola vez
    $restored = false;
    if ($data['total'] === 0) {
        $dbTotal = _vc_db_total();
        if ($dbTotal > 0) { $data['total'] = $dbTotal; $restored = true; }
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

    // Solo escribe a DB en múltiplos de 100 visitas o cuando se acaba de restaurar
    if ($restored || $data['total'] % 100 === 0) {
        _vc_db_save_total($data['total']);
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
