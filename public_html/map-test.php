<?php

/**
 * Project: Web Map (Leaflet / QGIS-friendly)
 * Framework: n/a map test
 * File: public_html/map-test.php
 * Date: 2026-01-15
 * Copyright: (c) Your Org
 */

declare(strict_types=1);


$geojson_url = "https://downloads.globalincidentmap.com/esri/index.php?api=CDA-1-CVB-CSZ-EOF&token=zkXxJkhg6ww6u7Jd";

 


function fetch_geojson_via_curl(string $url, int $timeoutSeconds = 8, int $maxBytes = 2_000_000): array
{
    // Basic URL validation (tighten this with an allow-list in production)
    $parts = parse_url($url);
    if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
        return ['ok' => false, 'error' => 'Invalid URL'];
    }

    $scheme = strtolower((string)$parts['scheme']);
    if (!in_array($scheme, ['http', 'https'], true)) {
        return ['ok' => false, 'error' => 'URL must be http or https'];
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'error' => 'cURL init failed'];
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT        => $timeoutSeconds,
        CURLOPT_USERAGENT      => 'GeoJSONMap/1.0',
        CURLOPT_HTTPHEADER     => ['Accept: application/geo+json, application/json;q=0.9, */*;q=0.1'],
    ]);

    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'error' => 'cURL error: ' . $err];
    }

    if ($code < 200 || $code >= 300) {
        return ['ok' => false, 'error' => 'HTTP error: ' . $code];
    }

    if (strlen($raw) > $maxBytes) {
        return ['ok' => false, 'error' => 'GeoJSON too large'];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || empty($decoded['type'])) {
        return ['ok' => false, 'error' => 'Invalid JSON/GeoJSON'];
    }

    return ['ok' => true, 'data' => $decoded];
}

$geojsonUrl = $geojson_url;
$geojsonData = null;
$loadError = '';

// Fallback sample (your provided item, shortened to one feature for demo)
$sampleGeojson = [
    "type" => "FeatureCollection",
    "features" => [
        [
            "type" => "Feature",
            "properties" => [
                "track_id" => "12910997",
                "title" => "Minnesota - Shooting Involving Federal Law Enforcement In North Minneapolis",
                "event_gmt_time" => "2026-01-15 02:40:05",
                "event_type" => "Shootings Sniper Incidents Etc",
                "country" => "United States",
                "location" => "North Lyndale Avenue, Minneapolis, MN, USA",
                "severity" => "Severe",
                "infrastructure" => "Unknown",
                "url" => "https://www.5newsonline.com/article/news/local/reports-shooting-involving-federal-law-enforcement-in-north-minneapolis/89-f0dc3033-f01c-4870-961a-369dc16bde21",
                "description" => "MINNESOTA - Shooting involving federal law enforcement in north Minneapolis",
                "latitude" => "45.0128230",
                "longitude" => "-93.2880230",
                "granularity" => "Address (geocoded)",
                "icon" => "https://img.globalincidentmap.com/assets/icons/shooting.gif",
            ],
            "geometry" => [
                "type" => "Point",
                "coordinates" => [-93.2880230, 45.0128230],
            ],
        ],
    ],
];

if ($geojsonUrl !== '') {
    $res = fetch_geojson_via_curl($geojsonUrl);
    if ($res['ok'] === true) {
        $geojsonData = $res['data'];
    } else {
        $loadError = (string)$res['error'];
        $geojsonData = $sampleGeojson; // still show a map
    }
} else {
    $geojsonData = $sampleGeojson;
}

header('Content-Type: text/html; charset=utf-8');

$geojsonJson = json_encode($geojsonData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$geojsonUrlEsc = htmlspecialchars($geojsonUrl, ENT_QUOTES, 'UTF-8');
$loadErrorEsc = htmlspecialchars($loadError, ENT_QUOTES, 'UTF-8');

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>GeoJSON Map</title>

  <!-- Leaflet core -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

  <!-- Plugins -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet.locatecontrol@0.81.0/dist/L.Control.Locate.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet-measure@3.3.1/dist/leaflet-measure.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet-minimap@3.6.1/dist/Control.MiniMap.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet-mouse-position@1.0.2/L.Control.MousePosition.css">

  <style>
    html, body { height: 100%; margin: 0; background: #0b0f14; color: #e7eef7; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
    #map { height: 100vh; width: 100%; }

    .topbar {
      position: fixed; z-index: 9999; top: 12px; left: 12px; right: 12px;
      display: flex; gap: 12px; align-items: center; justify-content: space-between;
      pointer-events: none;
    }

    .panel {
      pointer-events: auto;
      background: rgba(15, 18, 24, 0.86);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 10px;
      padding: 10px 12px;
      backdrop-filter: blur(6px);
    }

    .panel label { font-size: 12px; opacity: 0.9; display: block; margin-bottom: 6px; }
    .panel input {
      width: min(820px, 68vw);
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid rgba(255,255,255,0.18);
      background: rgba(0,0,0,0.25);
      color: #e7eef7;
    }

    .panel button {
      margin-left: 8px;
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid rgba(255,255,255,0.18);
      background: rgba(255,255,255,0.06);
      color: #e7eef7;
      cursor: pointer;
    }

    .err {
      margin-top: 8px;
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid rgba(255, 100, 100, 0.35);
      background: rgba(255, 80, 80, 0.10);
      font-size: 12px;
      max-width: 68vw;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .legend {
      background: rgba(15, 18, 24, 0.86);
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid rgba(255,255,255,0.12);
      color: #e7eef7;
      line-height: 1.2;
      max-width: 320px;
    }

    .legend h4 { margin: 0 0 8px; font-size: 13px; font-weight: 600; }
    .legend .row { display: flex; align-items: center; gap: 8px; margin: 6px 0; font-size: 12px; }
    .legend .swatch { width: 12px; height: 12px; border-radius: 3px; border: 1px solid rgba(255,255,255,0.18); }
    .popup h3 { margin: 0 0 6px; font-size: 14px; }
    .popup .k { opacity: 0.8; font-size: 12px; }
    .popup .v { font-size: 12px; margin-bottom: 6px; }
    .popup a { color: #8dc4ff; text-decoration: none; }
    .popup a:hover { text-decoration: underline; }
  </style>
</head>

<body>
  

  <div id="map"></div>

  <!-- Leaflet core -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <!-- Plugins -->
  <script src="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.js"></script>
  <script src="https://unpkg.com/leaflet.locatecontrol@0.81.0/dist/L.Control.Locate.min.js"></script>
  <script src="https://unpkg.com/leaflet-measure@3.3.1/dist/leaflet-measure.js"></script>
  <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
  <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
  <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
  <script src="https://unpkg.com/leaflet-minimap@3.6.1/dist/Control.MiniMap.min.js"></script>
  <script src="https://unpkg.com/leaflet-mouse-position@1.0.2/L.Control.MousePosition.js"></script>

  <script>
    const GEOJSON = <?= $geojsonJson ?>;

    // Basemaps
    const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    });

    const osmHot = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors, Tiles style by HOT'
    });

    const opentopo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
      maxZoom: 17,
      attribution: '&copy; OpenTopoMap contributors'
    });

    const map = L.map('map', {
      layers: [osm],
      fullscreenControl: true
    }).setView([39.5, -98.35], 4);

    // Layer control (basemap selector)
    const baseLayers = {
      "OpenStreetMap": osm,
      "OSM Humanitarian": osmHot,
      "OpenTopoMap": opentopo
    };

    // Marker cluster group
    const clusters = L.markerClusterGroup({
      showCoverageOnHover: false,
      spiderfyDistanceMultiplier: 1.4
    });

    // Color ramp by event_type (extend as needed)
    const typeColors = new Map();
    const palette = [
      '#ff5a5f', '#ffd166', '#06d6a0', '#4dabf7', '#b197fc',
      '#ffa8a8', '#74c0fc', '#8ce99a', '#fcc2d7', '#ffe066'
    ];
    let paletteIndex = 0;

    function colorForType(t) {
      const key = (t || 'Unknown').toString();
      if (!typeColors.has(key)) {
        typeColors.set(key, palette[paletteIndex % palette.length]);
        paletteIndex++;
      }
      return typeColors.get(key);
    }

    function escapeHtml(s) {
      return (s ?? '').toString()
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }

    function popupHtml(props) {
      const title = props?.title ? escapeHtml(props.title) : 'Feature';
      const url = props?.url ? props.url.toString() : '';
      const when = props?.event_gmt_time ? escapeHtml(props.event_gmt_time) : '';
      const loc = props?.location ? escapeHtml(props.location) : '';
      const sev = props?.severity ? escapeHtml(props.severity) : '';
      const et  = props?.event_type ? escapeHtml(props.event_type) : '';
      const desc = props?.description ? escapeHtml(props.description).replaceAll('\n','<br>') : '';

      let linkHtml = '';
      if (url) {
        const safeUrl = escapeHtml(url);
        linkHtml = `<div class="v"><a href="${safeUrl}" target="_blank" rel="noopener">Open source</a></div>`;
      }

      return `
        <div class="popup">
          <h3>${title}</h3>
          ${when ? `<div class="k">Time (GMT)</div><div class="v">${when}</div>` : ``}
          ${et ? `<div class="k">Type</div><div class="v">${et}</div>` : ``}
          ${sev ? `<div class="k">Severity</div><div class="v">${sev}</div>` : ``}
          ${loc ? `<div class="k">Location</div><div class="v">${loc}</div>` : ``}
          ${linkHtml}
          ${desc ? `<div class="k">Description</div><div class="v">${desc}</div>` : ``}
        </div>
      `;
    }

    // Feature styling
    function styleFeature(feature) {
      const props = feature?.properties || {};
      const c = colorForType(props.event_type || 'Unknown');
      return {
        color: c,
        weight: 2,
        opacity: 0.9,
        fillColor: c,
        fillOpacity: 0.25
      };
    }

    // Point-to-layer with per-feature icon support
    function pointToLayer(feature, latlng) {
      const props = feature?.properties || {};
      const iconUrl = props?.icon ? props.icon.toString() : '';

      if (iconUrl) {
        const icon = L.icon({
          iconUrl: iconUrl,
          iconSize: [24, 24],
          iconAnchor: [12, 12],
          popupAnchor: [0, -10]
        });
        return L.marker(latlng, { icon });
      }

      // Fallback: colored circle marker
      const c = colorForType(props.event_type || 'Unknown');
      return L.circleMarker(latlng, {
        radius: 7,
        color: c,
        weight: 2,
        fillColor: c,
        fillOpacity: 0.85
      });
    }

    const geoLayer = L.geoJSON(GEOJSON, {
      style: styleFeature,
      pointToLayer: pointToLayer,
      onEachFeature: (feature, layer) => {
        layer.bindPopup(popupHtml(feature.properties || {}), { maxWidth: 360 });

        // Optional hover highlight (non-markers)
        if (layer.setStyle) {
          layer.on('mouseover', () => layer.setStyle({ weight: 4, fillOpacity: 0.35 }));
          layer.on('mouseout', () => layer.setStyle(styleFeature(feature)));
        }
      }
    });

    // Add features into clusters where appropriate
    geoLayer.eachLayer(l => {
      if (l instanceof L.Marker) {
        clusters.addLayer(l);
      }
    });

    // Keep non-point geometries separate so they are not clustered
    const nonPointGroup = L.layerGroup();
    geoLayer.eachLayer(l => {
      if (!(l instanceof L.Marker)) {
        nonPointGroup.addLayer(l);
      }
    });

    clusters.addTo(map);
    nonPointGroup.addTo(map);

    // Overlay controls
    const overlays = {
      "Incidents (clustered points)": clusters,
      "Incidents (lines/polygons)": nonPointGroup
    };

    L.control.layers(baseLayers, overlays, { collapsed: false }).addTo(map);

    // Scale
    L.control.scale({ imperial: true, metric: true }).addTo(map);

    // Locate
    L.control.locate({
      position: 'topleft',
      flyTo: true,
      keepCurrentZoomLevel: false,
      strings: { title: 'Show my location' }
    }).addTo(map);

    // Measure
    L.control.measure({
      position: 'topleft',
      primaryLengthUnit: 'kilometers',
      secondaryLengthUnit: 'miles',
      primaryAreaUnit: 'sqmeters',
      secondaryAreaUnit: 'acres'
    }).addTo(map);

    // Draw (store drawn items locally)
    const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    const drawControl = new L.Control.Draw({
      position: 'topleft',
      edit: { featureGroup: drawnItems },
      draw: {
        circle: false,
        circlemarker: false
      }
    });
    map.addControl(drawControl);

    map.on(L.Draw.Event.CREATED, (e) => {
      drawnItems.addLayer(e.layer);
    });

    // Geocoder
    L.Control.geocoder({
      defaultMarkGeocode: true
    }).addTo(map);

    // MiniMap
    const mini = new L.Control.MiniMap(osm, { toggleDisplay: true, minimized: true }).addTo(map);

    // Mouse position
    L.control.mousePosition({
      position: 'bottomleft',
      separator: ' | ',
      numDigits: 6
    }).addTo(map);

    // Legend (auto from observed event types)
    const legend = L.control({ position: 'bottomright' });

    legend.onAdd = function () {
      const div = L.DomUtil.create('div', 'legend');
      div.innerHTML = `<h4>Legend (event_type)</h4><div id="legendRows"></div>`;
      return div;
    };

    legend.addTo(map);

    function renderLegend() {
      const holder = document.getElementById('legendRows');
      if (!holder) return;

      const keys = Array.from(typeColors.keys()).sort((a,b)=>a.localeCompare(b));
      if (keys.length === 0) {
        holder.innerHTML = `<div class="row"><span style="opacity:.8; font-size:12px;">No categories yet.</span></div>`;
        return;
      }

      holder.innerHTML = keys.map(k => {
        const c = typeColors.get(k);
        return `<div class="row"><span class="swatch" style="background:${c}"></span><span>${escapeHtml(k)}</span></div>`;
      }).join('');
    }

    // Fit map to data
    const bounds = geoLayer.getBounds();
    if (bounds && bounds.isValid()) {
      map.fitBounds(bounds.pad(0.15));
    }

    // Ensure legend reflects used categories
    renderLegend();
  </script>
</body>
</html>
