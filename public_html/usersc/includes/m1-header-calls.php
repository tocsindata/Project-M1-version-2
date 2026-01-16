<?php

if (!function_exists('header_calls_path_helper')) {
    function header_calls_path_helper() {
        global $abs_us_root, $us_url_root ; 
        // we need to make sure this is accurate, and dynamic for different hostings
        $out = [] ;
        $include_path = $abs_us_root . $us_url_root . "usersc/includes" ; // where this function is.
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
        
        //echo "DEBUG DISCOVERY: <pre>".print_r($out, true) . "</pre>" ; 
        //exit() ;
        return $out ;
    }
}

if (!function_exists('header_calls')) {
    function header_calls($header_calls = []) {
        global $abs_us_root, $us_url_root ; 
        $paths = header_calls_path_helper() ;

            // header_calls['func'] is set
            if(isset($header_calls['func']) && is_array($header_calls['func'])) {
                $function_calls = $header_calls['func'] ;
                foreach($function_calls as $function_call){
                    if(file_exists($paths['inc'] . $function_call)) {
                        echo PHP_EOL.'<!-- header calls inc '.$function_call.'  -->'.PHP_EOL;
                        require_once $paths['inc'] . $function_call ;
                    }
                }
            }
            
            // header_calls['css'] ...
            if(isset($header_calls['css']) && is_array($header_calls['css'])) {
                $script_calls = $header_calls['css'] ;
                foreach($script_calls as $script_call){
                    if(file_exists($paths['css'] . $script_call)) {
                        // file get contents?
                        $src = file_get_contents($paths['css'] . $script_call);
                        // we add the <style></style> here otherwise we cant use $header_calls['js'] for the same script
                        echo PHP_EOL .'<!-- header call css '.str_replace(".js", "", $script_call)." -->" .PHP_EOL ;
                        echo "<style>".PHP_EOL ;
                        echo $src ;
                        echo PHP_EOL ."</style>".PHP_EOL ;
                    }
                }
            }
            
            // header_calls['js'] ...

            // header_calls['script'] ...
            if(isset($header_calls['script']) && is_array($header_calls['script'])) {
                $script_calls = $header_calls['script'] ;
                foreach($script_calls as $script_call){
                    if(file_exists($paths['js'] . $script_call)) {
                        // file get contents?
                        $src = file_get_contents($paths['js'] . $script_call);
                        // we add the <script></script> here otherwise we cant use $header_calls['js'] for the same script
                        echo PHP_EOL .'<!-- header call js '.str_replace(".js", "", $script_call)." -->" .PHP_EOL ;
                        echo "<script>".PHP_EOL ;
                        echo $src ;
                        echo PHP_EOL ."</script>".PHP_EOL ;
                    }
                }
            }
    }
}

