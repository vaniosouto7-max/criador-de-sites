<?php
$directory = __DIR__;
$css_correction = '
  <style>
    /* Correção Global de Ícones */
    .material-icons, .material-icons-round {
      font-family: "Material Icons Round" !important;
      font-weight: normal !important;
      font-style: normal !important;
      font-size: 20px !important;
      line-height: 1 !important;
      letter-spacing: normal !important;
      text-transform: none !important;
      display: inline-block !important;
      white-space: nowrap !important;
      word-wrap: normal !important;
      direction: ltr !important;
      -webkit-font-feature-settings: "liga" !important;
      -webkit-font-smoothing: antialiased !important;
      color: inherit !important;
    }
    .container-fluid.py-4::before { content: none !important; }
  </style>
';

$files = glob($directory . "/*.php");
foreach ($files as $file) {
    if (basename($file) == 'fix_ui.php') continue;

    $content = file_get_contents($file);

    // Limpeza de aspas e textos residuais
    $content = str_replace('""', '', $content);
    $content = str_replace('hora a tabela de monitoramento */', '', $content);

    // Inserir CSS se não existir
    if (strpos($content, '/* Correção Global de Ícones */') === false) {
        $content = str_replace('</head>', $css_correction . '</head>', $content);
    }

    file_put_contents($file, $content);
    echo "Corrigido: " . basename($file) . "\n";
}
?>
