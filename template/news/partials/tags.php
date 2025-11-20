<?php

require_once __DIR__ . '/../../../inc/config.php';
// Obtener todos los textos (títulos + contenido) de posts publicados
$stmt = db()->query("
    SELECT CONCAT(title, ' ', content) AS texto
    FROM blog_posts
    WHERE status='published' AND deleted=0
");
$textos = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Unir todo en un solo string
$todoTexto = strtolower(strip_tags(implode(' ', $textos)));

// Quitar caracteres especiales y dividir en palabras
$palabras = preg_split('/\W+/u', $todoTexto, -1, PREG_SPLIT_NO_EMPTY);

// Lista de palabras comunes que no sirven como tag (stopwords en español e inglés)
$stopwords = [
    // Español
    'nbsp','a','acá','ahí','al','algo','algún','alguna','algunas','algunos','allá','allí','ante','antes','aquel',
    'aquella','aquellas','aquellos','aquí','así','aunque','bajo','bien','cada','casi','cierta','ciertas','cierto','ciertos',
    'como','con','contra','cual','cuando','cuanta','cuantas','cuanto','cuantos','cuya','cuyas','cuyo','cuyos',
    'de','del','desde','donde','dos','el','ella','ellas','ello','ellos','en','entre','era','eran','es','esa','esas',
    'ese','eso','esos','esta','estaba','estado','estamos','estan','estar','estas','este','esto','estos','está','están',
    'etc','fue','fueron','ha','había','habían','han','hasta','hay','la','las','le','les','lo','los','luego','me',
    'mi','mis','mientras','muy','más','ni','no','nos','nosotros','nuestra','nuestras','nuestro','nuestros','nunca',
    'o','otra','otras','otro','otros','para','pero','poco','por','porque','primero','puede','que','qué','quien',
    'quienes','se','sea','según','ser','si','sí','sin','sobre','solamente','solo','sólo','son','su','sus','también',
    'tan','tanto','te','tenemos','tener','tengo','ti','tiene','tienen','todo','todos','tras','tu','tus','un','una',
    'uno','unos','usted','ustedes','va','vamos','van','vos','vosotros','y','ya','yo','él','ésta','éstas','éste','éstos',
    'éxito','sino','hasta','ahora','entonces','luego','además','aún','aun','inclusive','según','mediante','durante','cuál',
    'cuáles','dónde','cuándo','cuánto','cuántos','cuántas','qué','quién','quiénes','cómo','será','era','eran','estar',
    'estará','estarán','hubo','hubieron','habrá','habrán','he','hemos','han','habías','habíamos','habíais','habían',
    'mismo','misma','mismos','mismas','otro','otra','otros','otras','propio','propia','propios','propias',
    'algunas','algunos','ninguno','ninguna','ningunos','ningunas','pues','entonces','ya','ahí','aquí','allí','allá',
    'tambien','ademas','mas','aun','asi','donde','cuando','quien','cuyo','cuyos','cuyas','cual','cuales','pues','porque',
    'pues','aunque','mientras','sin','con','contra','según','sobre','entre','desde','hacia','para','por','sobre','tras',
    'yo','tú','él','ella','nosotros','vosotros','ellos','ellas','ustedes','mí','ti','sí','conmigo','contigo','consigo',
    'mi','mis','tu','tus','su','sus','nuestro','nuestra','nuestros','nuestras','vuestro','vuestra','vuestros','vuestras',
    'uno','una','unos','unas','todo','toda','todos','todas','cualquiera','cualesquiera','alguno','alguna','algunos','algunas',
    'ninguno','ninguna','bastante','bastantes','poco','poca','pocos','pocas','mucho','mucha','muchos','muchas','demasiado',
    'demasiada','demasiados','demasiadas','tal','tales','otro','otra','otros','otras','cierto','cierta','ciertos','ciertas',
    'varios','varias','demás','mismo','misma','mismos','mismas','tan','tanto','tanta','tantos','tantas','cuanto','cuanta',
    'cuantos','cuantas','algún','ningún','ninguna','algunos','algunas',

    // Inglés
    'a','about','above','after','again','against','all','am','an','and','any','are','as','at','be','because','been',
    'before','being','below','between','both','but','by','could','did','do','does','doing','down','during','each',
    'few','for','from','further','had','has','have','having','he','her','here','hers','herself','him','himself','his',
    'how','i','if','in','into','is','it','its','itself','just','me','more','most','my','myself','no','nor','not','now',
    'of','off','on','once','only','or','other','our','ours','ourselves','out','over','own','same','she','should','so',
    'some','such','than','that','the','their','theirs','them','themselves','then','there','these','they','this','those',
    'through','to','too','under','until','up','very','was','we','were','what','when','where','which','while','who','whom',
    'why','will','with','you','your','yours','yourself','yourselves'
];

// Contar frecuencia de palabras
$frecuencias = [];
foreach ($palabras as $pal) {
    if (mb_strlen($pal) > 3 && !in_array($pal, $stopwords)) {
        $frecuencias[$pal] = ($frecuencias[$pal] ?? 0) + 1;
    }
}

// Ordenar por frecuencia
arsort($frecuencias);

// Tomar las 12 más repetidas
$tags = array_slice(array_keys($frecuencias), 0, 12);
?>

<div class="col-lg-3 col-md-6 mb-5">
    <h4 class="font-weight-bold mb-4">Tags</h4>
    <div class="d-flex flex-wrap m-n1">
        <?php foreach ($tags as $tag): ?>
            <a href="<?= URLBASE ?>/buscar/<?= urlencode($tag) ?>/" 
               class="btn btn-sm btn-outline-secondary m-1">
                <?= htmlspecialchars(ucfirst($tag)) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
