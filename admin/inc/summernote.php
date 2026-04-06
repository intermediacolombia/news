<!-- /admin/inc/summernote.php -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-es-ES.min.js"></script>

<style>
.note-editor .note-btn[data-event="help"] {
  display: none !important;
}

.note-editor.note-frame {
  border: 1px solid #dee2e6;
  border-radius: 6px;
}

.summernote-image-modal .modal-body {
  padding: 20px;
}

.summernote-image-modal .form-label {
  font-weight: 600;
  margin-bottom: 5px;
}

.summernote-image-modal .preview-image {
  max-width: 100%;
  max-height: 200px;
  margin-top: 10px;
  border-radius: 8px;
  display: none;
}
</style>

<!-- Modal para alt y caption de imagen -->
<div class="modal fade summernote-image-modal" id="imageModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Configurar Imagen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Texto Alternativo (alt)</label>
          <input type="text" class="form-control" id="imgAltText" placeholder="Descripción de la imagen">
          <small class="text-muted">Importante para accesibilidad y SEO</small>
        </div>
        <div class="mb-3">
          <label class="form-label">Pie de foto (caption)</label>
          <input type="text" class="form-control" id="imgCaptionText" placeholder="Texto debajo de la imagen">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="cancelImageBtn">Cancelar</button>
        <button type="button" class="btn btn-primary" id="insertImageBtn">Insertar</button>
      </div>
    </div>
  </div>
</div>

<script>
var pendingImageFile = null;
var pendingImageEditor = null;
var pendingImageUrl = null;
var pendingImageDataUrl = null;

$(document).ready(function() {
  $('.summernote').summernote({
    height: 400,
    lang: 'es-ES',
    toolbar: [
      ['style', ['style']],
      ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
      ['fontname', ['fontname']],
      ['fontsize', ['fontsize']],
      ['color', ['color']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['table', ['table']],
      ['insert', ['link', 'picture', 'video']],
      ['view', ['fullscreen', 'codeview']]
    ],
    callbacks: {
      onImageUpload: function(files) {
        for (let i = 0; i < files.length; i++) {
          let file = files[i];
          let editor = $(this);
          
          if (file.size < 500 * 1024) {
            let reader = new FileReader();
            reader.onloadend = function() {
              showImageModal(null, editor, reader.result);
            };
            reader.readAsDataURL(file);
          } else {
            uploadSummernoteImage(file, editor);
          }
        }
      }
    }
  });
  
  $('#imageModal').on('hidden.bs.modal', function() {
    pendingImageFile = null;
    pendingImageEditor = null;
    pendingImageUrl = null;
    pendingImageDataUrl = null;
  });
});

function showImageModal(file, editor, dataUrl) {
  pendingImageFile = file;
  pendingImageEditor = editor;
  pendingImageDataUrl = dataUrl;
  pendingImageUrl = null;
  
  $('#imgAltText').val('');
  $('#imgCaptionText').val('');
  
  new bootstrap.Modal($('#imageModal')).show();
}

$('#insertImageBtn').on('click', function() {
  var alt = $('#imgAltText').val().trim();
  var caption = $('#imgCaptionText').val().trim();
  
  if (pendingImageDataUrl) {
    insertImageWithMetadata(pendingImageDataUrl, alt, caption);
  } else if (pendingImageUrl) {
    insertImageWithMetadata(pendingImageUrl, alt, caption);
  }
  
  bootstrap.Modal.getInstance($('#imageModal')).hide();
});

$('#cancelImageBtn').on('click', function() {
  bootstrap.Modal.getInstance($('#imageModal')).hide();
});

function insertImageWithMetadata(url, alt, caption) {
  var imgTag = '<img src="' + url + '"';
  
  if (alt) {
    imgTag += ' alt="' + alt + '"';
  }
  
  imgTag += ' class="img-fluid"';
  
  if (caption) {
    imgTag += ' /><figure class="mt-2 text-center"><figcaption class="text-muted small">' + caption + '</figcaption></figure>';
  } else {
    imgTag += ' />';
  }
  
  pendingImageEditor.summernote('insertHTML', imgTag);
}

function uploadSummernoteImage(file, editor) {
  if (file.size > 5 * 1024 * 1024) {
    alert('La imagen supera los 5MB permitidos.');
    return;
  }

  var data = new FormData();
  data.append("file", file);
  
  editor.summernote('disable');
  
  $.ajax({
    url: '<?= $url ?>/admin/blog/upload_image.php',
    cache: false,
    contentType: false,
    processData: false,
    data: data,
    type: 'POST',
    success: function(response) {
      if (response.url) {
        showImageModal(null, editor, response.url);
      } else if (response.error) {
        alert('Error: ' + response.error);
      }
    },
    error: function(xhr, status, error) {
      console.error('Error al subir imagen:', error);
      alert('Error al subir la imagen. Por favor, intenta nuevamente.');
    },
    complete: function() {
      editor.summernote('enable');
    }
  });
}
</script>
