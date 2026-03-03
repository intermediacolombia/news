<?php /* tabs/tab_apariencia.php */ ?>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Configuracion de Apariencia</strong></div>
  <div class="card-body">

    <?php
    // tabs/ → configuraciones/ → admin/ → raiz/ → template/
    $themesDir = __DIR__ . '/../../../template/';
    $themes    = [];

    if (is_dir($themesDir)) {
        foreach (scandir($themesDir) as $file) {
            if ($file === '.' || $file === '..') continue;
            if (!is_dir($themesDir . $file))     continue;
            $themes[] = $file;
        }
    }
    ?>

    <div class="mb-3">
      <label class="form-label fw-semibold">Tema visual</label>

      <?php if (empty($themes)): ?>
        <div class="alert alert-warning py-2">
          No se encontraron temas en <code>/template/</code>.
          Ruta buscada: <code><?= htmlspecialchars(realpath($themesDir) ?: $themesDir) ?></code>
        </div>
      <?php endif; ?>

      <select name="site_theme" class="form-select">
        <?php foreach ($themes as $theme): ?>
          <option value="<?= htmlspecialchars($theme) ?>"
            <?= ($configs['site_theme'] ?? '') === $theme ? 'selected' : '' ?>>
            <?= ucfirst($theme) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">
        Selecciona el tema de diseño que deseas aplicar.
        Tema activo: <strong><?= htmlspecialchars($configs['site_theme'] ?? 'ninguno') ?></strong>
      </div>
    </div>

    <hr>

    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label fw-semibold">Color primario</label>
        <input type="color" name="primary" class="form-control form-control-color w-100"
               value="<?= htmlspecialchars($configs['primary'] ?? '#5fca00', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Hover Links</label>
        <input type="color" name="color-hover-link" class="form-control form-control-color w-100"
               value="<?= htmlspecialchars($configs['color-hover-link'] ?? '#214A82', ENT_QUOTES, 'UTF-8') ?>">
      </div>
    </div>

  </div>
</div>
