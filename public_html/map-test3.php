<?php
declare(strict_types=1);

/**
 * Project: Web Map (MapLibre 3D Terrain + 3D Buildings) TEST
 * Framework: n/a map test
 * File: public_html/map-test3.php
 * Date: 2026-01-15
 * Copyright: (c) Your Org
 */


$geojson_url = "https://downloads.globalincidentmap.com/esri/index.php?api=CDA-1-CVB-CSZ-EOF&token=zkXxJkhg6ww6u7Jd";

/*
  OPTIONAL KEYS (leave blank for tests):

  - Terrain DEM (global): MapTiler key recommended for real terrain mesh.
  - Buildings (vector tiles w/ heights): use your own OpenMapTiles server or a provider key.

  If keys are blank:
    - You still get pitch/rotate (2.5D feel),
    - GeoJSON renders,
    - Terrain + buildings are disabled.
*/

$maptiler_key = ""; // e.g. "YOUR_MAPTILER_KEY" to enable terrain
$stadia_key   = ""; // e.g. "YOUR_STADIA_KEY" to attempt 3D buildings with an OpenMapTiles-compatible style

/*
  Usage:
    /map-test3.php
    /map-test3.php?geojson_url=https%3A%2F%2Fexample.com%2Fdata.geojson
*/

function fetch_geojson_via_curl(string $url, int $timeoutSeconds = 8, int $maxBytes = 2_000_000): array
{
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
        CURLOPT_USERAGENT      => 'GeoJSONMapLibre3DTest/1.0',
        CURLOPT_HTTPHEADER     => ['Accept: application/geo+json, application/json;q=0.9, */*;q=0.1'],
    ]);

    $raw  = curl_exec($ch);
    $err  = curl_error($ch);
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

// Default is internal $geojson_url.
// Override ONLY if you manually pass ?geojson_url=... (no form/UI shown on page).
$geojsonUrl = $geojson_url;
if (isset($_GET['geojson_url']) && is_string($_GET['geojson_url'])) {
    $candidate = trim($_GET['geojson_url']);
    if ($candidate !== '') {
        $geojsonUrl = $candidate;
    }
}

$geojsonData = null;
$loadError = '';

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
        $geojsonData = $sampleGeojson;
    }
} else {
    $geojsonData = $sampleGeojson;
}

header('Content-Type: text/html; charset=utf-8');

$geojsonJson = json_encode($geojsonData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($geojsonJson === false) {
    $geojsonJson = json_encode($sampleGeojson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $loadError = $loadError !== '' ? $loadError : 'json_encode failed; using sample data';
}

$loadErrorEsc = htmlspecialchars($loadError, ENT_QUOTES, 'UTF-8');
$maptilerKeyEsc = htmlspecialchars($maptiler_key, ENT_QUOTES, 'UTF-8');
$stadiaKeyEsc   = htmlspecialchars($stadia_key, ENT_QUOTES, 'UTF-8');

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>3D Terrain Test (Flat Map) + Per-Feature Icons</title>

  <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@5.16.0/dist/maplibre-gl.css">

  <style>
    html, body { height: 100%; margin: 0; background: #0b0f14; color: #e7eef7; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
    #map { height: 100vh; width: 100%; }

    .hud {
      position: fixed; z-index: 9999; left: 12px; top: 12px;
      background: rgba(15, 18, 24, 0.86);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 10px;
      padding: 10px 12px;
      backdrop-filter: blur(6px);
      max-width: min(620px, 90vw);
      pointer-events: none;
      font-size: 12px;
      opacity: 0.92;
    }

    .hud .err {
      margin-top: 8px;
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid rgba(255, 100, 100, 0.35);
      background: rgba(255, 80, 80, 0.10);
      font-size: 12px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .legend {
      position: fixed;
      right: 12px;
      bottom: 12px;
      z-index: 9999;
      background: rgba(15, 18, 24, 0.86);
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid rgba(255,255,255,0.12);
      color: #e7eef7;
      line-height: 1.2;
      max-width: 320px;
      pointer-events: none;
    }

    .legend h4 { margin: 0 0 8px; font-size: 13px; font-weight: 600; }
    .legend .row { display: flex; align-items: center; gap: 8px; margin: 6px 0; font-size: 12px; }
    .legend .swatch { width: 12px; height: 12px; border-radius: 3px; border: 1px solid rgba(255,255,255,0.18); }

    .maplibregl-popup-content {
      background: rgba(15, 18, 24, 0.92);
      color: #e7eef7;
      border: 1px solid rgba(255,255,255,0.14);
      border-radius: 10px;
      box-shadow: none;
    }
    .maplibregl-popup-tip { border-top-color: rgba(15, 18, 24, 0.92) !important; }
    .popup h3 { margin: 0 0 6px; font-size: 14px; }
    .popup .k { opacity: 0.8; font-size: 12px; }
    .popup .v { font-size: 12px; margin-bottom: 6px; }
    .popup a { color: #8dc4ff; text-decoration: none; }
    .popup a:hover { text-decoration: underline; }
  </style>
</head>

<body>
  <div class="hud">
    <div>Philadelphia test view • drag = pan • right-drag = rotate • ctrl+drag = pitch • scroll = zoom</div>
    <div>Terrain: <?= $maptiler_key !== '' ? 'ON' : 'OFF' ?> • Buildings: <?= $stadia_key !== '' ? 'ON' : 'OFF' ?> • Icons: per-feature</div>
    <?php if ($loadErrorEsc !== ''): ?>
      <div class="err">GeoJSON load error (showing sample): <?= $loadErrorEsc ?></div>
    <?php endif; ?>
  </div>

  <div id="map"></div>

  <div class="legend">
    <h4>Legend (event_type)</h4>
    <div id="legendRows"></div>
  </div>

  <script src="https://unpkg.com/maplibre-gl@5.16.0/dist/maplibre-gl.js"></script>

  <script>
    const GEOJSON = <?= $geojsonJson ?>;
    const MAPTILER_KEY = "<?= $maptilerKeyEsc ?>";
    const STADIA_KEY   = "<?= $stadiaKeyEsc ?>";

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

    // Legend colors
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

    function renderLegend() {
      const holder = document.getElementById('legendRows');
      if (!holder) return;

      const keys = Array.from(typeColors.keys()).sort((a,b)=>a.localeCompare(b));
      holder.innerHTML = keys.length
        ? keys.map(k => {
            const c = typeColors.get(k);
            return `<div class="row"><span class="swatch" style="background:${c}"></span><span>${escapeHtml(k)}</span></div>`;
          }).join('')
        : `<div class="row"><span style="opacity:.8; font-size:12px;">No categories yet.</span></div>`;
    }

    // Style URLs (no input UI)
    const openFreeMapStyle = "https://tiles.openfreemap.org/styles/liberty";

    // If you provide a Stadia key, use their style JSON (often OpenMapTiles schema incl. building).
    const stadiaStyle = STADIA_KEY
      ? ("https://tiles.stadiamaps.com/styles/alidade_smooth_dark.json?api_key=" + encodeURIComponent(STADIA_KEY))
      : null;

    const styleUrl = stadiaStyle || openFreeMapStyle;

    // Philadelphia start view
    const startCenter = [-75.1652, 39.9526];

    const map = new maplibregl.Map({
      container: 'map',
      style: styleUrl,
      center: startCenter,
      zoom: 12.8,
      pitch: 62,
      bearing: -18,
      antialias: true
    });

    map.addControl(new maplibregl.NavigationControl({ visualizePitch: true }), 'top-left');
    map.addControl(new maplibregl.FullscreenControl(), 'top-left');
    map.addControl(new maplibregl.ScaleControl({ maxWidth: 120, unit: 'imperial' }), 'bottom-left');

    function safeIdFromUrl(url) {
      // Stable, maplibre-safe id from URL
      return 'icon_' + btoa(url).replaceAll('=', '').replaceAll('+', '-').replaceAll('/', '_');
    }

    function loadIcon(iconUrl) {
      const id = safeIdFromUrl(iconUrl);
      if (map.hasImage(id)) return Promise.resolve(id);

      return new Promise((resolve) => {
        map.loadImage(iconUrl, (err, img) => {
          if (err || !img) { resolve(null); return; }
          try {
            map.addImage(id, img, { sdf: false });
            resolve(id);
          } catch (e) {
            resolve(null);
          }
        });
      });
    }

    function firstSymbolLayerId() {
      const layers = map.getStyle()?.layers || [];
      for (const l of layers) {
        if (l.type === 'symbol') return l.id;
      }
      return undefined;
    }

    function enableTerrainIfConfigured() {
      if (!MAPTILER_KEY) return;

      map.addSource('terrain-dem', {
        type: 'raster-dem',
        url: 'https://api.maptiler.com/tiles/terrain-rgb/tiles.json?key=' + encodeURIComponent(MAPTILER_KEY),
        tileSize: 256
      });

      map.setTerrain({ source: 'terrain-dem', exaggeration: 1.35 });

      const beforeId = firstSymbolLayerId();
      map.addLayer({
        id: 'hillshade',
        type: 'hillshade',
        source: 'terrain-dem',
        paint: { 'hillshade-exaggeration': 0.35 }
      }, beforeId);
    }

    function enable3dBuildingsIfPossible() {
      if (!STADIA_KEY) return;

      const style = map.getStyle();
      if (!style || !style.sources) return;

      const sourceId = Object.keys(style.sources).find(k => style.sources[k].type === 'vector') || null;
      if (!sourceId) return;

      const beforeId = firstSymbolLayerId();

      map.addLayer({
        id: '3d-buildings',
        source: sourceId,
        'source-layer': 'building',
        type: 'fill-extrusion',
        minzoom: 14,
        filter: ['==', ['get', 'extrude'], 'true'],
        paint: {
          'fill-extrusion-color': '#6aa9ff',
          'fill-extrusion-opacity': 0.55,
          'fill-extrusion-height': [
            'coalesce',
            ['get', 'render_height'],
            ['get', 'height'],
            12
          ],
          'fill-extrusion-base': [
            'coalesce',
            ['get', 'render_min_height'],
            ['get', 'min_height'],
            0
          ]
        }
      }, beforeId || undefined);
    }

    async function addGeojsonLayersWithPerFeatureIcons() {
      map.addSource('incidents', { type: 'geojson', data: GEOJSON });

      const feats = Array.isArray(GEOJSON?.features) ? GEOJSON.features : [];
      for (const f of feats) {
        colorForType(f?.properties?.event_type || 'Unknown');
      }
      renderLegend();

      // Base circle layer fallback (used when no icon or icon fails to load)
      map.addLayer({
        id: 'incidents-circles',
        type: 'circle',
        source: 'incidents',
        filter: ['==', ['geometry-type'], 'Point'],
        paint: {
          'circle-radius': 6,
          'circle-opacity': 0.9,
          'circle-stroke-width': 2,
          'circle-stroke-color': '#0b0f14',
          'circle-color': [
            'case',
            ['has', 'event_type'],
            ['match', ['get', 'event_type'],
              ...Array.from(typeColors.entries()).flatMap(([k,v]) => [k, v]),
              '#ffd166'
            ],
            '#ffd166'
          ]
        }
      });

      // --- PER-FEATURE ICONS ---
      // Collect unique icon URLs (bounded for safety/perf).
      const iconUrls = [];
      const seen = new Set();
      const MAX_UNIQUE_ICONS = 80;

      for (const f of feats) {
        const icon = (f?.properties?.icon || '').toString().trim();
        if (!icon) continue;
        if (seen.has(icon)) continue;
        seen.add(icon);
        iconUrls.push(icon);
        if (iconUrls.length >= MAX_UNIQUE_ICONS) break;
      }

      if (iconUrls.length === 0) {
        return; // circles only
      }

      // Load icons and build a match expression mapping icon URL -> image id
      const mappingPairs = [];

      for (const url of iconUrls) {
        const imgId = await loadIcon(url);
        if (imgId) {
          mappingPairs.push(url, imgId);
        }
      }

      if (mappingPairs.length === 0) {
        return; // circles only
      }

      // Symbol layer picks the correct image per-feature using a match on the "icon" property
      map.addLayer({
        id: 'incidents-icons',
        type: 'symbol',
        source: 'incidents',
        filter: ['all', ['==', ['geometry-type'], 'Point'], ['has', 'icon']],
        layout: {
          'icon-image': ['match', ['get', 'icon'], ...mappingPairs, ''],
          'icon-size': 1,
          'icon-allow-overlap': true,
          'icon-ignore-placement': true,
        }
      });

      // Hide circle markers where an icon is present (even if icon unknown, symbol layer will render none)
      map.setPaintProperty('incidents-circles', 'circle-opacity', [
        'case',
        ['has', 'icon'], 0,
        0.9
      ]);

      // Fit bounds (points only)
      const bounds = new maplibregl.LngLatBounds();
      let hasAny = false;

      for (const f of feats) {
        const g = f?.geometry;
        if (g?.type === 'Point' && Array.isArray(g.coordinates) && g.coordinates.length >= 2) {
          bounds.extend([Number(g.coordinates[0]), Number(g.coordinates[1])]);
          hasAny = true;
        }
      }

      if (hasAny) {
        map.fitBounds(bounds, { padding: 80, duration: 900 });
      }
    }

    function bindPopups() {
      const popup = new maplibregl.Popup({ closeButton: true, closeOnClick: true, maxWidth: '380px' });

      map.on('click', (e) => {
        const layers = ['incidents-icons', 'incidents-circles'].filter(id => map.getLayer(id));
        const features = map.queryRenderedFeatures(e.point, { layers });

        if (!features || features.length === 0) return;

        const f = features[0];
        popup
          .setLngLat(e.lngLat)
          .setHTML(popupHtml(f.properties || {}))
          .addTo(map);
      });

      map.on('mousemove', (e) => {
        const layers = ['incidents-icons', 'incidents-circles'].filter(id => map.getLayer(id));
        const features = map.queryRenderedFeatures(e.point, { layers });
        map.getCanvas().style.cursor = (features && features.length) ? 'pointer' : '';
      });
    }

    map.on('load', async () => {
      await addGeojsonLayersWithPerFeatureIcons();
      enableTerrainIfConfigured();
      enable3dBuildingsIfPossible();
      bindPopups();
    });
  </script>
</body>
</html>
