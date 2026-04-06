<!-- /admin/inc/summernote.php  — usa Quill (reemplaza TinyMCE) -->

<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<!-- Modal: Galería de medios para el editor de contenido -->
<div class="modal fade" id="editorMediaModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-images me-2"></i> Biblioteca de medios</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div class="p-3">
          <div class="row g-2 mb-3 align-items-end">
            <div class="col-sm-5">
              <input type="text" id="em-search" class="form-control form-control-sm" placeholder="Buscar imagen...">
            </div>
            <div class="col-sm-2">
              <button type="button" class="btn btn-sm btn-primary w-100" id="btn-em-search">
                <i class="bi bi-search me-1"></i> Buscar
              </button>
            </div>
            <div class="col-sm-3 text-end">
              <span class="text-muted small" id="em-total"></span>
            </div>
          </div>

          <div class="row g-2">
            <div class="col-lg-8">
              <div id="em-grid" class="row g-2" style="min-height:250px;">
                <div class="col-12 text-center py-5 text-muted">
                  <div class="spinner-border spinner-border-sm me-2"></div> Cargando...
                </div>
              </div>
              <div id="em-pagination" class="d-flex justify-content-center mt-3 gap-1 flex-wrap"></div>
            </div>

            <div class="col-lg-4">
              <div class="border rounded p-3" style="background:#f8f9fa; min-height:250px;">
                <div id="em-detail-empty" class="text-center text-muted py-5">
                  <i class="bi bi-hand-index-thumb fs-2"></i>
                  <div class="mt-2 small">Selecciona una imagen<br>para ver sus detalles</div>
                </div>
                <div id="em-detail-content" class="d-none">
                  <img id="em-detail-img" src="" alt="" class="img-fluid rounded mb-2 w-100"
                       style="max-height:130px; object-fit:cover;">
                  <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Texto alternativo (alt)</label>
                    <input type="text" id="em-alt" class="form-control form-control-sm"
                           placeholder="Descripción de la imagen">
                  </div>
                  <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Caption (pie de foto)</label>
                    <input type="text" id="em-caption" class="form-control form-control-sm"
                           placeholder="Opcional">
                  </div>
                  <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Tamaño</label>
                    <select id="em-width" class="form-select form-select-sm">
                      <option value="">Grande (100%)</option>
                      <option value="img-medium" selected>Mediano (75%)</option>
                      <option value="img-small">Pequeño (50%)</option>
                      <option value="img-thumbnail">Miniatura (25%)</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <span class="me-auto small text-muted" id="em-selected-info"></span>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-em-insert" disabled>
          <i class="bi bi-check-circle me-1"></i> Insertar imagen
        </button>
      </div>
    </div>
  </div>
</div>

<script>
/* ── Image Resizer: drag corners to resize images inside Quill 2 ── */
function initImageResizer(quill) {
  var quillRoot = quill.root;                   // .ql-editor
  var container = quillRoot.parentElement;      // .ql-container (position:relative)

  var overlay   = null;
  var activeImg = null;

  function removeOverlay() {
    if (overlay) { overlay.remove(); overlay = null; }
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

  /* Click en una imagen → mostrar handles */
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

  var emSelected = null;
  var emEditor = null;

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
    emSelected = null;
    emEditor = this;
    document.getElementById('em-detail-empty').classList.remove('d-none');
    document.getElementById('em-detail-content').classList.add('d-none');
    document.getElementById('btn-em-insert').disabled = true;
    document.getElementById('em-selected-info').textContent = '';
    document.getElementById('em-search').value = '';
    loadEmGrid(1);
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

  function loadEmGrid(page) {
    var q = (document.getElementById('em-search').value || '').trim();
    var grid = document.getElementById('em-grid');
    var pgn = document.getElementById('em-pagination');

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
          var col = document.createElement('div');
          col.className = 'col-6 col-sm-4 col-md-3';
          col.innerHTML =
            '<div class="em-card border rounded overflow-hidden" style="cursor:pointer;" '
            + 'data-url="' + _esc(f.url) + '" '
            + 'data-alt="' + _esc(f.alt || '') + '" '
            + 'data-caption="' + _esc(f.caption || '') + '" '
            + 'data-name="' + _esc(f.name) + '">'
            + '<div style="height:80px;overflow:hidden;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">'
            + '<img src="' + f.url + '" alt="' + _esc(f.name) + '" style="width:100%;height:80px;object-fit:cover;" loading="lazy" '
            + 'onerror="this.parentNode.innerHTML=\'<i class=\\\'bi bi-image text-muted\\\' style=\\\'font-size:28px\\\'></i>\'">'
            + '</div>'
            + '<div class="p-1 bg-white" style="font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'
            + _esc(f.name) + '</div>'
            + '</div>';

          col.querySelector('.em-card').addEventListener('click', function () {
            document.querySelectorAll('.em-card').forEach(function (el) {
              el.style.outline = '';
              el.style.boxShadow = '';
            });
            this.style.outline = '3px solid #0d6efd';
            this.style.boxShadow = '0 0 0 1px #0d6efd';

            emSelected = {
              url: this.dataset.url,
              alt: this.dataset.alt,
              caption: this.dataset.caption,
              widthClass: ''
            };

            document.getElementById('em-detail-empty').classList.add('d-none');
            document.getElementById('em-detail-content').classList.remove('d-none');
            document.getElementById('em-detail-img').src = emSelected.url;
            document.getElementById('em-alt').value = emSelected.alt;
            document.getElementById('em-caption').value = emSelected.caption;
            document.getElementById('em-width').value = 'img-medium';
            document.getElementById('btn-em-insert').disabled = false;
            document.getElementById('em-selected-info').textContent = this.dataset.name;
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

  document.getElementById('btn-em-insert').addEventListener('click', function () {
    if (!emSelected || !emEditor) return;

    var alt = document.getElementById('em-alt').value.trim();
    var caption = document.getElementById('em-caption').value.trim();
    var widthClass = document.getElementById('em-width').value;
    var url = emSelected.url;

    var range = emEditor.quill.getSelection() || { index: emEditor.quill.getLength() };
    var html;
    var imgClass = 'img-fluid ' + (widthClass || '');
    if (caption) {
      html = '<figure class="figure d-block text-center">'
           + '<img src="' + url + '" alt="' + _esc(alt) + '" class="' + imgClass + ' figure-img" style="max-width:100%;">'
           + '<figcaption class="figure-caption">' + _escHtml(caption) + '</figcaption>'
           + '</figure>';
    } else {
      html = '<img src="' + url + '" alt="' + _esc(alt) + '" class="' + imgClass + '" style="max-width:100%;">';
    }
    
    emEditor.quill.clipboard.dangerouslyPasteHTML(range.index, html);
    
    var newIndex = range.index + 1;
    if (caption) newIndex += 2;
    emEditor.quill.setSelection(newIndex, 0);

    bootstrap.Modal.getInstance(document.getElementById('editorMediaModal')).hide();
  });

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
