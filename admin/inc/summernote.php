<!-- /admin/inc/summernote.php  — usa Quill (reemplaza TinyMCE) -->

<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<style>
  /* Sin margen por defecto en imágenes — el usuario lo controla desde el editor */
  .ql-editor img { margin: 0; vertical-align: bottom; cursor: grab; }
  .ql-editor img:active { cursor: grabbing; }
  .ql-editor p:has(> img:only-child) { margin: 0; line-height: 0; }
  /* El pie de foto queda pegado a la imagen, sin espacio entre ambos */
  .ql-editor p:has(> img:only-child) + p { margin-top: 0 !important; }

  /* Tarjetas de medios en la galería */
  .em-card { cursor: pointer; transition: .15s; position: relative; }
  .em-card:hover { opacity: .88; }
  .em-check-badge {
    position: absolute; top: 4px; right: 4px;
    width: 22px; height: 22px;
    background: #0d6efd; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    z-index: 1; pointer-events: none;
  }
  .em-check-badge i { font-size: 13px; font-weight: bold; }

  /* Miniaturas en la cola de subida */
  .em-upload-thumb {
    position: relative; border-radius: 6px; overflow: hidden;
    border: 1px solid #dee2e6; background: #f8f9fa;
  }
  .em-upload-thumb img { width: 100%; height: 80px; object-fit: cover; display: block; }
  .em-upload-thumb .thumb-name {
    font-size: 10px; padding: 2px 4px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    background: #fff;
  }
  .em-upload-thumb .thumb-remove {
    position: absolute; top: 2px; right: 2px;
    width: 18px; height: 18px; border-radius: 50%;
    background: rgba(220,53,69,.85); border: none; color: #fff;
    font-size: 11px; line-height: 1; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
  }
  .em-upload-thumb .thumb-status {
    position: absolute; inset: 0; display: flex;
    align-items: center; justify-content: center;
    background: rgba(255,255,255,.75);
    font-size: 20px;
  }
</style>

<!-- Modal: Galería de medios para el editor de contenido -->
<div class="modal fade" id="editorMediaModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-images me-2"></i> Biblioteca de medios</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">

        <!-- TABS -->
        <ul class="nav nav-tabs px-3 pt-2" id="editorMediaTabs">
          <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#em-tab-library">
              <i class="bi bi-grid me-1"></i> Biblioteca
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#em-tab-upload">
              <i class="bi bi-cloud-upload me-1"></i> Subir archivos
            </a>
          </li>
        </ul>

        <div class="tab-content">

          <!-- TAB BIBLIOTECA -->
          <div class="tab-pane fade show active p-3" id="em-tab-library">

            <div class="row g-2 mb-2 align-items-end">
              <div class="col-sm-6">
                <input type="text" id="em-search" class="form-control form-control-sm"
                       placeholder="Buscar imagen...">
              </div>
              <div class="col-sm-3">
                <button type="button" class="btn btn-sm btn-primary w-100" id="btn-em-search">
                  <i class="bi bi-search me-1"></i> Buscar
                </button>
              </div>
              <div class="col-sm-3 text-end">
                <span class="text-muted small" id="em-total"></span>
              </div>
            </div>

            <!-- Selector de tamaño global + hint de selección múltiple -->
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
              <label class="form-label small fw-semibold mb-0 text-nowrap">Tamaño:</label>
              <select id="em-width" class="form-select form-select-sm" style="max-width:180px;">
                <option value="">Grande (100%)</option>
                <option value="img-medium" selected>Mediano (75%)</option>
                <option value="img-small">Pequeño (50%)</option>
                <option value="img-thumbnail">Miniatura (25%)</option>
              </select>
              <span class="text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                Haz clic en las imágenes para seleccionarlas (puedes elegir varias)
              </span>
            </div>

            <div id="em-grid" class="row g-2" style="min-height:250px;"></div>
            <div id="em-pagination" class="d-flex justify-content-center mt-3 gap-1 flex-wrap"></div>
          </div>

          <!-- TAB SUBIR ARCHIVOS -->
          <div class="tab-pane fade p-3" id="em-tab-upload">

            <!-- Zona de drop -->
            <div class="border rounded p-4 text-center mb-3"
                 id="em-upload-zone"
                 style="cursor:pointer; border-style:dashed !important; border-color:#dee2e6; transition:.2s;
                        min-height:110px; display:flex; flex-direction:column;
                        align-items:center; justify-content:center;">
              <i class="bi bi-cloud-arrow-up fs-1 text-muted"></i>
              <div class="mt-2 text-muted">Arrastra archivos aquí o haz clic para seleccionar</div>
              <small class="text-muted">JPG, PNG, WebP, GIF, SVG — Máx 20 MB por archivo — Puedes elegir varios</small>
            </div>
            <input type="file" id="em-file-input" accept="image/*" multiple class="d-none">

            <!-- Previsualización de los archivos seleccionados -->
            <div id="em-upload-queue" class="row g-2 mb-3"></div>

            <!-- Botón subir -->
            <button type="button" class="btn btn-primary w-100" id="btn-em-upload" disabled>
              <i class="bi bi-upload me-1"></i>
              Subir <span id="em-upload-count">0</span> archivo(s) y seleccionar para insertar
            </button>

            <!-- Barra de progreso -->
            <div id="em-upload-progress" class="d-none mt-3">
              <div class="progress mb-1" style="height:10px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated"
                     id="em-progress-bar" style="width:0%; transition:width .3s;"></div>
              </div>
              <div class="text-muted small text-center" id="em-progress-text">Preparando...</div>
            </div>

          </div>

        </div>
      </div>

      <div class="modal-footer">
        <span class="me-auto small text-muted fw-semibold" id="em-selected-info">
          Ninguna imagen seleccionada
        </span>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-em-insert" disabled>
          <i class="bi bi-check-circle me-1"></i>
          Insertar <span id="em-insert-count">0</span> imagen(es)
        </button>
      </div>
    </div>
  </div>
</div>

<script>
/* ── Image Resizer + Delete + Drag-to-move para imágenes en Quill 2 ── */
function initImageResizer(quill) {
  var quillRoot = quill.root;                   // .ql-editor
  var container = quillRoot.parentElement;      // .ql-container (position:relative)

  var overlay    = null;
  var activeImg  = null;

  /* Variables para drag-to-move */
  var draggedImg  = null;
  var draggedIdx  = null;
  var draggedHtml = null;
  var dropCursor  = null;   // línea indicadora de posición de drop

  /* ── Helpers de overlay ── */
  function removeOverlay() {
    if (overlay) { overlay.remove(); overlay = null; }
    if (activeImg) activeImg.removeAttribute('draggable');
    activeImg = null;
  }

  function positionOverlay() {
    if (!overlay || !activeImg) return;
    var ir = activeImg.getBoundingClientRect();
    var cr = container.getBoundingClientRect();
    overlay.style.left   = (ir.left - cr.left) + 'px';
    overlay.style.top    = (ir.top  - cr.top)  + 'px';
    overlay.style.width  = ir.width  + 'px';
    overlay.style.height = ir.height + 'px';
  }

  function showOverlay(img) {
    removeOverlay();
    activeImg = img;
    img.setAttribute('draggable', 'true');

    overlay = document.createElement('div');
    overlay.style.cssText = [
      'position:absolute',
      'pointer-events:none',
      'z-index:99',
      'border:2px dashed #0d6efd',
      'box-sizing:border-box'
    ].join(';');
    container.appendChild(overlay);

    ['nw','ne','sw','se'].forEach(function(corner) {
      var h = document.createElement('span');
      h.style.cssText = [
        'position:absolute',
        'display:block',
        'width:12px',
        'height:12px',
        'background:#0d6efd',
        'border:2px solid #fff',
        'border-radius:2px',
        'pointer-events:all'
      ].join(';');
      if (corner === 'nw') { h.style.top = '-6px';    h.style.left  = '-6px';  h.style.cursor = 'nw-resize'; }
      if (corner === 'ne') { h.style.top = '-6px';    h.style.right = '-6px';  h.style.cursor = 'ne-resize'; }
      if (corner === 'sw') { h.style.bottom = '-6px'; h.style.left  = '-6px';  h.style.cursor = 'sw-resize'; }
      if (corner === 'se') { h.style.bottom = '-6px'; h.style.right = '-6px';  h.style.cursor = 'se-resize'; }

      h.addEventListener('mousedown', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var startX = e.clientX;
        var imgRect = activeImg.getBoundingClientRect();
        var startW  = imgRect.width;
        var startH  = imgRect.height;
        var ratio   = startH / startW;
        var isLeft  = corner === 'nw' || corner === 'sw';

        function onMove(e) {
          var dx  = e.clientX - startX;
          var newW = Math.max(40, isLeft ? startW - dx : startW + dx);
          activeImg.style.width  = newW + 'px';
          activeImg.style.height = (newW * ratio) + 'px';
          positionOverlay();
        }
        function onUp() {
          document.removeEventListener('mousemove', onMove);
          document.removeEventListener('mouseup', onUp);
        }
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
      });

      overlay.appendChild(h);
    });

    positionOverlay();
  }

  /* ── Click en imagen → handles ── */
  quillRoot.addEventListener('click', function(e) {
    if (e.target.tagName === 'IMG') {
      showOverlay(e.target);
    } else {
      removeOverlay();
    }
  });

  /* Click fuera del editor → quitar handles */
  document.addEventListener('click', function(e) {
    if (activeImg && !container.contains(e.target)) {
      removeOverlay();
    }
  });

  /* Scroll del editor → reposicionar overlay */
  quillRoot.addEventListener('scroll', positionOverlay);

  /* ── Eliminar imagen con Supr / Delete ── */
  document.addEventListener('keydown', function(e) {
    if (!activeImg || !activeImg.isConnected) return;
    if (e.key !== 'Delete' && e.key !== 'Backspace') return;
    /* No interferir si el foco está en un campo de texto */
    var tag = (document.activeElement || {}).tagName || '';
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
    e.preventDefault();
    try {
      var blot = Quill.find(activeImg, true);
      if (blot) quill.deleteText(quill.getIndex(blot), 1, 'user');
    } catch (ex) {
      /* fallback: eliminar el nodo directamente */
      activeImg.closest('p') ? activeImg.closest('p').remove() : activeImg.remove();
    }
    removeOverlay();
  });

  /* ── Obtener índice Quill desde coordenadas de ratón ── */
  function getDropIndex(clientX, clientY) {
    var node, offset;
    if (document.caretPositionFromPoint) {
      var pos = document.caretPositionFromPoint(clientX, clientY);
      if (!pos) return quill.getLength() - 1;
      node = pos.offsetNode; offset = pos.offset;
    } else if (document.caretRangeFromPoint) {
      var r = document.caretRangeFromPoint(clientX, clientY);
      if (!r) return quill.getLength() - 1;
      node = r.startContainer; offset = r.startOffset;
    } else {
      return quill.getLength() - 1;
    }
    var current = node;
    while (current && current !== quillRoot) {
      try {
        var blot = Quill.find(current, false);
        if (blot) {
          var idx = quill.getIndex(blot);
          if (node.nodeType === 3) idx += Math.min(offset, node.textContent.length);
          return Math.max(0, Math.min(idx, quill.getLength() - 1));
        }
      } catch (ex) {}
      current = current.parentNode;
    }
    return quill.getLength() - 1;
  }

  /* ── Línea indicadora de drop ── */
  function showDropCursor(clientX, clientY) {
    if (!dropCursor) {
      dropCursor = document.createElement('div');
      dropCursor.style.cssText = 'position:absolute;pointer-events:none;z-index:200;'
        + 'width:3px;background:#0d6efd;border-radius:2px;transition:top .05s,left .05s,height .05s;';
      container.appendChild(dropCursor);
    }
    /* Buscar el elemento más cercano al punto para dimensionar el cursor */
    var cr  = container.getBoundingClientRect();
    var el  = document.elementFromPoint(clientX, clientY);
    var ref = (el && container.contains(el)) ? el : null;
    var h   = ref ? ref.getBoundingClientRect().height : 20;
    var top = ref ? (ref.getBoundingClientRect().top - cr.top) : (clientY - cr.top - 10);
    dropCursor.style.left   = (clientX - cr.left - 1) + 'px';
    dropCursor.style.top    = top + 'px';
    dropCursor.style.height = Math.max(16, h) + 'px';
  }

  function removeDropCursor() {
    if (dropCursor) { dropCursor.remove(); dropCursor = null; }
  }

  /* ── Drag-to-move ── */
  quillRoot.addEventListener('dragstart', function(e) {
    if (e.target.tagName !== 'IMG') { e.preventDefault(); return; }
    draggedImg  = e.target;
    draggedHtml = e.target.outerHTML;
    try {
      var blot = Quill.find(draggedImg, true);
      draggedIdx = blot ? quill.getIndex(blot) : null;
    } catch (ex) { draggedIdx = null; }
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', '');   /* requerido en Firefox */
    setTimeout(function() { if (draggedImg) draggedImg.style.opacity = '0.25'; }, 0);
  });

  quillRoot.addEventListener('dragover', function(e) {
    if (!draggedImg) return;
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    showDropCursor(e.clientX, e.clientY);
  });

  quillRoot.addEventListener('dragleave', function(e) {
    if (!quillRoot.contains(e.relatedTarget)) removeDropCursor();
  });

  quillRoot.addEventListener('drop', function(e) {
    removeDropCursor();
    if (!draggedImg || draggedIdx === null) return;
    e.preventDefault();
    e.stopPropagation();

    var dropIdx = getDropIndex(e.clientX, e.clientY);
    var html    = draggedHtml;

    draggedImg.style.opacity = '';
    draggedImg  = null;

    /* Borrar del sitio original */
    quill.deleteText(draggedIdx, 1, 'user');

    /* Ajustar índice si el destino estaba después del origen */
    if (dropIdx > draggedIdx) dropIdx = Math.max(0, dropIdx - 1);

    /* Insertar en la nueva posición conservando todos los atributos (src, class, style…) */
    quill.clipboard.dangerouslyPasteHTML(dropIdx, html, 'user');

    draggedIdx  = null;
    draggedHtml = null;
    removeOverlay();
  });

  /* Cancelar drag (soltó fuera del editor) */
  quillRoot.addEventListener('dragend', function() {
    removeDropCursor();
    if (draggedImg) { draggedImg.style.opacity = ''; draggedImg = null; }
    draggedIdx  = null;
    draggedHtml = null;
  });

  /* Cambio de contenido → reposicionar o quitar overlay */
  quill.on('text-change', function() {
    if (activeImg && activeImg.isConnected) {
      positionOverlay();
    } else {
      removeOverlay();
    }
  });
}

(function () {
  var UPLOAD_URL = '<?= $url ?>/admin/multimedia/upload_ajax.php';
  var PICKER_URL = '<?= $url ?>/admin/multimedia/media_picker.php';

  /* Lista de imágenes seleccionadas para insertar: [{url, alt, caption}] */
  var emSelectedList = [];
  var emEditor = null;

  /* ─────────────── QuillEditor ─────────────── */
  var QuillEditor = function (selector) {
    this.selector = selector;
    this.quill = null;
    this.imageHandler = null;
  };

  QuillEditor.prototype.init = function (imageHandler) {
    var self = this;
    this.imageHandler = imageHandler;

    var container = document.querySelector(this.selector);
    if (!container) return;

    var wrapper = document.createElement('div');
    wrapper.className = 'quill-wrapper';
    wrapper.style.cssText = 'min-height:350px;';

    var editorDiv = document.createElement('div');
    editorDiv.style.cssText = 'min-height:300px;';

    container.style.display = 'none';
    container.parentNode.insertBefore(wrapper, container);
    wrapper.appendChild(editorDiv);
    wrapper.appendChild(container);

    this.quill = new Quill(editorDiv, {
      theme: 'snow',
      height: 300,
      modules: {
        toolbar: {
          container: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['blockquote', 'code-block'],
            ['link', 'image', 'video'],
            ['clean']
          ],
          handlers: {
            image: function () {
              self.openGallery();
            }
          }
        },
      }
    });

    initImageResizer(this.quill);

    this.quill.root.addEventListener('paste', function (e) {
      var clipboardData = e.clipboardData || window.clipboardData;
      if (!clipboardData) return;

      var items = clipboardData.items;
      if (!items) return;

      for (var i = 0; i < items.length; i++) {
        if (items[i].type.indexOf('image') !== -1) {
          e.preventDefault();
          var file = items[i].getAsFile();
          self.uploadImage(file);
          break;
        }
      }
    });
  };

  QuillEditor.prototype.openGallery = function () {
    // Guardar el cursor ANTES de que el modal quite el foco al editor
    this._savedRange = this.quill.getSelection() || { index: this.quill.getLength(), length: 0 };
    emSelectedList = [];
    emEditor = this;
    updateEmSelection();
    document.getElementById('em-search').value = '';
    // Asegurar que se inicia en la tab de biblioteca
    var libTab = document.querySelector('[href="#em-tab-library"]');
    if (libTab) bootstrap.Tab.getOrCreateInstance(libTab).show();
    loadEmGrid(1);
    // Limpiar cola de subida
    resetUploadTab();
    new bootstrap.Modal(document.getElementById('editorMediaModal')).show();
  };

  QuillEditor.prototype.uploadImage = function (file) {
    var self = this;
    var fd = new FormData();
    fd.append('file', file);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', UPLOAD_URL);
    xhr.onload = function () {
      if (xhr.status !== 200) {
        alert('Error al subir imagen');
        return;
      }
      try {
        var r = JSON.parse(xhr.responseText);
        if (r.success) {
          var range = self.quill.getSelection();
          self.quill.insertEmbed(range.index, 'image', r.url);
        } else {
          alert(r.message || 'Error al subir la imagen');
        }
      } catch (e) {
        alert('Respuesta inválida del servidor');
      }
    };
    xhr.onerror = function () { alert('Error de red'); };
    xhr.send(fd);
  };

  QuillEditor.prototype.setContent = function (html) {
    if (this.quill && html) {
      this.quill.root.innerHTML = html;
    }
  };

  QuillEditor.prototype.getContent = function () {
    if (this.quill) {
      return this.quill.getSemanticHTML() || this.quill.root.innerHTML;
    }
    return '';
  };

  QuillEditor.prototype.syncToTextarea = function () {
    var textarea = document.querySelector(this.selector);
    if (textarea && this.quill) {
      textarea.value = this.quill.root.innerHTML;
    }
  };

  var editors = [];

  document.addEventListener('submit', function () {
    editors.forEach(function (ed) { ed.syncToTextarea(); });
  }, true);

  function initEditors() {
    document.querySelectorAll('textarea.summernote').forEach(function (textarea) {
      if (!textarea.id) {
        textarea.id = 'editor-' + Math.random().toString(36).substr(2, 9);
      }
      var quillEditor = new QuillEditor('#' + textarea.id);
      quillEditor.init(function () { quillEditor.openGallery(); });

      var initialContent = textarea.value;
      if (initialContent) {
        setTimeout(function() {
          quillEditor.setContent(initialContent);
        }, 100);
      }

      editors.push(quillEditor);
      window[textarea.id + '_quill'] = quillEditor;
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEditors);
  } else {
    initEditors();
  }

  /* ─────────────── Actualizar contador de selección ─────────────── */
  function updateEmSelection() {
    var count = emSelectedList.length;
    var infoEl   = document.getElementById('em-selected-info');
    var countEl  = document.getElementById('em-insert-count');
    var btnInsert = document.getElementById('btn-em-insert');

    if (count === 0) {
      infoEl.textContent = 'Ninguna imagen seleccionada';
      btnInsert.disabled = true;
    } else {
      infoEl.textContent = count + ' imagen' + (count > 1 ? 'es' : '') + ' seleccionada' + (count > 1 ? 's' : '');
      btnInsert.disabled = false;
    }
    countEl.textContent = count;
  }

  /* ─────────────── Grid de biblioteca (multi-select) ─────────────── */
  function loadEmGrid(page) {
    var q    = (document.getElementById('em-search').value || '').trim();
    var grid = document.getElementById('em-grid');
    var pgn  = document.getElementById('em-pagination');

    grid.innerHTML = '<div class="col-12 text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> Cargando...</div>';
    pgn.innerHTML = '';

    fetch(PICKER_URL + '?type=image&p=' + page + '&q=' + encodeURIComponent(q))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        grid.innerHTML = '';
        document.getElementById('em-total').textContent = data.total + ' imágenes';

        if (!data.files || !data.files.length) {
          grid.innerHTML = '<div class="col-12 text-center py-4 text-muted"><i class="bi bi-images fs-2"></i><div class="mt-2">No hay imágenes</div></div>';
          return;
        }

        data.files.forEach(function (f) {
          var isSelected = emSelectedList.some(function(s) { return s.url === f.url; });
          var col = document.createElement('div');
          col.className = 'col-6 col-sm-4 col-md-3';

          col.innerHTML =
            '<div class="em-card border rounded overflow-hidden'
            + (isSelected ? '" style="outline:3px solid #0d6efd; box-shadow:0 0 0 1px #0d6efd;"' : '"')
            + ' data-url="' + _esc(f.url) + '"'
            + ' data-alt="' + _esc(f.alt || '') + '"'
            + ' data-caption="' + _esc(f.caption || '') + '"'
            + ' data-name="' + _esc(f.name) + '">'
            + (isSelected ? '<div class="em-check-badge"><i class="bi bi-check text-white"></i></div>' : '')
            + '<div style="height:80px;overflow:hidden;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">'
            + '<img src="' + f.url + '" alt="' + _esc(f.name) + '"'
            + ' style="width:100%;height:80px;object-fit:cover;" loading="lazy"'
            + ' onerror="this.parentNode.innerHTML=\'<i class=\\\'bi bi-image text-muted\\\' style=\\\'font-size:28px\\\'></i>\'">'
            + '</div>'
            + '<div class="p-1 bg-white" style="font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'
            + _esc(f.name) + '</div>'
            + '</div>';

          var card = col.querySelector('.em-card');
          card.addEventListener('click', function () {
            var url     = this.dataset.url;
            var selIdx  = emSelectedList.findIndex(function(s) { return s.url === url; });

            if (selIdx >= 0) {
              /* Deseleccionar */
              emSelectedList.splice(selIdx, 1);
              this.style.outline    = '';
              this.style.boxShadow  = '';
              var badge = this.querySelector('.em-check-badge');
              if (badge) badge.remove();
            } else {
              /* Seleccionar */
              emSelectedList.push({
                url    : this.dataset.url,
                alt    : this.dataset.alt,
                caption: this.dataset.caption
              });
              this.style.outline   = '3px solid #0d6efd';
              this.style.boxShadow = '0 0 0 1px #0d6efd';
              var newBadge = document.createElement('div');
              newBadge.className = 'em-check-badge';
              newBadge.innerHTML = '<i class="bi bi-check text-white"></i>';
              this.appendChild(newBadge);
            }

            updateEmSelection();
          });

          grid.appendChild(col);
        });

        renderEmPagination(data.page, data.total_pages);
      })
      .catch(function () {
        grid.innerHTML = '<div class="col-12 text-center py-4 text-danger">Error al cargar la biblioteca</div>';
      });
  }

  function renderEmPagination(current, total) {
    var pgn = document.getElementById('em-pagination');
    pgn.innerHTML = '';
    if (total <= 1) return;
    var html = '';
    if (current > 1) html += '<button class="btn btn-sm btn-outline-secondary em-pg" data-p="' + (current - 1) + '">‹ Anterior</button>';
    html += '<span class="btn btn-sm btn-light disabled px-3">' + current + ' / ' + total + '</span>';
    if (current < total) html += '<button class="btn btn-sm btn-outline-secondary em-pg" data-p="' + (current + 1) + '">Siguiente ›</button>';
    pgn.innerHTML = html;
    pgn.querySelectorAll('.em-pg').forEach(function (b) {
      b.addEventListener('click', function () { loadEmGrid(parseInt(this.dataset.p)); });
    });
  }

  document.getElementById('btn-em-search').addEventListener('click', function () { loadEmGrid(1); });
  document.getElementById('em-search').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); loadEmGrid(1); }
  });

  /* ─────────────── Insertar todas las seleccionadas ─────────────── */
  document.getElementById('btn-em-insert').addEventListener('click', function () {
    if (!emSelectedList.length || !emEditor) return;

    var widthClass = document.getElementById('em-width').value;
    var imgClass   = 'img-fluid' + (widthClass ? ' ' + widthClass : '');
    var idx = (emEditor._savedRange || { index: emEditor.quill.getLength() }).index;

    var insertHtml = '';
    var extraChars = 0;
    emSelectedList.forEach(function (item) {
      insertHtml += '<img src="' + item.url + '" alt="' + _esc(item.alt) + '" class="' + imgClass + '" style="max-width:100%;">';
      if (item.caption) {
        insertHtml += '<p style="text-align:center;font-style:italic;margin:0 0 0.5em;">' + _escHtml(item.caption) + '</p>';
        extraChars += item.caption.length + 2;
      }
    });

    emEditor.quill.clipboard.dangerouslyPasteHTML(idx, insertHtml);

    var after = idx + emSelectedList.length + extraChars;
    emEditor.quill.setSelection(after, 0);

    bootstrap.Modal.getInstance(document.getElementById('editorMediaModal')).hide();
  });

  /* ─────────────── Tab Subir archivos (múltiple) ─────────────── */
  var uploadQueue = []; // [{file, objectUrl}]

  var uploadZone    = document.getElementById('em-upload-zone');
  var fileInput     = document.getElementById('em-file-input');
  var queueEl       = document.getElementById('em-upload-queue');
  var btnUpload     = document.getElementById('btn-em-upload');
  var uploadCountEl = document.getElementById('em-upload-count');
  var progressWrap  = document.getElementById('em-upload-progress');
  var progressBar   = document.getElementById('em-progress-bar');
  var progressText  = document.getElementById('em-progress-text');

  uploadZone.addEventListener('click', function () { fileInput.click(); });

  ['dragenter','dragover'].forEach(function(ev) {
    uploadZone.addEventListener(ev, function(e) {
      e.preventDefault();
      uploadZone.style.background   = '#f0f4ff';
      uploadZone.style.borderColor  = '#0d6efd';
    });
  });
  ['dragleave','drop'].forEach(function(ev) {
    uploadZone.addEventListener(ev, function(e) {
      e.preventDefault();
      uploadZone.style.background  = '';
      uploadZone.style.borderColor = '#dee2e6';
    });
  });
  uploadZone.addEventListener('drop', function (e) {
    e.preventDefault();
    uploadZone.style.background  = '';
    uploadZone.style.borderColor = '#dee2e6';
    addFilesToQueue(e.dataTransfer.files);
  });
  fileInput.addEventListener('change', function () {
    addFilesToQueue(this.files);
    this.value = ''; // reset para permitir re-seleccionar los mismos archivos
  });

  function addFilesToQueue(fileList) {
    Array.from(fileList).forEach(function (file) {
      if (!file.type.startsWith('image/')) return;
      uploadQueue.push({ file: file, objectUrl: URL.createObjectURL(file) });
    });
    renderUploadQueue();
  }

  function renderUploadQueue() {
    queueEl.innerHTML = '';
    uploadQueue.forEach(function (item, i) {
      var col = document.createElement('div');
      col.className = 'col-4 col-sm-3 col-md-2';
      col.innerHTML =
        '<div class="em-upload-thumb">'
        + '<img src="' + item.objectUrl + '" alt="">'
        + '<div class="thumb-name" title="' + _esc(item.file.name) + '">' + _esc(item.file.name) + '</div>'
        + '<button type="button" class="thumb-remove" data-idx="' + i + '" title="Quitar">&times;</button>'
        + '</div>';
      col.querySelector('.thumb-remove').addEventListener('click', function () {
        var idx = parseInt(this.dataset.idx);
        URL.revokeObjectURL(uploadQueue[idx].objectUrl);
        uploadQueue.splice(idx, 1);
        renderUploadQueue();
      });
      queueEl.appendChild(col);
    });

    var count = uploadQueue.length;
    uploadCountEl.textContent = count;
    btnUpload.disabled = (count === 0);
    if (count > 0) {
      queueEl.classList.remove('d-none');
    } else {
      queueEl.classList.add('d-none');
      progressWrap.classList.add('d-none');
    }
  }

  function resetUploadTab() {
    uploadQueue.forEach(function(item) { URL.revokeObjectURL(item.objectUrl); });
    uploadQueue = [];
    renderUploadQueue();
    progressWrap.classList.add('d-none');
    progressBar.style.width = '0%';
    progressText.textContent = 'Preparando...';
  }

  btnUpload.addEventListener('click', function () {
    if (!uploadQueue.length) return;

    btnUpload.disabled = true;
    progressWrap.classList.remove('d-none');

    var total    = uploadQueue.length;
    var done     = 0;
    var uploaded = [];

    function uploadNext(i) {
      if (i >= total) {
        /* Todos subidos */
        progressBar.style.width = '100%';
        progressText.textContent = 'Listo. ' + uploaded.length + ' de ' + total + ' subidos.';

        /* Agregar a la selección */
        uploaded.forEach(function (item) {
          if (!emSelectedList.some(function(s) { return s.url === item.url; })) {
            emSelectedList.push(item);
          }
        });
        updateEmSelection();

        /* Limpiar cola y cambiar a tab Biblioteca */
        setTimeout(function () {
          resetUploadTab();
          var libTab = document.querySelector('[href="#em-tab-library"]');
          if (libTab) bootstrap.Tab.getOrCreateInstance(libTab).show();
          loadEmGrid(1); // recarga la biblioteca para mostrar las nuevas imágenes
        }, 600);
        return;
      }

      var item = uploadQueue[i];
      progressText.textContent = 'Subiendo ' + (i + 1) + ' de ' + total + ': ' + item.file.name;
      progressBar.style.width = Math.round((i / total) * 100) + '%';

      var fd = new FormData();
      fd.append('file', item.file);

      fetch(UPLOAD_URL, { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.success) {
            uploaded.push({ url: d.url, alt: d.alt || '', caption: d.caption || '' });
          }
          done++;
          uploadNext(i + 1);
        })
        .catch(function () {
          done++;
          uploadNext(i + 1);
        });
    }

    uploadNext(0);
  });

  /* ─────────────── Helpers ─────────────── */
  function _esc(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }
  function _escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  window.editorSetContent = function (id, html) {
    var quill = window[id + '_quill'];
    if (quill) quill.setContent(html || '');
  };
  window.editorGetContent = function (id) {
    var quill = window[id + '_quill'];
    return quill ? quill.getContent() : '';
  };

})();
</script>
