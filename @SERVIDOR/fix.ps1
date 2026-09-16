$files = @("add_produto.php", "edit_produto.php", "pixel.php", "config.php", "apis.php", "administrador.php", "dashboard.php")
foreach ($f in $files) {
    if (Test-Path $f) {
        $content = Get-Content -Path $f -Raw
        $content = $content -replace 'ÁƒÂ§ÁƒÂµes', 'ções'
        $content = $content -replace 'ÁƒÂ§ÁƒÂ£o', 'ção'
        $content = $content -replace 'ÁƒÂ§ÁƒÂ£', 'çã'
        $content = $content -replace 'ÁƒÂ¡', 'á'
        $content = $content -replace 'ÁƒÂ©', 'é'
        $content = $content -replace 'ÁƒÂ­', 'í'
        $content = $content -replace 'ÁƒÂ³', 'ó'
        $content = $content -replace 'ÁƒÂº', 'ú'
        $content = $content -replace 'ÁƒÂ¢', 'â'
        $content = $content -replace 'ÁƒÂª', 'ê'
        $content = $content -replace 'ÁƒÂ´', 'ô'
        $content = $content -replace 'ÁƒÂ£', 'ã'
        $content = $content -replace 'ÁƒÂµ', 'õ'
        $content = $content -replace 'ÁƒÂ§', 'ç'
        $content = $content -replace 'Áƒâ€¡ÁƒÆ’O', 'ÇÃO'
        $content = $content -replace 'Áƒâ€¡Áƒâ€¢ES', 'ÇÕES'
        $content = $content -replace 'Áƒâ€¡', 'Ç'
        $content = $content -replace 'Áƒâ€š', 'Â'
        $content = $content -replace 'Ã§Ã£o', 'ção'
        $content = $content -replace 'Ã§Ãµes', 'ções'
        $content = $content -replace 'Ã¡', 'á'
        $content = $content -replace 'Ã©', 'é'
        $content = $content -replace 'Ã­', 'í'
        $content = $content -replace 'Ã³', 'ó'
        $content = $content -replace 'Ãº', 'ú'
        $content = $content -replace 'Ã£', 'ã'
        $content = $content -replace 'Ãµ', 'õ'
        $content = $content -replace 'Ã§', 'ç'
        $content = $content -replace 'Ã¢', 'â'
        $content = $content -replace 'Ãª', 'ê'
        $content = $content -replace 'Ã´', 'ô'
        $content = $content -replace 'Ãš', 'Ú'
        Set-Content -Path $f -Value $content
    }
}
