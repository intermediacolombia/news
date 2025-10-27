<?php
/**********************************************
 * Widget del clima dinámico con Open-Meteo
 * Autor: Edisson Medina Bedoya
 **********************************************/

// === CONFIGURACIÓN ===
$cityName = "Armenia"; // cambia tu ciudad
$latitude  = 4.5339;   // latitud de Armenia, Quindío
$longitude = -75.6811; // longitud de Armenia, Quindío

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

// === Asignar ícono según código del clima ===
function getWeatherIcon($code) {
    $icons = [
        0 => "sunny.png",      // Soleado
        1 => "partly.png",     // Parcialmente nublado
        2 => "cloudy.png",     // Nublado
        3 => "overcast.png",   // Cubierto
        45 => "fog.png",       // Niebla
        48 => "fog.png",
        51 => "drizzle.png",   // Llovizna
        61 => "rain.png",      // Lluvia ligera
        63 => "rain.png",      // Lluvia moderada
        65 => "rain.png",      // Lluvia fuerte
        71 => "snow.png",      // Nieve ligera
        95 => "storm.png",     // Tormenta
    ];
    return "img/weather/" . ($icons[$code] ?? "default.png");
}

$weather_icon_url = getWeatherIcon($weatherCode);
?>

<!-- ===========================
     Bloque visual del clima
     =========================== -->
<style>
.weather-widget {
  display: flex;
  align-items: center;
  gap: 10px;
}

.weather-widget img {
  width: 55px;
  height: 55px;
  object-fit: contain;
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

<div class="weather-widget">
  <img src="<?= htmlspecialchars($weather_icon_url) ?>" alt="Clima">
  <div class="d-flex align-items-center">
    <strong><?= htmlspecialchars($temp) ?>°C</strong>
    <div class="location">
      <span><?= htmlspecialchars($cityName) ?>,</span>
      <small><?= htmlspecialchars($date) ?></small>
    </div>
  </div>
</div>
