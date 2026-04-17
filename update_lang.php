<?php

$domain = $argv[1];
$key = $argv[2];
$en = $argv[3];
$ar = $argv[4];

function updateLangFile($path, $keyStr, $value)
{
    if (! file_exists(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    $data = [];
    if (file_exists($path)) {
        $data = include $path;
    }

    // Set nested key
    $keys = explode('.', $keyStr);
    $current = &$data;
    foreach ($keys as $k) {
        if (! isset($current[$k]) || ! is_array($current[$k])) {
            $current[$k] = [];
        }
        $current = &$current[$k];
    }
    $current = $value;

    // Write back
    $content = "<?php\n\nreturn ".var_export($data, true).";\n";
    // Fix array syntax from array () to []
    $content = str_replace(['array (', ')'], ['[', ']'], $content);
    file_put_contents($path, $content);
}

updateLangFile(__DIR__."/lang/en/{$domain}.php", $key, $en);
updateLangFile(__DIR__."/lang/ar/{$domain}.php", $key, $ar);
