<?php /* tabs/tab_generales.php */ ?>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Datos Generales</strong></div>
  <div class="card-body">

    <div class="mb-3">
      <label class="form-label">Nombre de la tienda / sitio</label>
      <input type="text" name="site_name" class="form-control"
             value="<?= htmlspecialchars($configs['site_name'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Logo</label><br>
      <?php if (!empty($configs['site_logo'])): ?>
        <img src="<?= htmlspecialchars($configs['site_logo'], ENT_QUOTES, 'UTF-8') ?>"
             alt="Logo" style="max-height:60px; margin-bottom:8px; display:block;">
      <?php endif; ?>
      <input type="file" name="site_logo" class="form-control" accept=".png,.jpg,.jpeg,.webp,.gif">
    </div>

    <div class="mb-3">
      <label class="form-label">Favicon</label><br>
      <?php if (!empty($configs['site_favicon'])): ?>
        <img src="<?= htmlspecialchars($configs['site_favicon'], ENT_QUOTES, 'UTF-8') ?>"
             alt="Favicon" style="max-height:32px; margin-bottom:8px; display:block;">
      <?php endif; ?>
      <input type="file" name="site_favicon" class="form-control" accept=".png,.jpg,.jpeg,.webp,.gif,.ico">
    </div>

    <div class="mb-3">
      <label class="form-label">Banner Inferior</label><br>
      <?php if (!empty($configs['banner_inferior'])): ?>
        <div class="d-flex align-items-center gap-3 mb-2">
          <img src="<?= htmlspecialchars($configs['banner_inferior'], ENT_QUOTES, 'UTF-8') ?>"
               alt="Banner Inferior" style="max-height:100px; border:1px solid #ccc; border-radius:6px;">
          <button type="button" class="btn btn-sm btn-danger" id="deleteBannerBtn">
            <i class="fa fa-trash"></i> Eliminar
          </button>
        </div>
      <?php endif; ?>
      <input type="file" name="banner_inferior" class="form-control" accept=".png,.jpg,.jpeg,.webp,.gif">
    </div>

    <div class="mb-3">
      <label class="form-label">Correo de contacto</label>
      <input type="email" name="site_email" class="form-control"
             value="<?= htmlspecialchars($configs['site_email'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Direccion de la Tienda</label>
      <input type="text" name="business_address" class="form-control"
             value="<?= htmlspecialchars($configs['business_address'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Telefono de la Tienda</label>
      <input type="tel" name="business_phone" class="form-control"
             value="<?= htmlspecialchars($configs['business_phone'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Info Footer</label>
      <textarea name="info_footer" class="form-control"><?= htmlspecialchars($configs['info_footer'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="mb-3">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="enable_text_to_speech"
               name="enable_text_to_speech" value="1"
               <?= !empty($configs['enable_text_to_speech']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="enable_text_to_speech">
          <i class="fas fa-volume-up me-2"></i>Habilitar Text-to-Speech
        </label>
      </div>
    </div>

  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Redes Sociales</strong></div>
  <div class="card-body">

    <div class="mb-3">
      <label class="form-label"><i class="fab fa-facebook text-primary me-1"></i> Facebook</label>
      <input type="text" name="facebook" class="form-control"
             value="<?= htmlspecialchars($configs['facebook'], ENT_QUOTES, 'UTF-8') ?>"
             placeholder="https://facebook.com/tu-pagina">
    </div>

    <div class="mb-3">
      <label class="form-label"><i class="fab fa-instagram text-danger me-1"></i> Instagram</label>
      <input type="text" name="instagram" class="form-control"
             value="<?= htmlspecialchars($configs['instagram'], ENT_QUOTES, 'UTF-8') ?>"
             placeholder="https://instagram.com/tu-perfil">
    </div>

    <div class="mb-3">
      <label class="form-label"><i class="fab fa-youtube text-danger me-1"></i> YouTube</label>
      <input type="text" name="youtube" class="form-control"
             value="<?= htmlspecialchars($configs['youtube'], ENT_QUOTES, 'UTF-8') ?>"
             placeholder="https://youtube.com/tu-canal">
    </div>

    <div class="mb-3">
      <label class="form-label"><i class="fab fa-tiktok text-dark me-1"></i> TikTok</label>
      <input type="text" name="tiktok" class="form-control"
             value="<?= htmlspecialchars($configs['tiktok'], ENT_QUOTES, 'UTF-8') ?>"
             placeholder="https://tiktok.com/@tuusuario">
    </div>

    <div class="mb-3">
      <label class="form-label"><i class="fab fa-whatsapp text-success me-1"></i> WhatsApp</label>
      <input type="text" name="whatsapp" class="form-control"
             value="<?= htmlspecialchars($configs['whatsapp'], ENT_QUOTES, 'UTF-8') ?>"
             placeholder="3001234567 (solo numero)">
    </div>

    <div class="mb-3">
      <label class="form-label"><i class="fab fa-x-twitter text-dark me-1"></i> X (Twitter)</label>
      <input type="text" name="twitter" class="form-control"
             value="<?= htmlspecialchars($configs['twitter'], ENT_QUOTES, 'UTF-8') ?>"
             placeholder="https://x.com/tuusuario">
    </div>

    <div class="mb-3">
      <label class="form-label"><i class="fa-solid fa-hashtag me-1"></i> Hash Tag</label>
      <input type="text" name="hashtag" class="form-control"
             value="<?= htmlspecialchars($configs['hashtag'], ENT_QUOTES, 'UTF-8') ?>"
             placeholder="#TuSitio">
    </div>

  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Mapa Google Maps</strong></div>
  <div class="card-body">
    <div class="mb-3">
      <label class="form-label"><i class="fa-solid fa-code me-1"></i> URL Embed</label>
      <input type="text" name="business_map" class="form-control"
             value="<?= htmlspecialchars($configs['business_map'], ENT_QUOTES, 'UTF-8') ?>"
             placeholder="https://www.google.com/maps/embed?pb=...">
      <small class="text-muted">Solo la URL del embed, sin el tag iframe.</small>
    </div>
  </div>
</div>