<?php
$directory = __DIR__;
$files = glob($directory . "/*.php");

$replacements = [
    'ÁƒÂ§ÁƒÂµes' => 'ções',
    'ÁƒÂ§ÁƒÂ£o' => 'ção',
    'ÁƒÂ§ÁƒÂ£' => 'çã',
    'ÁƒÂ¡' => 'á',
    'ÁƒÂ©' => 'é',
    'ÁƒÂ­' => 'í',
    'ÁƒÂ³' => 'ó',
    'ÁƒÂº' => 'ú',
    'ÁƒÂ¢' => 'â',
    'ÁƒÂª' => 'ê',
    'ÁƒÂ´' => 'ô',
    'ÁƒÂ£' => 'ã',
    'ÁƒÂµ' => 'õ',
    'ÁƒÂ§' => 'ç',
    'Áƒâ€¡ÁƒÆ’O' => 'ÇÃO',
    'Áƒâ€¡Áƒâ€¢ES' => 'ÇÕES',
    'Áƒâ€¡' => 'Ç',
    'Áƒâ€š' => 'Â',
    'Ã§Ã£o' => 'ção',
    'Ã§Ãµes' => 'ções',
    'Ã¡' => 'á',
    'Ã©' => 'é',
    'Ã­' => 'í',
    'Ã³' => 'ó',
    'Ãº' => 'ú',
    'Ã£' => 'ã',
    'Ãµ' => 'õ',
    'Ã§' => 'ç',
    'Ã¢' => 'â',
    'Ãª' => 'ê',
    'Ã´' => 'ô',
    'Ãš' => 'Ú'
];

foreach ($files as $file) {
    if (basename($file) == 'fix_encoding_now.php') continue;

    $content = file_get_contents($file);
    $original_content = $content;

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content !== $original_content) {
        file_put_contents($file, $content);
        echo "Corrigido encodings em: " . basename($file) . "\n";
    }
}
echo "Finalizado.\n";
?>
