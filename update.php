<?php
/**
 * Atualizador automático silencioso (chamado via start.php)
 */
set_time_limit(0);

$repoUser = "AvlisSuetham";
$repoName = "CJR-System";
$branch = "main";
$localVersionFile = __DIR__ . "/.version";
$tempZip = __DIR__ . "/update.zip";
$tempDir = __DIR__ . "/update_tmp/";
$rawVersionUrl = "https://raw.githubusercontent.com/$repoUser/$repoName/$branch/.version";
$zipUrl = "https://github.com/$repoUser/$repoName/archive/refs/heads/$branch.zip";

// Busca versão remota
function fetchUrl($url) {
    $opts = ['http' => ['header' => "User-Agent: CJR-Updater\r\n"]];
    $context = stream_context_create($opts);
    return @file_get_contents($url, false, $context) ?: false;
}

$remoteVersion = trim(fetchUrl($rawVersionUrl) ?: "");

// Baixa o ZIP
file_put_contents($tempZip, fopen($zipUrl, 'r'));

// Extrai
$zip = new ZipArchive;
if ($zip->open($tempZip) === TRUE) {
    $zip->extractTo($tempDir);
    $zip->close();
} else {
    http_response_code(500);
    exit("Falha ao abrir ZIP");
}

// Copia recursivamente
function recurse_copy_ignoring($src, $dst, $ignore = []) {
    $dir = opendir($src);
    @mkdir($dst);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..' || in_array($file, $ignore, true)) continue;
        $srcPath = "$src/$file";
        $dstPath = "$dst/$file";
        if (is_dir($srcPath)) {
            recurse_copy_ignoring($srcPath, $dstPath, $ignore);
        } else {
            copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
}
$extractedFolder = "$tempDir/$repoName-$branch";
$ignoreFiles = ['config.php', '.env', 'uploads', 'start.php', 'update.php'];
recurse_copy_ignoring($extractedFolder, __DIR__, $ignoreFiles);

// Atualiza versão local
if ($remoteVersion) file_put_contents($localVersionFile, $remoteVersion);

// Limpa
unlink($tempZip);
function rrmdir($dir) {
    foreach (array_diff(scandir($dir), ['.','..']) as $f) {
        (is_dir("$dir/$f")) ? rrmdir("$dir/$f") : unlink("$dir/$f");
    }
    rmdir($dir);
}
rrmdir($tempDir);

// Retorno silencioso (JSON)
header("Content-Type: application/json");
echo json_encode(["status" => "ok", "version" => $remoteVersion]);
