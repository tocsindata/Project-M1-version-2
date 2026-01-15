<?php
declare(strict_types=1);

/**
 * Project: Web Map (CesiumJS 3D Buildings + GeoJSON Icons)
 * Framework: n/a map test
 * File: public_html/map-test4.php
 * Date: 2026-01-15
 * Copyright: (c) Your Org
 */

$geojson_url = "https://downloads.globalincidentmap.com/esri/index.php?api=CDA-1-CVB-CSZ-EOF&token=zkXxJkhg6ww6u7Jd";


/*
  Cesium note:
  - For reliable 3D terrain + OSM 3D Buildings, set a Cesium ion access token.
  - If left blank, the map still loads and icons still work, but buildings/terrain may be unavailable.
*/
$cesium_ion_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiI4ZjA1ZjVjOC0xOTliLTQxOWUtYTg3Yi05NTlkOGIyN2Q3M2MiLCJpZCI6Mzc4ODM3LCJpYXQiOjE3Njg0NTYyODd9.M1-1QRp2cokt-ddxIrkTg7vup8DlYujdQcRzgE0Xgoc"; // e.g. "YOUR_CESIUM_ION_TOKEN"

/*
  Icon proxy endpoint (same origin) to avoid CORS/WebGL texture restrictions:
    /map-test4.php?icon_proxy=1&u=<urlencoded icon url>
*/
function icon_proxy_output(string $url, int $timeoutSeconds = 8, int $maxBytes = 300_000): void
{
    $parts = parse_url($url);
    if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Invalid icon URL";
        exit;
    }

    $scheme = strtolower((string)$parts['scheme']);
    if (!in_array($scheme, ['http', 'https'], true)) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Icon URL must be http/https";
        exit;
    }

    $ch = curl_init($url);
    if ($ch === false) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "cURL init failed";
        exit;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 2,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT        => $timeoutSeconds,
        CURLOPT_USERAGENT      => 'IconProxy/1.0',
        CURLOPT_HTTPHEADER     => ['Accept: image/*;q=1, */*;q=0.1'],
    ]);

    $raw  = curl_exec($ch);
    $err  = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $ct   = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($raw === false) {
        http_response_code(502);
        header('Content-Type: text/plain; charset=utf-8');
        echo "cURL error: {$err}";
        exit;
    }

    if ($code < 200 || $code >= 300) {
        http_response_code(502);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Icon HTTP error: {$code}";
        exit;
    }

    if (strlen($raw) > $maxBytes) {
        http_response_code(413);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Icon too large";
        exit;
    }

    $ctLower = strtolower($ct);
    $allowed = [
        'image/png',
        'image/jpeg',
        'image/jpg',
        'image/gif',
        'image/webp',
        'image/svg+xml',
    ];

    $contentType = 'image/png';
    foreach ($allowed as $a) {
        if (str_starts_with($ctLower, $a)) {
            $contentType = $a;
            break;
        }
    }

    header('Content-Type: ' . $contentType);
    header('Cache-Control: public, max-age=86400');
    header('X-Icon-Proxy: 1');
    echo $raw;
    exit;
}

if (isset($_GET['icon_proxy']) && (string)($_GET['icon_proxy']) === '1') {
    $u = isset($_GET['u']) ? trim((string)$_GET['u']) : '';
    if ($u === '') {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Missing u";
        exit;
    }
    icon_proxy_output($u);
}

function fetch_geojson_via_curl(string $url, int $timeoutSeconds = 10, int $maxBytes = 3_000_000): array
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
        CURLOPT_USERAGENT      => 'GeoJSONCesium3DTest/1.0',
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
                "icon" => "https://img.globalincidentmap.com/assets/icons/shooting.gif",
            ],
            "geometry" => [
                "type" => "Point",
                "coordinates" => [-93.2880230, 45.0128230],
            ],
        ],
    ],
];

$geojsonData = null;
$loadError = '';

$res = fetch_geojson_via_curl($geojson_url);
if ($res['ok'] === true) {
    $geojsonData = $res['data'];
} else {
    $loadError = (string)$res['error'];
    $geojsonData = $sampleGeojson;
}

// Proxy icons to same origin for WebGL texture safety
$baseSelf = strtok((string)($_SERVER['REQUEST_URI'] ?? ''), '?'); // /map-test4.php
if (isset($geojsonData['features']) && is_array($geojsonData['features'])) {
    foreach ($geojsonData['features'] as $i => $f) {
        if (!is_array($f) || !isset($f['properties']) || !is_array($f['properties'])) {
            continue;
        }
        $icon = $f['properties']['icon'] ?? '';
        if (!is_string($icon) || trim($icon) === '') {
            continue;
        }
        $icon = trim($icon);
        $geojsonData['features'][$i]['properties']['icon'] = $baseSelf . '?icon_proxy=1&u=' . rawurlencode($icon);
    }
}

header('Content-Type: text/html; charset=utf-8');

$geojsonJson = json_encode($geojsonData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($geojsonJson === false) {
    $geojsonJson = json_encode($sampleGeojson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $loadError = $loadError !== '' ? $loadError : 'json_encode failed; using sample data';
}

$cesiumTokenEsc = htmlspecialchars($cesium_ion_token, ENT_QUOTES, 'UTF-8');
$loadErrorEsc   = htmlspecialchars($loadError, ENT_QUOTES, 'UTF-8');

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cesium 3D Buildings + GeoJSON Icons</title>

  <link rel="stylesheet" href="https://unpkg.com/cesium@1.133.1/Build/Cesium/Widgets/widgets.css">

  <style>
    html, body { height: 100%; margin: 0; background: #0b0f14; color: #e7eef7; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; overflow: hidden; }
    #cesiumContainer { width: 100%; height: 100vh; display:block; }

    .hud {
      position: fixed; z-index: 9999; left: 12px; top: 12px;
      background: rgba(15, 18, 24, 0.86);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 10px;
      padding: 10px 12px;
      backdrop-filter: blur(6px);
      max-width: min(760px, 92vw);
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
      max-width: 360px;
      pointer-events: none;
    }

    .legend h4 { margin: 0 0 8px; font-size: 13px; font-weight: 600; }
    .legend .row { display: flex; align-items: center; gap: 8px; margin: 6px 0; font-size: 12px; }
    .legend .swatch { width: 12px; height: 12px; border-radius: 3px; border: 1px solid rgba(255,255,255,0.18); }
    .legend img { width: 18px; height: 18px; image-rendering: auto; }

    /* Cesium widget contrast tweaks */
    .cesium-viewer-toolbar, .cesium-viewer-animationContainer, .cesium-viewer-timelineContainer {
      filter: saturate(1.1) contrast(1.05);
    }
  </style>
</head>

<body>
  <div class="hud">
    <div>3D globe + (attempt) OSM 3D buildings + GeoJSON icon billboards • Philadelphia start view</div>
    <div>Tip: scroll = zoom, right-drag = tilt/rotate</div>
    <?php if ($loadErrorEsc !== ''): ?>
      <div class="err">GeoJSON load error (showing sample): <?= $loadErrorEsc ?></div>
    <?php endif; ?>
  </div>

  <div id="cesiumContainer"></div>

  <div class="legend">
    <h4>Legend (event_type)</h4>
    <div id="legendRows"></div>
  </div>

  <script src="https://unpkg.com/cesium@1.133.1/Build/Cesium/Cesium.js"></script>

  <script>
    const GEOJSON = JSON.parse(<?= json_encode($geojsonJson, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
    const CESIUM_ION_TOKEN = "<?= $cesiumTokenEsc ?>";

    function escapeHtml(s) {
      return (s ?? '').toString()
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }

    function colorFromString(str) {
      // deterministic color-ish
      let h = 0;
      const s = (str || 'Unknown').toString();
      for (let i=0; i<s.length; i++) h = (h*31 + s.charCodeAt(i)) >>> 0;
      const r = 80 + (h & 127);
      const g = 80 + ((h >> 8) & 127);
      const b = 80 + ((h >> 16) & 127);
      return Cesium.Color.fromBytes(r, g, b, 255);
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
        linkHtml = `<div style="margin:6px 0;"><a href="${safeUrl}" target="_blank" rel="noopener" style="color:#8dc4ff;text-decoration:none;">Open source</a></div>`;
      }

      return `
        <div class="popup" style="max-width:360px;">
          <h3 style="margin:0 0 6px;font-size:14px;">${title}</h3>
          ${when ? `<div style="opacity:.8;font-size:12px;">Time (GMT)</div><div style="font-size:12px;margin-bottom:6px;">${when}</div>` : ``}
          ${et ? `<div style="opacity:.8;font-size:12px;">Type</div><div style="font-size:12px;margin-bottom:6px;">${et}</div>` : ``}
          ${sev ? `<div style="opacity:.8;font-size:12px;">Severity</div><div style="font-size:12px;margin-bottom:6px;">${sev}</div>` : ``}
          ${loc ? `<div style="opacity:.8;font-size:12px;">Location</div><div style="font-size:12px;margin-bottom:6px;">${loc}</div>` : ``}
          ${linkHtml}
          ${desc ? `<div style="opacity:.8;font-size:12px;">Description</div><div style="font-size:12px;margin-bottom:6px;">${desc}</div>` : ``}
        </div>
      `;
    }

    // Build legend: event_type -> { color, iconUrl (first seen) }
    const legendMap = new Map();
    const feats = Array.isArray(GEOJSON?.features) ? GEOJSON.features : [];
    for (const f of feats) {
      const p = f?.properties || {};
      const et = (p.event_type || 'Unknown').toString();
      if (!legendMap.has(et)) {
        legendMap.set(et, {
          color: colorFromString(et),
          icon: (p.icon || '').toString().trim()
        });
      }
    }

    function renderLegend() {
      const holder = document.getElementById('legendRows');
      if (!holder) return;

      const keys = Array.from(legendMap.keys()).sort((a,b)=>a.localeCompare(b));
      if (!keys.length) {
        holder.innerHTML = `<div class="row"><span style="opacity:.8;">No categories.</span></div>`;
        return;
      }

      holder.innerHTML = keys.map(k => {
        const v = legendMap.get(k);
        const c = v.color;
        const css = `rgb(${Math.round(c.red*255)},${Math.round(c.green*255)},${Math.round(c.blue*255)})`;
        const icon = v.icon ? `<img src="${escapeHtml(v.icon)}" alt="">` : ``;
        return `<div class="row"><span class="swatch" style="background:${css}"></span>${icon}<span>${escapeHtml(k)}</span></div>`;
      }).join('');
    }

    renderLegend();

    if (CESIUM_ION_TOKEN) {
      Cesium.Ion.defaultAccessToken = CESIUM_ION_TOKEN;
    }

    const viewer = new Cesium.Viewer('cesiumContainer', {
      sceneModePicker: true,
      baseLayerPicker: true,
      geocoder: true,
      homeButton: true,
      navigationHelpButton: true,
      animation: false,
      timeline: false,
      fullscreenButton: true,
      infoBox: true,
      selectionIndicator: true,
      shouldAnimate: false
    });

    // Darken the default imagery a bit (visual comfort)
    try {
      viewer.scene.brightness = 0.9;
    } catch (_) {}

    // Start near Philadelphia
    viewer.camera.setView({
      destination: Cesium.Cartesian3.fromDegrees(-75.1652, 39.9526, 1800.0),
      orientation: {
        heading: Cesium.Math.toRadians(-18),
        pitch: Cesium.Math.toRadians(-35),
        roll: 0.0
      }
    });

    async function enableTerrainAndBuildings() {
      // Terrain (if token supports)
      try {
        if (CESIUM_ION_TOKEN) {
          viewer.terrainProvider = await Cesium.createWorldTerrainAsync();
        }
      } catch (_) {}

      // 3D Buildings (if token supports)
      try {
        if (CESIUM_ION_TOKEN) {
          const buildings = await Cesium.createOsmBuildingsAsync();
          viewer.scene.primitives.add(buildings);
        }
      } catch (_) {}
    }

    enableTerrainAndBuildings();

    // Add points as billboards (icons) + fallback colored pin if no icon
    const entities = [];

    for (const f of feats) {
      const g = f?.geometry;
      const p = f?.properties || {};

      if (!g || g.type !== 'Point' || !Array.isArray(g.coordinates) || g.coordinates.length < 2) continue;

      const lon = Number(g.coordinates[0]);
      const lat = Number(g.coordinates[1]);
      if (!Number.isFinite(lon) || !Number.isFinite(lat)) continue;

      const et = (p.event_type || 'Unknown').toString();
      const color = colorFromString(et);

      const iconUrl = (p.icon || '').toString().trim();

      const e = viewer.entities.add({
        position: Cesium.Cartesian3.fromDegrees(lon, lat, 0),
        billboard: iconUrl ? {
          image: iconUrl,
          width: 26,
          height: 26,
          verticalOrigin: Cesium.VerticalOrigin.BOTTOM,
          heightReference: Cesium.HeightReference.CLAMP_TO_GROUND
        } : undefined,
        point: !iconUrl ? {
          pixelSize: 10,
          color: color.withAlpha(0.95),
          outlineColor: Cesium.Color.fromCssColorString('#0b0f14'),
          outlineWidth: 2,
          heightReference: Cesium.HeightReference.CLAMP_TO_GROUND
        } : undefined,
        properties: p
      });

      entities.push(e);
    }

    // Also load non-point GeoJSON (lines/polys) with Cesium’s GeoJsonDataSource
    // (points already handled with billboards above)
    (async () => {
      try {
        const nonPoint = {
          type: 'FeatureCollection',
          features: feats.filter(f => (f?.geometry?.type || '') !== 'Point')
        };

        if (nonPoint.features.length) {
          const ds = await Cesium.GeoJsonDataSource.load(nonPoint, {
            stroke: Cesium.Color.CYAN.withAlpha(0.85),
            fill: Cesium.Color.CYAN.withAlpha(0.20),
            clampToGround: true
          });
          viewer.dataSources.add(ds);
        }
      } catch (_) {}
    })();

    // Click handling: show infoBox content for entity
    const handler = new Cesium.ScreenSpaceEventHandler(viewer.scene.canvas);

    handler.setInputAction((click) => {
      const picked = viewer.scene.pick(click.position);
      if (!Cesium.defined(picked)) return;

      const ent = picked.id;
      if (!ent || !ent.properties) return;

      const props = {};
      try {
        const names = ent.properties.propertyNames || [];
        for (const n of names) props[n] = ent.properties[n].getValue();
      } catch (_) {}

      ent.description = popupHtml(props);
      viewer.selectedEntity = ent;
    }, Cesium.ScreenSpaceEventType.LEFT_CLICK);
  </script>
</body>
</html>
