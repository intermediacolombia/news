<?php
// generate_audio.php
header('Content-Type: audio/mpeg');
header('Cache-Control: public, max-age=86400'); // Cache por 24 horas

$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$post_id) {
    http_response_code(400);
    exit('ID inválido');
}

// Obtener el post de tu base de datos
// Ejemplo:
// $post = obtenerPost($post_id);
// $text = $post['title'] . '. ' . strip_tags($post['content']);

// Para este ejemplo, simulamos el texto
$text = "Título del artículo. Contenido del artículo aquí...";

// Usar Google Translate TTS (método simple sin librerías)
$text_encoded = urlencode($text);
$audio_url = "https://translate.google.com/translate_tts?ie=UTF-8&tl=es&client=tw-ob&q=" . $text_encoded;

// Descargar y servir el audio
$audio_content = file_get_contents($audio_url);

if ($audio_content) {
    echo $audio_content;
} else {
    http_response_code(500);
    exit('Error al generar audio');
}