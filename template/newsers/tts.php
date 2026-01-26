<?php
// tts.php
if (isset($_GET['text'])) {
    $text = strip_tags($_GET['text']);
    $text = urlencode(substr($text, 0, 1000)); // Limitamos a 1000 caracteres
    
    // Google TTS URL
    $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl=es-ES&client=tw-ob&q=" . $text;

    // Configuramos las cabeceras para que el navegador lo reconozca como audio
    header('Content-Type: audio/mpeg');
    header('Cache-Control: no-cache');
    
    // Usamos file_get_contents para traer el audio desde Google a nuestro servidor
    echo file_get_contents($url);
    exit;
}