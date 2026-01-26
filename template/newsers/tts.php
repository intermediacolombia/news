<?php
// tts.php
if (isset($_GET['text'])) {
    $text = $_GET['text'];
    // Limpiamos el texto y limitamos longitud
    $text = strip_tags($text);
    $text = mb_substr($text, 0, 250, 'UTF-8'); 
    
    $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl=es-ES&client=tw-ob&q=" . urlencode($text);

    header('Content-Type: audio/mpeg');
    // Esto descarga el audio desde Google y lo entrega a tu navegador
    readfile($url);
    exit;
}