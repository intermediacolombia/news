<?php /* tabs/tab_apariencia.php */ ?>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Configuracion de Apariencia</strong></div>
  <div class="card-body">

    <?php
    $themesDir = __DIR__ . '/../template/';
    $themes    = [];
    if (is_dir($themesDir)) {
        foreach (scandir($themesDir) as $file) {
            if ($file !== '.' && $file !== '..' && is_dir($themesDir . $file)) {
                $themes[] = $file;
            }
        }
    }
    ?>

    <div class="mb-3">
      <label class="form-label fw-semibold">Tema visual</label>
      <select name="site_theme" class="form-select">
        <?php foreach ($themes as $theme): ?>
          <option value="<?= htmlspecialchars($theme) ?>"
            <?= ($configs['site_theme'] ?? '') === $theme ? 'selected' : '' ?>>
            <?= ucfirst($theme) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">Selecciona el tema de diseño que deseas aplicar.</div>
    </div>

    <hr>

    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label fw-semibold">Color primario</label>
        <input type="color" name="primary" class="form-control form-control-color w-100"
               value="<?= htmlspecialchars($configs['primary'], ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Hover Links</label>
        <input type="color" name="color-hover-link" class="form-control form-control-color w-100"
               value="<?= htmlspecialchars($configs['color-hover-link'], ENT_QUOTES, 'UTF-8') ?>">
      </div>
    </div>

  </div>
</div>
