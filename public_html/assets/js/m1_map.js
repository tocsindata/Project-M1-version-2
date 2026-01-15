/* Project: Project M1 Dashboard
 * Framework: UserSpice 6
 * File: assets/js/m1_map.js
 * Date: 2026-01-07
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */

(function () {
  "use strict";

  // Use a class-based container. If multiple exist, we initialize the first one.
  var el = document.querySelector(".mapView");
  if (!el) return;

  // Optional layer URLs (public URLs with CORS enabled).
  // You can set these per-page via data attributes on the .mapView element.
  // Examples:
  // data-geojson-url="https://example.com/data/incidents.geojson"
  // data-kml-url="https://example.com/data/feeds.kml"
  var geojsonUrl = el.getAttribute("data-geojson-url") || "";
  var kmlUrl = el.getAttribute("data-kml-url") || "";

  // Default location: Washington, DC
  // ArcGIS MapView uses [longitude, latitude]
  var defaultCenter = [-77.0369, 38.9072];
  var defaultZoom = 10;

  require([
    "esri/Map",
    "esri/views/MapView",
    "esri/layers/GeoJSONLayer",
    "esri/layers/KMLLayer",
    "esri/widgets/Home",
    "esri/widgets/Legend",
    "esri/widgets/Expand"
  ], function (
    Map,
    MapView,
    GeoJSONLayer,
    KMLLayer,
    Home,
    Legend,
    Expand
  ) {

    var map = new Map({
      basemap: "streets-navigation-vector"
    });

    var view = new MapView({
      container: el, // element container (class-based)
      map: map,
      center: defaultCenter,
      zoom: defaultZoom
    });

    // Home button (zoom UI is included by default)
    var homeWidget = new Home({ view: view });
    view.ui.add(homeWidget, "top-left");

    // Legend (collapsed into Expand)
    var legend = new Legend({ view: view });
    var legendExpand = new Expand({
      view: view,
      content: legend,
      expanded: false
    });
    view.ui.add(legendExpand, "top-right");

    // Add GeoJSON layer (optional)
    if (geojsonUrl) {
      try {
        var geojsonLayer = new GeoJSONLayer({
          url: geojsonUrl,
          title: "GeoJSON"
        });
        map.add(geojsonLayer);
      } catch (e) { /* silent */ }
    }

    // Add KML layer (optional)
    if (kmlUrl) {
      try {
        var kmlLayer = new KMLLayer({
          url: kmlUrl,
          title: "KML"
        });
        map.add(kmlLayer);
      } catch (e) { /* silent */ }
    }

    // Auto-fit to first layer that has an extent (keeps DC defaults if no layers load)
    view.when(function () {
      var layers = map.layers.toArray();
      if (!layers.length) return;

      (async function () {
        for (var i = 0; i < layers.length; i++) {
          try {
            var layer = layers[i];
            await layer.load();
            if (layer.fullExtent) {
              await view.goTo(layer.fullExtent.expand(1.2));
              break;
            }
          } catch (err) {
            // continue
          }
        }
      })();
    });

  });
})();
