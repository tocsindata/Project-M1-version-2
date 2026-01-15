<?php

if (!function_exists('header_calls_path_helper')) {
    function header_calls_path_helper() {
        // we need to make sure this is accurate, and dynamic for different hostings
        $out = [] ;
        $include_path = __DIR__ ; // where this function is.
        $out['inc'] = $include_path."/" ;
        $out['usersc'] = str_replace("/includes", "", $include_path)."/" ;
        $out['users'] = str_replace("usersc/", "users", $out['usersc'])."/" ;
        $out['public'] = str_replace("/usersc/includes", "", $include_path)."/" ;
        $out['assets'] = str_replace("/usersc/includes", "/assets", $include_path)."/" ;
        $out['css'] = $out['assets']."css/" ;
        $out['js'] = $out['assets']."js/" ;
        $out['ajax'] = $out['assets']."ajax/" ;
        $out['images'] = $out['assets']."images/" ;
        $out['videos'] = $out['assets']."videos/" ;
        
        // echo "DEBUG DISCOVERY: <pre>".print_r($out, true) . "</pre>" ; 
        // exit() ;
        return $out ;
    }
}

if (!function_exists('header_calls')) {
    function header_calls($header_calls = []) {
        // global $abs_us_root, $us_url_root ; // <<<< THIS IS A PROBLEM This function exists before these variables do, and we cant move it
        $paths = header_calls_path_helper() ;

            // header_calls['func'] is set
            if(isset($header_calls['func']) && is_array($header_calls['func'])) {
                $function_calls = $header_calls['func'] ;
                foreach($function_calls as $function_call){
                    if(file_exists($paths['inc'] . $function_call)) {
                        require_once $paths['inc'] . $function_call ;
                    }
                }
            }
            
            // header_calls['css'] ...
            
            // header_calls['js'] ...

            // header_calls['script'] ...
    }
}

