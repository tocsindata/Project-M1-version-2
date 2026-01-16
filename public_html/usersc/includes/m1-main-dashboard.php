<?php

// display_dashboard_spans example-only dashboard (additive grid mode)
if (!function_exists('display_main_dashboard')) {
    function display_main_dashboard(int $this_user_id = 0): void
    {
      global $abs_us_root, $us_url_root ;

        if ($this_user_id === 0) {
            echo '<div class="row m1-row"><p>FATAL ERROR: You are not logged in.</p></div>' . PHP_EOL;
            exit;
        }

        $location_id = get_users_location_id($this_user_id) ;

                echo '
        <div class="dashboardc dashboardc-grid container-fluid">

          <div class="m1g m1g-w-2 m1g-h-2"><h4 style="margin-top:0;">Normal (w2 h2)</h4><p style="margin-bottom:0;">1/4 width, 1/3 height.</p></div>
          <div class="m1g m1g-w-2 m1g-h-2"><h4 style="margin-top:0;">Normal (w2 h2)</h4><p style="margin-bottom:0;">1/4 width, 1/3 height.</p></div>

        <div class="m1g m1g-4x4">
          ' ;
          $widget_title = get_location_title($location_id);
          $btn_link = "#d7map_settings";
          $display_id = 7 ; // main dashboard based on map-test7.php
          $data_sources = get_source($this_user_id, $display_id, "geojson") ;
          echo widget_title_settings($this_user_id, $widget_title, $btn_link);

          echo '
          <div class="mapView"></div>';

          echo '
        </div>

          <!-- Normal widgets: 1/4 width x 1/3 height -->
          <div class="m1g m1g-w-2 m1g-h-2"><h4 style="margin-top:0;">Normal (w2 h2)</h4><p style="margin-bottom:0;">1/4 width, 1/3 height.</p></div>
          <div class="m1g m1g-w-2 m1g-h-2"><h4 style="margin-top:0;">Normal (w2 h2)</h4><p style="margin-bottom:0;">1/4 width, 1/3 height.</p></div>

          <!-- Half-width: 1/8 width -->
          <div class="m1g m1g-w-1 m1g-h-2"><h4 style="margin-top:0;">Half-width (w1 h2)</h4><p style="margin-bottom:0;">1/8 width.</p></div>
          <div class="m1g m1g-w-1 m1g-h-2"><h4 style="margin-top:0;">Half-width (w1 h2)</h4><p style="margin-bottom:0;">1/8 width.</p></div>

          <!-- Double-width: 1/2 width -->
          <div class="m1g m1g-w-4 m1g-h-2"><h4 style="margin-top:0;">Double-width (w4 h2)</h4><p style="margin-bottom:0;">1/2 width.</p></div>

          <!-- Two rows tall + double wide -->
          <div class="m1g m1g-w-4 m1g-h-4"><h4 style="margin-top:0;">2 rows tall + 2x wide (w4 h4)</h4><p style="margin-bottom:0;">Spans 2 normal rows, half width.</p></div>

          <!-- Two rows tall, normal width -->
          <div class="m1g m1g-w-2 m1g-h-4"><h4 style="margin-top:0;">2 rows tall (w2 h4)</h4><p style="margin-bottom:0;">Spans 2 normal rows, 1/4 width.</p></div>

          <!-- Extra combos you will likely want soon -->
          <div class="m1g m1g-w-8 m1g-h-1"><h4 style="margin-top:0;">Full-width banner (w8 h1)</h4><p style="margin-bottom:0;">Full width, half-height band.</p></div>
          <div class="m1g m1g-w-3 m1g-h-2"><h4 style="margin-top:0;">3/8 width (w3 h2)</h4><p style="margin-bottom:0;">Useful for maps + side panels.</p></div>
          <div class="m1g m1g-w-5 m1g-h-2"><h4 style="margin-top:0;">5/8 width (w5 h2)</h4><p style="margin-bottom:0;">Pairs with 3/8 for clean splits.</p></div>

        </div>
        ';
        // require_once $abs_us_root.$us_url_root.'usersc/includes/m1-location-selection.php'; // called via header call
        m1_location_selection((int)$this_user_id);

    }
}