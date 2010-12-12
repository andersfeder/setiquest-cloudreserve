<?php 

function nonce() {
    require_once 'config.inc.php';
    return substr(hash("sha256", uniqid(CR_SALT . @$_SERVER['REMOTE_ADDR'])), 0, 25);
}

function glue_url($parsed) { 
    if (!is_array($parsed)) { 
        return false; 
    } 

    $uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '' : '//') : ''; 
    $uri .= isset($parsed['user']) ? $parsed['user'].(isset($parsed['pass']) ? ':'.$parsed['pass'] : '').'@' : ''; 
    $uri .= isset($parsed['host']) ? $parsed['host'] : ''; 
    $uri .= isset($parsed['port']) ? ':'.$parsed['port'] : ''; 

    if (isset($parsed['path'])) { 
        $uri .= (substr($parsed['path'], 0, 1) == '/') ? 
            $parsed['path'] : ((!empty($uri) ? '/' : '' ) . $parsed['path']); 
    } 

    $uri .= isset($parsed['query']) ? '?'.$parsed['query'] : ''; 
    $uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : ''; 

    return $uri; 
} 

function print_error($message) {
  die("<b>Error:</b> $message");
}

?>
