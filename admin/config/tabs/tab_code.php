<?php /* tabs/tab_code.php */ ?>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Codigo Personalizado</strong></div>
  <div class="card-body">

    <div class="mb-3">
      <label class="form-label"><code>Codigo en &lt;head&gt;&lt;/head&gt;</code></label>
      <textarea name="code_head" class="form-control" rows="4"><?= htmlspecialchars($configs['code_head'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label"><code>Codigo antes de cerrar &lt;/body&gt;</code></label>
      <textarea name="code_footer" class="form-control" rows="4"><?= htmlspecialchars($configs['code_footer'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label"><code>Codigo HTML en Slider Bar</code></label>
      <textarea name="code_sliderbar" class="form-control" rows="4"><?= htmlspecialchars($configs['code_sliderbar'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">URL Reproductor Horizontal</label>
      <input type="text" name="code_player" class="form-control"
             value="<?= htmlspecialchars($configs['code_player'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="enable_stop_player"
               name="enable_stop_player" value="1"
               <?= !empty($configs['enable_stop_player']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="enable_stop_player">
          Habilitar reproduccion continua (no detiene el audio al cambiar de pagina)
        </label>
      </div>
      <small class="text-muted">Puede causar fallos en algunos temas. Usar con precaucion.</small>
    </div>

    <div class="mb-3">
      <label class="form-label">Alto del Reproductor (px)</label>
      <input type="number" name="player_height" class="form-control"
             value="<?= htmlspecialchars($configs['player_height'], ENT_QUOTES, 'UTF-8') ?>"
             placeholder="Ej: 90">
    </div>

  </div>
</div>