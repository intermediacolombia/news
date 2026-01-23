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
</style>

<script>
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
          let editor = $(this); // FIX: Guardar referencia correcta
          
          // Si la imagen es pequeña (<500KB), usar Base64
          if (file.size < 500 * 1024) {
            let reader = new FileReader();
            reader.onloadend = function() {
              editor.summernote('insertImage', reader.result); // FIX: Usar editor guardado
            };
            reader.readAsDataURL(file);
          } else {
            // Si es grande, subir al servidor
            uploadSummernoteImage(file, editor);
          }
        }
      }
    }
  });
});

function uploadSummernoteImage(file, editor) {
  // Validar tamaño
  if (file.size > 5 * 1024 * 1024) {
    alert('La imagen supera los 5MB permitidos.');
    return;
  }

  var data = new FormData();
  data.append("file", file);
  
  // Mostrar indicador de carga
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
        editor.summernote('insertImage', response.url);
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
