<script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs4.min.js"></script>	
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-es-ES.min.js"></script>
	<style>
/* Ocultar botón de ayuda de Summernote */
.note-editor .note-btn[data-event="help"] {
  display: none !important;
}

	</style>

<script>
$(document).ready(function() {
  $('.summernote').summernote({
    height: 300,
    lang: 'es-ES',
    toolbar: [
      ['style', ['style']],
      ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
      ['fontname', ['fontname']],
      ['fontsize', ['fontsize']],
      ['color', ['color']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['table', ['table']],
      ['insert', ['link', 'picture', 'video']],
      ['view', ['fullscreen', 'codeview']]
    ],
    fontNames: [
      'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New',
      'Segoe UI', 'Tahoma', 'Times New Roman', 'Verdana'
    ],
    fontSizes: [
      '8', '9', '10', '11', '12', '14', '16', '18',
      '20', '24', '28', '32', '36', '48', '64'
    ]
  });

  // Parchear los botones de cerrar para Bootstrap 5
  $(document).on('shown.bs.modal', function() {
    $('.note-editor .modal .close')
      .attr('data-bs-dismiss', 'modal')   // lo que usa Bootstrap 5
      .removeAttr('data-dismiss');        // quitar lo viejo
  });
});
</script>
