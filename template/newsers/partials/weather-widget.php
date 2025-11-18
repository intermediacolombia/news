<?php
/**********************************************
 * Widget del clima dinámico con Open-Meteo
 * Autor: Edisson Medina Bedoya
 **********************************************/

// === Si el visitante permite geolocalización, usar sus coordenadas ===
if (isset($_GET['lat']) && isset($_GET['lon'])) {
  $latitude  = floatval($_GET['lat']);
  $longitude = floatval($_GET['lon']);

  // Obtener ciudad aproximada
  $geoUrl = "https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=$latitude&longitude=$longitude&localityLanguage=es";
  $geoResponse = @file_get_contents($geoUrl);
  if ($geoResponse) {
    $geo = json_decode($geoResponse, true);
    $cityName = $geo['city'] ?? $geo['locality'] ?? 'Ubicación actual';
  } else {
    $cityName = "Ubicación actual";
  }
} else {
  // === CONFIGURACIÓN PREDETERMINADA ===
    $cityName = "Bogotá";
  $latitude  = 4.7110;
  $longitude = -74.0721;
}

// === Consultar la API ===
$apiUrl = "https://api.open-meteo.com/v1/forecast?latitude=$latitude&longitude=$longitude&current_weather=true&timezone=auto";
$response = @file_get_contents($apiUrl);

if ($response) {
    $data = json_decode($response, true);
    $temp = round($data['current_weather']['temperature']);
    $weatherCode = $data['current_weather']['weathercode'];
    $date = date("D. d M Y", strtotime($data['current_weather']['time']));
} else {
    $temp = "--";
    $weatherCode = null;
    $date = date("D. d M Y");
}

// === Asignar ícono SVG según código del clima ===
function getWeatherIconSVG($code) {
    $icons = [
        0 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>', // Soleado
        
        1 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="M20 12h2"/><path d="m19.07 4.93-1.41 1.41"/><path d="M15.947 12.65a4 4 0 0 0-5.925-4.128"/><path d="M13 22H7a5 5 0 1 1 4.9-6H13a3 3 0 0 1 0 6Z"/></svg>', // Parcialmente nublado
        
        2 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg>', // Nublado
        
        3 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 21H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="M22 10a3 3 0 0 0-3-3h-2.207a5.502 5.502 0 0 0-10.702.5"/></svg>', // Cubierto
        
        45 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M16 17H7"/><path d="M17 21H9"/></svg>', // Niebla
        
        48 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M16 17H7"/><path d="M17 21H9"/></svg>', // Niebla
        
        51 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="M16 14v2"/><path d="M8 14v2"/><path d="M12 16v2"/></svg>', // Llovizna
        
        61 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="M16 14v6"/><path d="M8 14v6"/><path d="M12 14v6"/></svg>', // Lluvia ligera
        
        63 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="M16 14v6"/><path d="M8 14v6"/><path d="M12 14v6"/><path d="M20 14v6"/><path d="M4 14v6"/></svg>', // Lluvia moderada
        
        65 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="M16 14v6"/><path d="M8 14v6"/><path d="M12 14v6"/><path d="M20 14v6"/><path d="M4 14v6"/></svg>', // Lluvia fuerte
        
        71 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="M10 13.5l-2 3"/><path d="M16 13.5l-2 3"/><path d="M13 16.5l-1 1.5"/></svg>', // Nieve
        
        95 => '<svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="M19 16l-2 3"/><path d="M13 16l-2 3"/><path d="M7 16l-2 3"/></svg>', // Tormenta
    ];
    
    return $icons[$code] ?? $icons[0]; // Por defecto, soleado
}

$weather_icon_svg = getWeatherIconSVG($weatherCode);
?>

<!-- ===========================
     Bloque visual del clima
     =========================== -->
<style>
.weather-widget {
  display: flex;
  align-items: center;
  gap: 10px;
	margin-right: 20px;
}
.weather-widget .weather-icon {
  color: #FFA500;
  flex-shrink: 0;
}
.weather-widget strong {
  font-size: 1.6rem;
  color: #555;
}
.weather-widget .location {
  display: flex;
  flex-direction: column;
  margin-left: 10px;
}
.weather-widget .location span {
  font-weight: 500;
  color: #333;
  text-transform: uppercase;
}
.weather-widget .location small {
  font-size: 0.85rem;
  color: #777;
}
</style>
<div class="d-flex">
<div class="weather-widget">
  <div class="weather-icon">
    <?= $weather_icon_svg ?>
  </div>
  <div class="d-flex align-items-center">
    <strong><?= htmlspecialchars($temp) ?>°C</strong>
    <div class="location">
      <span><?= htmlspecialchars($cityName) ?>,</span>
      <small><?= htmlspecialchars($date) ?></small>
    </div>
  </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(pos) {
      const lat = pos.coords.latitude;
      const lon = pos.coords.longitude;

      // Recargar el widget con coordenadas
      fetch('<?= URLBASE ?>/template/newsers/partials/<?= basename(__FILE__) ?>?lat=' + lat + '&lon=' + lon)
        .then(r => r.text())
        .then(html => {
          document.querySelector('.weather-widget').outerHTML =
            new DOMParser().parseFromString(html, 'text/html').querySelector('.weather-widget').outerHTML;
        })
        .catch(console.error);
    });
  }
});
</script>
