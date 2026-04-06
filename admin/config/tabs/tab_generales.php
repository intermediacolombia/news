<?php /* tabs/tab_generales.php */ ?>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Datos Generales</strong></div>
  <div class="card-body">

    <div class="mb-3">
      <label class="form-label">Nombre de la tienda / sitio</label>
      <input type="text" name="site_name" class="form-control"
             value="<?= htmlspecialchars($configs['site_name'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <!-- ══ LOGO ══ -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Logo</label>
      <div class="d-flex align-items-center gap-3 mb-2" id="logo-preview-wrap"
           style="<?= empty($configs['site_logo']) ? 'display:none!important' : '' ?>">
        <img id="logo-preview-img"
             src="<?= htmlspecialchars($configs['site_logo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
             alt="Logo actual"
             style="max-height:60px; max-width:200px; border:1px solid #dee2e6; border-radius:6px; padding:4px; background:#fff;">
        <button type="button" class="btn btn-sm btn-outline-danger" id="logo-remove-btn">
          <i class="fa fa-times me-1"></i> Quitar
        </button>
      </div>
      <input type="hidden" name="site_logo" id="site_logo_path"
             value="<?= htmlspecialchars($configs['site_logo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <button type="button" class="btn btn-outline-primary btn-sm" id="logo-open-media">
        <i class="bi bi-images me-1"></i> Seleccionar desde biblioteca
      </button>
      <div class="form-text">PNG, JPG, WebP recomendado. Se guarda la ruta relativa.</div>
    </div>

    <!-- ══ FAVICON ══ -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Favicon</label>
      <div class="d-flex align-items-center gap-3 mb-2" id="favicon-preview-wrap"
           style="<?= empty($configs['site_favicon']) ? 'display:none!important' : '' ?>">
        <img id="favicon-preview-img"
             src="<?= htmlspecialchars($configs['site_favicon'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
             alt="Favicon actual"
             style="width:32px; height:32px; object-fit:contain; border:1px solid #dee2e6; border-radius:4px; background:#fff;">
        <button type="button" class="btn btn-sm btn-outline-danger" id="favicon-remove-btn">
          <i class="fa fa-times me-1"></i> Quitar
        </button>
      </div>
      <input type="hidden" name="site_favicon" id="site_favicon_path"
             value="<?= htmlspecialchars($configs['site_favicon'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <button type="button" class="btn btn-outline-primary btn-sm" id="favicon-open-media">
        <i class="bi bi-images me-1"></i> Seleccionar desde biblioteca
      </button>
      <div class="form-text">ICO, PNG 32×32 recomendado.</div>
    </div>

    <!-- ══ BANNER INFERIOR ══ -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Banner Inferior</label>
      <div class="d-flex align-items-center gap-3 mb-2" id="banner-preview-wrap"
           style="<?= empty($configs['banner_inferior']) ? 'display:none!important' : '' ?>">
        <img id="banner-preview-img"
             src="<?= htmlspecialchars($configs['banner_inferior'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
             alt="Banner"
             style="max-height:80px; border:1px solid #dee2e6; border-radius:6px;">
        <button type="button" class="btn btn-sm btn-outline-danger" id="banner-remove-btn">
          <i class="fa fa-times me-1"></i> Quitar
        </button>
      </div>
      <input type="hidden" name="banner_inferior" id="banner_inferior_path"
             value="<?= htmlspecialchars($configs['banner_inferior'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <button type="button" class="btn btn-outline-primary btn-sm" id="banner-open-media">
        <i class="bi bi-images me-1"></i> Seleccionar desde biblioteca
      </button>
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
      <label class="form-label"><i class="fas fa-language me-2"></i>Idioma del Administrador</label>
      <select name="admin_language" class="form-select">
        <option value="es" <?= ($configs['admin_language'] ?? 'es') === 'es' ? 'selected' : '' ?>>Español</option>
        <option value="en" <?= ($configs['admin_language'] ?? 'es') === 'en' ? 'selected' : '' ?>>English</option>
      </select>
      <small class="text-muted">Idioma de la interfaz del panel de administración</small>
    </div>

    <div class="mb-3">
      <div class="form-check form-switch">
        <input type="hidden" name="enable_text_to_speech" value="0">
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

<!-- Redes Sociales y Maps sin cambios -->
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
      <label class="form-label"><i class="fa fa-hashtag me-1"></i> Hash Tag</label>
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
      <label class="form-label"><i class="fa fa-code me-1"></i> URL Embed</label>
      <input type="text" name="business_map" class="form-control"
             value="<?= htmlspecialchars($configs['business_map'], ENT_QUOTES, 'UTF-8') ?>"
             placeholder="https://www.google.com/maps/embed?pb=...">
      <small class="text-muted">Solo la URL del embed, sin el tag iframe.</small>
    </div>
  </div>
</div>

<!-- ══ Modal Biblioteca de Medios (reutilizable para logo/favicon/banner) ══ -->
<div class="modal fade" id="modalMediaPickerConfig" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#214A82,#4972AA);color:#fff;">
        <h5 class="modal-title"><i class="bi bi-images me-2"></i><span id="mpc-title">Biblioteca de medios</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">

        <ul class="nav nav-tabs px-3 pt-2" id="mpcTabs">
          <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#mpc-tab-library">
              <i class="bi bi-grid me-1"></i> Biblioteca
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#mpc-tab-upload">
              <i class="bi bi-cloud-upload me-1"></i> Subir nueva
            </a>
          </li>
        </ul>

        <div class="tab-content">

          <!-- Biblioteca -->
          <div class="tab-pane fade show active p-3" id="mpc-tab-library">
            <div class="row g-2 mb-3 align-items-end">
              <div class="col-sm-5">
                <input type="text" id="mpc-search" class="form-control form-control-sm"
                       placeholder="Buscar por nombre...">
              </div>
              <div class="col-sm-2">
                <button type="button" class="btn btn-sm btn-primary w-100" id="mpc-btn-search">
                  <i class="bi bi-search me-1"></i> Buscar
                </button>
              </div>
              <div class="col-sm-3 text-muted small" id="mpc-total"></div>
            </div>

            <div class="row g-2" id="mpc-grid" style="min-height:250px;">
              <div class="col-12 text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm me-2"></div> Cargando...
              </div>
            </div>
            <div id="mpc-pagination" class="d-flex justify-content-center gap-2 mt-3 flex-wrap"></div>
          </div>

          <!-- Subir nueva -->
          <div class="tab-pane fade p-3" id="mpc-tab-upload">
            <div class="border rounded p-4 text-center mb-3"
                 id="mpc-upload-zone"
                 style="cursor:pointer;border-style:dashed!important;border-color:#dee2e6;transition:.2s;">
              <i class="bi bi-cloud-arrow-up fs-1 text-muted"></i>
              <div class="mt-2 text-muted">Arrastra o haz clic para subir</div>
              <small class="text-muted">JPG, PNG, WebP, GIF — Máx 20MB</small>
            </div>
            <input type="file" id="mpc-file-input" accept="image/*" class="d-none">
            <div id="mpc-upload-preview" class="d-none mb-3 text-center">
              <img id="mpc-preview-img" src="" class="img-thumbnail" style="max-height:100px;">
              <div class="small text-muted mt-1" id="mpc-preview-name"></div>
            </div>
            <div id="mpc-upload-progress" class="d-none mb-3">
              <div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated w-100"></div></div>
              <div class="text-muted small text-center mt-1">Subiendo...</div>
            </div>
            <button type="button" class="btn btn-primary w-100" id="mpc-btn-upload" disabled>
              <i class="bi bi-upload me-1"></i> Subir e insertar
            </button>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <div class="me-auto small text-muted" id="mpc-selected-info">Ninguna imagen seleccionada</div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="mpc-btn-insert" disabled>
          <i class="bi bi-check-circle me-1"></i> Usar esta imagen
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
    const BASE = '<?= URLBASE ?>';

    // Mapa: qué campo estamos editando actualmente
    let currentTarget = null; // 'logo' | 'favicon' | 'banner'
    let selectedMedia = null;
    let mpcPage       = 1;
    let uploadedFile  = null;

    const targets = {
        logo    : { inputId: 'site_logo_path',    previewId: 'logo-preview-img',    wrapId: 'logo-preview-wrap',    title: 'Seleccionar Logo' },
        favicon : { inputId: 'site_favicon_path', previewId: 'favicon-preview-img', wrapId: 'favicon-preview-wrap', title: 'Seleccionar Favicon' },
        banner  : { inputId: 'banner_inferior_path', previewId: 'banner-preview-img', wrapId: 'banner-preview-wrap', title: 'Seleccionar Banner Inferior' },
    };

    /* — Abrir modal — */
    function openPicker(target) {
        currentTarget = target;
        selectedMedia = null;

        document.getElementById('mpc-title').textContent    = targets[target].title;
        document.getElementById('mpc-btn-insert').disabled  = true;
        document.getElementById('mpc-selected-info').textContent = 'Ninguna imagen seleccionada';
        document.getElementById('mpc-search').value         = '';
        document.querySelectorAll('#mpc-grid .mpc-card').forEach(c => c.style.outline = '');

        loadLibrary(1);
        new bootstrap.Modal(document.getElementById('modalMediaPickerConfig')).show();
    }

    document.getElementById('logo-open-media')?.addEventListener('click',    () => openPicker('logo'));
    document.getElementById('favicon-open-media')?.addEventListener('click', () => openPicker('favicon'));
    document.getElementById('banner-open-media')?.addEventListener('click',  () => openPicker('banner'));

    /* — Quitar imagen — */
   function removeMedia(inputId, previewImgId, wrapId) {
    document.getElementById(inputId).value      = '';
    document.getElementById(previewImgId).src   = '';
    document.getElementById(wrapId).style.cssText = 'display:none!important';
}

document.getElementById('logo-remove-btn')?.addEventListener('click', () =>
    removeMedia('site_logo_path', 'logo-preview-img', 'logo-preview-wrap'));

document.getElementById('favicon-remove-btn')?.addEventListener('click', () =>
    removeMedia('site_favicon_path', 'favicon-preview-img', 'favicon-preview-wrap'));

document.getElementById('banner-remove-btn')?.addEventListener('click', () =>
    removeMedia('banner_inferior_path', 'banner-preview-img', 'banner-preview-wrap'));

    /* — Cargar biblioteca — */
    function loadLibrary(page) {
        mpcPage = page;
        const q = document.getElementById('mpc-search').value.trim();
        const grid = document.getElementById('mpc-grid');
        grid.innerHTML = '<div class="col-12 text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> Cargando...</div>';
        document.getElementById('mpc-pagination').innerHTML = '';

        fetch(`${BASE}/admin/multimedia/media_picker.php?p=${page}&type=image&q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(data => {
                grid.innerHTML = '';
                document.getElementById('mpc-total').textContent = data.total + ' imágenes';

                if (!data.files.length) {
                    grid.innerHTML = '<div class="col-12 text-center py-4 text-muted">No hay imágenes</div>';
                    return;
                }

                data.files.forEach(f => {
                    const col = document.createElement('div');
                    col.className = 'col-6 col-sm-4 col-md-3 col-lg-2';
                    col.innerHTML = `
                        <div class="mpc-card border rounded overflow-hidden"
                             style="cursor:pointer;transition:.15s;"
                             data-path="${f.path}" data-url="${f.url}" data-name="${f.name}">
                          <div style="height:80px;overflow:hidden;background:#f8f9fa;display:flex;align-items:center;justify-content:center;">
                            <img src="${f.url}" style="width:100%;height:80px;object-fit:cover;" loading="lazy"
                                 onerror="this.parentNode.innerHTML='<i class=\'bi bi-image text-muted\' style=\'font-size:28px\'></i>'">
                          </div>
                          <div class="p-1 bg-white" style="font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            ${f.name}
                          </div>
                        </div>`;

                    col.querySelector('.mpc-card').addEventListener('click', function () {
                        document.querySelectorAll('.mpc-card').forEach(c => { c.style.outline = ''; c.style.boxShadow = ''; });
                        this.style.outline   = '3px solid #0d6efd';
                        this.style.boxShadow = '0 0 0 1px #0d6efd';
                        selectedMedia = { path: this.dataset.path, url: this.dataset.url, name: this.dataset.name };
                        document.getElementById('mpc-btn-insert').disabled = false;
                        document.getElementById('mpc-selected-info').textContent = this.dataset.name;
                    });

                    grid.appendChild(col);
                });

                // Paginación simple
                const pag = document.getElementById('mpc-pagination');
                if (data.total_pages > 1) {
                    if (page > 1) {
                        const b = document.createElement('button');
                        b.className = 'btn btn-sm btn-outline-secondary';
                        b.textContent = '‹ Anterior';
                        b.onclick = () => loadLibrary(page - 1);
                        pag.appendChild(b);
                    }
                    const s = document.createElement('span');
                    s.className = 'btn btn-sm btn-light disabled';
                    s.textContent = page + ' / ' + data.total_pages;
                    pag.appendChild(s);
                    if (page < data.total_pages) {
                        const b = document.createElement('button');
                        b.className = 'btn btn-sm btn-outline-secondary';
                        b.textContent = 'Siguiente ›';
                        b.onclick = () => loadLibrary(page + 1);
                        pag.appendChild(b);
                    }
                }
            })
            .catch(() => {
                document.getElementById('mpc-grid').innerHTML = '<div class="col-12 text-center py-4 text-danger">Error al cargar</div>';
            });
    }

    document.getElementById('mpc-btn-search')?.addEventListener('click', () => loadLibrary(1));
    document.getElementById('mpc-search')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); loadLibrary(1); }
    });

    /* — Insertar imagen seleccionada — */
    document.getElementById('mpc-btn-insert')?.addEventListener('click', () => {
        if (!selectedMedia || !currentTarget) return;
        applyMedia(selectedMedia.path, selectedMedia.url);
        bootstrap.Modal.getInstance(document.getElementById('modalMediaPickerConfig')).hide();
    });

    /* — Aplicar imagen al campo destino — */
    // Reemplaza applyMedia por:
    function applyMedia(path, url) {
    const t = targets[currentTarget];
    document.getElementById(t.inputId).value  = path;
    document.getElementById(t.previewId).src  = url;
    // Quitar todos los estilos inline y clases que lo oculten
    const wrap = document.getElementById(t.wrapId);
    wrap.style.cssText = '';
    wrap.classList.remove('d-none');
}
    /* — Upload zona — */
    const mpcZone     = document.getElementById('mpc-upload-zone');
    const mpcFileIn   = document.getElementById('mpc-file-input');
    const mpcPrev     = document.getElementById('mpc-upload-preview');
    const mpcPrevImg  = document.getElementById('mpc-preview-img');
    const mpcPrevName = document.getElementById('mpc-preview-name');
    const mpcBtnUp    = document.getElementById('mpc-btn-upload');
    const mpcProgress = document.getElementById('mpc-upload-progress');

    mpcZone?.addEventListener('click', () => mpcFileIn.click());
    ['dragenter','dragover'].forEach(e => mpcZone?.addEventListener(e, ev => { ev.preventDefault(); mpcZone.style.background = '#f0f4ff'; mpcZone.style.borderColor = '#0d6efd'; }));
    ['dragleave','drop'].forEach(e => mpcZone?.addEventListener(e, ev => { ev.preventDefault(); mpcZone.style.background = ''; mpcZone.style.borderColor = '#dee2e6'; }));
    mpcZone?.addEventListener('drop', ev => { mpcFileIn.files = ev.dataTransfer.files; handleMpcFile(); });
    mpcFileIn?.addEventListener('change', handleMpcFile);

    function handleMpcFile() {
        if (!mpcFileIn.files.length) return;
        uploadedFile = mpcFileIn.files[0];
        mpcPrevImg.src      = URL.createObjectURL(uploadedFile);
        mpcPrevName.textContent = uploadedFile.name + ' (' + (uploadedFile.size / 1024).toFixed(0) + 'KB)';
        mpcPrev.classList.remove('d-none');
        mpcBtnUp.disabled = false;
    }

    mpcBtnUp?.addEventListener('click', () => {
        if (!uploadedFile) return;
        const fd = new FormData();
        fd.append('file', uploadedFile);

        mpcBtnUp.disabled = true;
        mpcProgress.classList.remove('d-none');

        fetch(`${BASE}/admin/multimedia/upload_ajax.php`, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                mpcProgress.classList.add('d-none');
                mpcBtnUp.disabled = false;

                if (d.success) {
                    applyMedia(d.path, d.url);
                    bootstrap.Modal.getInstance(document.getElementById('modalMediaPickerConfig')).hide();
                } else {
                    alert('Error: ' + d.message);
                }
            })
            .catch(() => {
                mpcProgress.classList.add('d-none');
                mpcBtnUp.disabled = false;
                alert('Error al subir el archivo.');
            });
    });

    /* — Limpiar backdrop — */
    document.getElementById('modalMediaPickerConfig')?.addEventListener('hidden.bs.modal', function () {
        if (!document.querySelector('.modal.show')) {
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow  = '';
            document.body.style.paddingRight = '';
        }
    });

})();
</script>