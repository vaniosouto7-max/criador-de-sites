<?php
/** Helper de Facebook/Meta Pixel para as páginas públicas. */
if (!function_exists('fb_pixel_ensure_table')) {
    function fb_pixel_ensure_table() {
        global $conn;
        if (!$conn) { return; }
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS facebook_pixel (
            id INT NOT NULL PRIMARY KEY,
            pixel_id TEXT,
            ativo TINYINT(1) NOT NULL DEFAULT 0,
            purchase_event TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        mysqli_query($conn, "ALTER TABLE facebook_pixel MODIFY COLUMN pixel_id TEXT");
        mysqli_query($conn, "INSERT IGNORE INTO facebook_pixel (id, pixel_id, ativo, purchase_event) VALUES (1, '', 0, 1)");
    }
}

if (!function_exists('fb_pixel_config')) {
    function fb_pixel_config() {
        global $conn;
        if (!$conn) { return ['pixel_id'=>'', 'ativo'=>0, 'purchase_event'=>1]; }
        fb_pixel_ensure_table();
        $sql = mysqli_query($conn, "SELECT pixel_id, ativo, purchase_event FROM facebook_pixel WHERE id='1' LIMIT 1");
        $row = ($sql && mysqli_num_rows($sql) > 0) ? mysqli_fetch_assoc($sql) : null;
        if (!$row) { return ['pixel_id'=>'', 'ativo'=>0, 'purchase_event'=>1]; }

        $ids = preg_split('/[,\s]+/', (string)$row['pixel_id'], -1, PREG_SPLIT_NO_EMPTY);
        $ids = array_values(array_filter($ids, function($id) { return preg_match('/^\d+$/', $id); }));
        $row['pixel_id'] = implode(',', $ids);
        return $row;
    }
}

if (!function_exists('fb_pixel_enabled')) {
    function fb_pixel_enabled() {
        $cfg = fb_pixel_config();
        return !empty($cfg['pixel_id']) && (int)$cfg['ativo'] === 1;
    }
}

if (!function_exists('fb_pixel_base_code')) {
    function fb_pixel_base_code() {
        $cfg = fb_pixel_config();
        if (empty($cfg['pixel_id']) || (int)$cfg['ativo'] !== 1) { return ''; }

        $pixels = explode(',', $cfg['pixel_id']);
        $pixels = array_filter(array_map('trim', $pixels));
        if(empty($pixels)) { return ''; }

        $code = "\n<!-- Meta/Facebook Pixel -->\n<script>\n";
        $code .= "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?\n";
        $code .= " n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;\n";
        $code .= " n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;\n";
        $code .= " t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');\n";

        foreach($pixels as $p) {
            $code .= "fbq('init', '" . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . "');\n";
        }

        $code .= "fbq('track', 'PageView');\nif (window._fbPixelPendingEvents) { window._fbPixelPendingEvents.forEach(function(e) { fbq.apply(null, e); }); window._fbPixelPendingEvents = []; }\n</script>\n<noscript>\n";

        foreach($pixels as $p) {
            $code .= "<img height=\"1\" width=\"1\" style=\"display:none\" src=\"https://www.facebook.com/tr?id=" . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . "&ev=PageView&noscript=1\" />\n";
        }

        $code .= "</noscript>\n<!-- End Meta/Facebook Pixel -->\n";

        return $code;
    }
}

if (!function_exists('fb_pixel_purchase_enabled')) {
    function fb_pixel_purchase_enabled() {
        $cfg = fb_pixel_config();
        return fb_pixel_enabled() && (int)($cfg['purchase_event'] ?? 1) === 1;
    }
}

if (!function_exists('fb_pixel_prepare_params')) {
    function fb_pixel_prepare_params($params = []) {
        if (!is_array($params)) { return []; }
        if (!isset($params['currency'])) { $params['currency'] = 'BRL'; }
        return $params;
    }
}

if (!function_exists('fb_pixel_event_script')) {
    function fb_pixel_event_script($event, $params = []) {
        if (!fb_pixel_enabled()) { return ''; }
        $event = preg_replace('/[^A-Za-z0-9_]/', '', $event);
        if ($event === 'Purchase' && !fb_pixel_purchase_enabled()) { return ''; }
        $json = json_encode(fb_pixel_prepare_params($params), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return "\n<script>(function(){var e=['track','{$event}',{$json}];if(typeof fbq==='function'){fbq.apply(null,e);}else{window._fbPixelPendingEvents=window._fbPixelPendingEvents||[];window._fbPixelPendingEvents.push(e);}})();</script>\n";
    }
}

if (!function_exists('fb_pixel_event_js')) {
    function fb_pixel_event_js($event, $params = []) {
        if (!fb_pixel_enabled()) { return 'false'; }
        $event = preg_replace('/[^A-Za-z0-9_]/', '', $event);
        if ($event === 'Purchase' && !fb_pixel_purchase_enabled()) { return 'false'; }
        $json = json_encode(fb_pixel_prepare_params($params), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return "(function(){ var e=['track','{$event}',{$json}]; if (typeof fbq === 'function') { fbq.apply(null,e); return true; } window._fbPixelPendingEvents=window._fbPixelPendingEvents||[]; window._fbPixelPendingEvents.push(e); return false; })()";
    }
}
?>
