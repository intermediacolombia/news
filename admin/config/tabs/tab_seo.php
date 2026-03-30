<?php /* tabs/tab_seo.php */ ?>

<div class="tab-pane fade" id="seo">

  <!-- SEO Basico -->
  <div class="card mb-3">
    <div class="card-header bg-light"><strong><i class="fa fa-google me-2"></i>SEO Pagina Principal</strong></div>
    <div class="card-body">

      <div class="mb-3">
        <label class="form-label">SEO Title</label>
        <input type="text" name="seo_home_title" id="seo_home_title" maxlength="180" class="form-control"
               value="<?= htmlspecialchars($configs['seo_home_title'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text">Max 60-70 caracteres. <span id="seo_home_title_counter" class="badge bg-secondary">0</span></div>
      </div>

      <div class="mb-3">
        <label class="form-label">SEO Description</label>
        <textarea name="seo_home_description" id="seo_home_description" maxlength="300" rows="2" class="form-control"><?= htmlspecialchars($configs['seo_home_description'], ENT_QUOTES, 'UTF-8') ?></textarea>
        <div class="form-text">Max 160 caracteres. <span id="seo_home_description_counter" class="badge bg-secondary">0</span></div>
      </div>

      <div class="mb-3">
        <label class="form-label">SEO Keywords</label>
        <input type="text" name="seo_home_keywords" id="seo_home_keywords" maxlength="300" class="form-control"
               value="<?= htmlspecialchars($configs['seo_home_keywords'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text">Separa por comas. <span id="seo_home_keywords_counter" class="badge bg-secondary">0</span></div>
      </div>

    </div>
  </div>

  <!-- Google AdSense -->
  <div class="card mb-3">
    <div class="card-header bg-light d-flex align-items-center justify-content-between">
      <strong><i class="fa fa-rectangle-ad me-2"></i>Google AdSense (Monetizacion)</strong>
      <span class="badge bg-success">Monetizacion</span>
    </div>
    <div class="card-body">

      <div class="alert alert-info py-2 mb-3">
        <i class="fa fa-circle-info me-2"></i>
        El <strong>Publisher ID</strong> activa AdSense en todo el sitio. Los bloques individuales
        se gestionan desde el modulo de <strong>Publicidad</strong>.
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold"><i class="fa fa-google me-1 text-primary"></i> Publisher ID</label>
        <input type="text" name="adsense_publisher_id" class="form-control font-monospace"
               placeholder="ca-pub-XXXXXXXXXXXXXXXX"
               value="<?= htmlspecialchars($configs['adsense_publisher_id'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text">En <a href="https://adsense.google.com" target="_blank">Google AdSense</a> → Cuenta → Informacion de cuenta.</div>
      </div>

      <div class="mb-3">
  <div class="form-check form-switch">

   
    <input type="hidden" name="adsense_auto_ads" value="0">

    <input class="form-check-input" type="checkbox" id="adsense_auto_ads"
           name="adsense_auto_ads" value="1"
           <?= !empty($configs['adsense_auto_ads']) && $configs['adsense_auto_ads'] == '1' ? 'checked' : '' ?>>
    <label class="form-check-label" for="adsense_auto_ads">
      <i class="fa fa-wand-magic-sparkles me-1"></i>
      Habilitar <strong>Auto Ads</strong> (Google coloca anuncios automaticamente)
    </label>
  </div>
  <div class="form-text">Desactiva si prefieres control manual de posiciones desde Publicidad.</div>
</div>

      <?php if (!empty($configs['adsense_publisher_id'])): ?>
      <div class="mb-2">
        <label class="form-label text-muted small">Script generado para &lt;head&gt;:</label>
        <pre class="bg-light border rounded p-2" style="font-size:11px; overflow-x:auto;"><code>&lt;script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= htmlspecialchars($configs['adsense_publisher_id'], ENT_QUOTES, 'UTF-8') ?>" crossorigin="anonymous"&gt;&lt;/script&gt;</code></pre>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Verificacion de Buscadores -->
  <div class="card mb-3">
    <div class="card-header bg-light"><strong><i class="fa fa-magnifying-glass me-2"></i>Verificacion de Buscadores</strong></div>
    <div class="card-body">
      <p class="text-muted small mb-3">Pega solo el valor del atributo <code>content</code>, no el tag completo.</p>

      <div class="mb-3">
        <label class="form-label fw-semibold"><i class="fa fa-google me-1" style="color:#4285F4"></i> Google Search Console</label>
        <input type="text" name="verify_google" class="form-control font-monospace"
               placeholder="Codigo de verificacion..."
               value="<?= htmlspecialchars($configs['verify_google'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text"><a href="https://search.google.com/search-console" target="_blank">Search Console</a> → Agregar propiedad → Etiqueta HTML → valor del atributo content.</div>
      </div>

      <div class="alert alert-success py-2 mb-0 mt-1 d-flex align-items-center gap-2">
        <i class="fa fa-sitemap"></i>
        <span>
          <strong>Sitemap:</strong> La URL que debes enviar a Google Search Console es
          <code><?= rtrim(URLBASE ?? '', '/') ?>/sitemap.php</code>
          — en Search Console ve a <strong>Sitemaps</strong> y pega esa URL.
        </span>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold"><i class="fa fa-microsoft me-1" style="color:#00A4EF"></i> Bing Webmaster Tools</label>
        <input type="text" name="verify_bing" class="form-control font-monospace"
               placeholder="Codigo de verificacion..."
               value="<?= htmlspecialchars($configs['verify_bing'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text"><a href="https://www.bing.com/webmasters" target="_blank">Bing Webmaster</a> → Agregar sitio → Etiqueta meta → valor content.</div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold"><i class="fa fa-y me-1" style="color:#FF0000"></i> Yandex Webmaster</label>
        <input type="text" name="verify_yandex" class="form-control font-monospace"
               placeholder="Codigo de verificacion..."
               value="<?= htmlspecialchars($configs['verify_yandex'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text"><a href="https://webmaster.yandex.com" target="_blank">Yandex Webmaster</a> → Agregar sitio → Etiqueta meta.</div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold"><i class="fa fa-meta me-1" style="color:#0866FF"></i> Meta (Facebook Domain Verification)</label>
        <input type="text" name="verify_meta" class="form-control font-monospace"
               placeholder="Codigo de verificacion..."
               value="<?= htmlspecialchars($configs['verify_meta'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text"><a href="https://business.facebook.com/settings/owned-domains" target="_blank">Meta Business</a> → Configuracion → Dominios.</div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold"><i class="fa fa-pinterest me-1" style="color:#E60023"></i> Pinterest</label>
        <input type="text" name="verify_pinterest" class="form-control font-monospace"
               placeholder="Codigo de verificacion..."
               value="<?= htmlspecialchars($configs['verify_pinterest'], ENT_QUOTES, 'UTF-8') ?>">
      </div>

    </div>
  </div>

  <!-- Google Tag Manager -->
  <div class="card mb-3">
    <div class="card-header bg-light"><strong><i class="fa fa-tags me-2"></i>Google Tag Manager</strong></div>
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label fw-semibold">GTM Container ID</label>
        <input type="text" name="gtm_container_id" class="form-control font-monospace"
               placeholder="GTM-XXXXXXX"
               value="<?= htmlspecialchars($configs['gtm_container_id'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text">Deja vacio si no usas GTM. El snippet se inserta automaticamente en head y body.</div>
      </div>
    </div>
  </div>

</div><!-- /tab-pane #seo -->

<script>
(function () {
  function updateCounter(inputId, counterId, min, max) {
    const input   = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (!input || !counter) return;
    if (input.value.length > max) input.value = input.value.substring(0, max);
    const len = input.value.length;
    counter.textContent = len;
    if (len === 0)      counter.className = 'badge bg-danger';
    else if (len < min) counter.className = 'badge bg-warning text-dark';
    else if (len > max) counter.className = 'badge bg-danger';
    else                counter.className = 'badge bg-success';
  }
  document.addEventListener('DOMContentLoaded', function () {
    const fields = [
      { id: 'seo_home_title',       min: 50,  max: 70  },
      { id: 'seo_home_description', min: 120, max: 160 },
      { id: 'seo_home_keywords',    min: 5,   max: 250 },
    ];
    fields.forEach(f => {
      updateCounter(f.id, f.id + '_counter', f.min, f.max);
      document.getElementById(f.id)?.addEventListener('input', () =>
        updateCounter(f.id, f.id + '_counter', f.min, f.max));
    });
  });
})();
</script>