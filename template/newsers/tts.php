<?php
// tts.php
if (isset($_GET['text'])) {
    $text = strip_tags($_GET['text']);
    $text = mb_substr($text, 0, 250, 'UTF-8');
    $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl=es-ES&client=tw-ob&q=" . urlencode($text);

    header('Content-Type: audio/mpeg');
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // Que lo imprima directamente
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0'); // Engañar a Google para que crea que es un navegador
    curl_exec($ch);
    curl_close($ch);
    exit;
}