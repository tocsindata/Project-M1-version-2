<?php
declare(strict_types=1);

/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice 6
 * File: m1-data-source-helpers.php
 * Date: 2026-01-16
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */

/*
  Site-wide helpers for data source discovery.

  Conventions:
  - $display_id is the display variant identifier (e.g., 7 for d7).
  - $type is a source category selector (e.g., 'geojson', 'kml', 'api', etc.).
  - Return format is a normalized associative array of arrays:
      [
        'geojson' => ['https://...'],
        'kml'     => ['https://...'],
        ...
      ]
*/

if (!function_exists('get_source')) {
  function get_source(int $this_user_id, int $display_id, string $type): array
  {
    $out = [];

    $type = strtolower(trim($type));
    if($type == "geojson"){
    // Placeholder defaults (intentionally minimal for now)
        if ($type === 'geojson') {
        $out['geojson'][] = 'https://example.com/geojson.json';
        }
    }

    return $out;
  }
}


if (!function_exists('fetch_geojson_via_curl')) {
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
}






