<?php /* tabs/tab_identidad.php */ ?>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Quienes Somos?</strong></div>
  <div class="card-body">
    <textarea name="about_us" class="form-control summernote" rows="3"><?= htmlspecialchars($configs['about_us'], ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Terminos y Condiciones</strong></div>
  <div class="card-body">
    <textarea name="terms-and-conditions" class="form-control summernote" rows="3"><?= htmlspecialchars($configs['terms-and-conditions'], ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Politica de Privacidad</strong></div>
  <div class="card-body">
    <textarea name="privacy-policy" class="form-control summernote" rows="3"><?= htmlspecialchars($configs['privacy-policy'], ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Politica de Devoluciones</strong></div>
  <div class="card-body">
    <textarea name="return-policy" class="form-control summernote" rows="3"><?= htmlspecialchars($configs['return-policy'], ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
</div>