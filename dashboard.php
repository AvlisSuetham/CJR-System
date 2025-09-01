<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php'; // necessário para checar type de usuários no BD

$baseDir = "uploads/";
$baseReal = realpath($baseDir);
if (!$baseReal) { // garante que exista
    mkdir($baseDir, 0777, true);
    $baseReal = realpath($baseDir);
}

$userDir = $baseDir . $_SESSION['username'] . "/";
if (!is_dir($userDir)) {
    mkdir($userDir, 0777, true);
}

$msg = "";

$meuTipo = isset($_SESSION['type']) ? $_SESSION['type'] : 'user';
$meuUser = isset($_SESSION['username']) ? $_SESSION['username'] : '';

/** Utilitário: obtém type (user/professor/admin) de um username */
function getUserType(mysqli $conn, string $username): ?string {
    static $cache = [];
    if (isset($cache[$username])) return $cache[$username];
    $stmt = $conn->prepare("SELECT type FROM usuarios WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($type);
    $ok = $stmt->fetch();
    $stmt->close();
    if ($ok) {
        $cache[$username] = $type;
        return $type;
    }
    return null;
}

/** Helpers visuais e utilitários */
function fileExt(string $name): string {
    $p = strrpos($name, '.');
    return $p === false ? '' : strtolower(substr($name, $p + 1));
}

function humanSize(int $bytes): string {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
    if ($bytes < 1024 * 1024 * 1024) return round($bytes / (1024 * 1024), 1) . ' MB';
    return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
}

function fileIconSvg(string $ext): string {
    $ext = strtolower($ext);
    $colors = [
        'pdf'  => '#D32F2F',
        'doc'  => '#1976D2', 'docx' => '#1976D2',
        'xls'  => '#2E7D32', 'xlsx' => '#2E7D32',
        'ppt'  => '#E64A19', 'pptx' => '#E64A19',
        'png'  => '#6A1B9A', 'jpg'  => '#6A1B9A', 'jpeg' => '#6A1B9A', 'gif' => '#6A1B9A', 'webp' => '#6A1B9A',
        'mp3'  => '#8E24AA', 'wav'  => '#8E24AA', 'ogg' => '#8E24AA',
        'mp4'  => '#3949AB', 'mov'  => '#3949AB', 'mkv' => '#3949AB',
        'zip'  => '#616161', 'rar'  => '#616161', '7z'  => '#616161',
        'txt'  => '#455A64', 'md'   => '#455A64',
        'php'  => '#F57C00', 'js'   => '#F57C00', 'py'  => '#F57C00',
        'java' => '#F57C00', 'c'    => '#F57C00', 'cpp' => '#F57C00',
        'html' => '#F57C00', 'css'  => '#F57C00',
    ];

    $color = $colors[$ext] ?? '#9E9E9E';
    $svgOpen  = "<svg width='40' height='40' viewBox='0 0 24 24' aria-hidden='true' focusable='false'>";
    $svgClose = "</svg>";

    switch ($ext) {
        case 'pdf':
            $inner = "<rect x='2' y='2' width='20' height='20' rx='4' fill='{$color}'/>"
                   . "<text x='12' y='15' text-anchor='middle' font-size='7' fill='white' font-family='Arial'>PDF</text>";
            return $svgOpen . $inner . $svgClose;

        case 'doc': case 'docx':
            $inner = "<rect x='2' y='2' width='20' height='20' rx='4' fill='{$color}'/>"
                   . "<text x='12' y='15' text-anchor='middle' font-size='7' fill='white' font-family='Arial'>DOC</text>";
            return $svgOpen . $inner . $svgClose;

        case 'png': case 'jpg': case 'jpeg': case 'gif': case 'webp':
            $inner = "<rect x='2' y='2' width='20' height='20' rx='4' fill='{$color}'/>"
                   . "<circle cx='7.5' cy='8' r='2.2' fill='white'/>"
                   . "<path d='M4 17l5-6 4 5 6-9v13H4z' fill='white' />";
            return $svgOpen . $inner . $svgClose;

        case 'mp3': case 'wav': case 'ogg':
            $inner = "<rect x='2' y='2' width='20' height='20' rx='4' fill='{$color}'/>"
                   . "<path d='M9 9v6a3 3 0 0 0 6 0V9' stroke='white' stroke-width='1.2' fill='none' stroke-linecap='round' stroke-linejoin='round'/>";
            return $svgOpen . $inner . $svgClose;

        case 'mp4': case 'mov': case 'mkv':
            $inner = "<rect x='2' y='2' width='20' height='20' rx='4' fill='{$color}'/>"
                   . "<polygon points='9,7 16,12 9,17' fill='white'/>";
            return $svgOpen . $inner . $svgClose;

        case 'zip': case 'rar': case '7z':
            $inner = "<rect x='2' y='2' width='20' height='20' rx='4' fill='{$color}'/>"
                   . "<rect x='7' y='6' width='6' height='3' rx='0.5' fill='white'/>"
                   . "<path d='M9 9v6' stroke='white' stroke-width='1' stroke-linecap='round'/>";
            return $svgOpen . $inner . $svgClose;

        case 'txt': case 'md':
            $inner = "<rect x='2' y='2' width='20' height='20' rx='4' fill='{$color}'/>"
                   . "<path d='M7 8h10M7 11h10M7 14h8' stroke='white' stroke-width='1.2' stroke-linecap='round'/>";
            return $svgOpen . $inner . $svgClose;

        default:
            $inner = "<rect x='2' y='2' width='20' height='20' rx='4' fill='{$color}'/>"
                   . "<path d='M7 7h10v10H7z' fill='white' />";
            return $svgOpen . $inner . $svgClose;
    }
}

/** Upload (somente na própria pasta) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'])) {
    $destino = $userDir . basename($_FILES["arquivo"]["name"]);
    if (move_uploaded_file($_FILES["arquivo"]["tmp_name"], $destino)) {
        $msg = "✅ Arquivo enviado com sucesso!";
    } else {
        $msg = "❌ Erro ao enviar o arquivo.";
    }
}

/** Exclusão de arquivo */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    $arquivoPost = $_POST['excluir'];
    $donoArquivo = $_POST['dono'] ?? '';

    $arquivoReal = realpath($arquivoPost);

    if ($arquivoReal && strpos($arquivoReal, $baseReal) === 0 && is_file($arquivoReal)) {
        $podeExcluir = false;

        if ($_SESSION['type'] === 'admin' || $_SESSION['type'] === 'professor') {
            $podeExcluir = true;
        } elseif ($_SESSION['type'] === 'user' && $donoArquivo === $_SESSION['username']) {
            $podeExcluir = true;
        }

        if ($podeExcluir) {
            if (@unlink($arquivoReal)) {
                $msg = "🗑️ Arquivo excluído com sucesso!";
            } else {
                $msg = "❌ Erro ao excluir o arquivo.";
            }
        } else {
            $msg = "🚫 Você não tem permissão para excluir este arquivo.";
        }
    } else {
        $msg = "❌ Caminho inválido.";
    }
}

/** Lista arquivos de uma pasta, mostrando cartão (grid) de arquivos */
function listarArquivos(string $pasta, string $tipoUsuario, string $usuarioLogado, string $dono) {
    if (!is_dir($pasta)) return;

    $itens = scandir($pasta);
    foreach ($itens as $arq) {
        if ($arq === "." || $arq === "..") continue;

        $caminho = $pasta . $arq;
        if (!is_file($caminho)) continue;

        $href = $pasta . rawurlencode($arq);
        $label = htmlspecialchars($arq, ENT_QUOTES, 'UTF-8');
        $ext = fileExt($arq);
        $size = filesize($caminho);
        $mtime = filemtime($caminho);

        $iconSvg = fileIconSvg($ext);

        $podeExcluir = false;
        if ($tipoUsuario === 'admin' || $tipoUsuario === 'professor') {
            $podeExcluir = true;
        } elseif ($tipoUsuario === 'user' && $dono === $usuarioLogado) {
            $podeExcluir = true;
        }

        echo "<li class='file-card' data-name='" . htmlspecialchars(strtolower($arq), ENT_QUOTES, 'UTF-8') . "' data-ext='" . htmlspecialchars($ext, ENT_QUOTES, 'UTF-8') . "'>";
        echo "  <div class='card-top'>";
        echo "    <div class='card-icon'>" . $iconSvg . "</div>";
        echo "    <div class='card-meta'>";
        echo "      <a class='card-name' href='" . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . "' download>" . $label . "</a>";
        echo "      <div class='card-sub'>" . htmlspecialchars($ext !== '' ? strtoupper($ext) : '—') . " • " . humanSize($size) . "</div>";
        echo "    </div>";
        echo "  </div>";

        echo "  <div class='card-actions'>";
        echo "    <a class='btn tiny' href='" . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . "' download title='Download'>Baixar</a>";

        if ($podeExcluir) {
            $hiddenPath = htmlspecialchars($caminho, ENT_QUOTES, 'UTF-8');
            $hiddenDono = htmlspecialchars($dono, ENT_QUOTES, 'UTF-8');
            echo "    <form method='POST' class='inline-delete' onsubmit='return confirm(\'Tem certeza que deseja excluir este arquivo?\')'>";
            echo "      <input type='hidden' name='excluir' value='" . $hiddenPath . "'>";
            echo "      <input type='hidden' name='dono' value='" . $hiddenDono . "'>";
            echo "      <button class='btn tiny danger' type='submit' title='Excluir'>Excluir</button>";
            echo "    </form>";
        }

        echo "  </div>";
        echo "</li>";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciador de Arquivos — Sophira</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        /* Paleta laranja */
        :root{
            --bg: #fff8f3;
            --card: #fff6ef;
            --muted: #bf360c;
            --accent: #e65100;
            --accent-2: #ff7043;
            --danger: #e53935;
            --radius: 12px;
            --gap: 12px;
        }
        /* Reset minimal */
        *{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%}
        body{font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; background:var(--bg); color:#0f172a}

        /* Layout */
        .app{display:flex; min-height:100vh}
        aside.sidebar{width:260px; background:linear-gradient(180deg,var(--accent),#7f2a00); color:#fff; padding:24px 18px}
        .brand{font-size:18px; font-weight:700; letter-spacing:0.2px; margin-bottom:18px}
        .user-info{display:flex; align-items:center; gap:12px; margin-bottom:24px}
        .avatar{width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,var(--accent-2),#ffb74d); display:flex;align-items:center;justify-content:center;color:white;font-weight:700}
        .menu{margin-top:12px}
        .menu a{display:block;color:inherit;padding:10px;border-radius:8px;text-decoration:none;font-weight:600;margin-bottom:6px}
        .menu a.active{background:rgba(255,255,255,0.06)}

        main{flex:1;padding:20px}
        .topbar{display:flex;align-items:center;gap:12px;margin-bottom:18px}
        .search{flex:1;display:flex;align-items:center;gap:10px}
        .search input{flex:1;padding:10px 12px;border-radius:10px;border:1px solid #ffe6d6;background:white}
        .actions{display:flex;gap:8px}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border-radius:10px;border:1px solid transparent;background:#fff;color:#0f172a;font-weight:700;text-decoration:none;cursor:pointer}
        .btn.primary{background:var(--accent);color:white}
        .btn.ghost{background:transparent;border:1px solid rgba(15,23,42,0.06)}
        .btn.small{padding:6px 8px;font-size:13px}

        .panel{background:var(--card);border-radius:12px;padding:18px;box-shadow:0 10px 30px rgba(19,24,33,0.04)}

        /* toolbar */
        .toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
        .view-toggle{display:flex;gap:8px}

        /* grid */
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}
        .file-card{list-style:none;background:linear-gradient(180deg,#fffaf6,var(--card));border:1px solid #fff0e6;border-radius:10px;padding:12px;display:flex;flex-direction:column;gap:12px}
        .card-top{display:flex;gap:12px;align-items:center}
        .card-icon{width:56px;height:56px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#fff,#fff3e0);box-shadow:0 8px 20px rgba(2,6,23,0.04)}
        .card-icon svg{width:40px;height:40px}
        .card-meta{display:flex;flex-direction:column}
        .card-name{font-weight:700;color:#0f172a;text-decoration:none}
        .card-sub{font-size:13px;color:var(--muted);margin-top:6px}
        .card-actions{display:flex;gap:8px;align-items:center}
        .inline-delete{display:inline}

        /* empty state */
        .empty{padding:40px;text-align:center;color:var(--muted)}

        /* upload area overlay */
        .upload-drop{border:2px dashed #ffe6d0;border-radius:12px;padding:18px;text-align:center;background:linear-gradient(180deg,rgba(255,152,0,0.03),transparent)}

        /* responsive */
        @media(max-width:900px){aside.sidebar{display:none}}    
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">Portal do Aluno CJR</div>
        <div class="user-info">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['username'],0,1)); ?></div>
            <div>
                <div style="font-weight:700"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                <div style="font-size:13px;color:#94a3b8;margin-top:2px"><?php echo htmlspecialchars($meuTipo); ?></div>
            </div>
        </div>
        <nav class="menu">
            <?php $self = htmlspecialchars($_SERVER['PHP_SELF']); ?>
            <?php if ($meuTipo === 'user'): ?>
                <a class="active" href="<?php echo $self; ?>">Meus arquivos</a>
                <a href="<?php echo $self; ?>?atividades=1">Atividades</a>
                <a href="alterar_senha.php">Alterar senha</a>
                <a href="logout.php">Sair</a>
            <?php else: ?>
                <a class="active" href="<?php echo $self; ?>">Meus arquivos</a>
                <a href="<?php echo $self; ?>?todas=1">Todas as pastas</a>
                <a href="alterar_senha.php">Alterar senha</a>
                <?php if ($meuTipo === 'admin'): ?>
                    <a href="alterar_senha_aluno.php">Alterar senha de alunos</a>
                <?php endif; ?>
                <a href="logout.php">Sair</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main>
        <div class="topbar">
            <div class="search">
                <input id="searchInput" type="search" placeholder="Pesquisar arquivos... (nome ou extensão)">
            </div>
            <div class="actions">
                <label class="btn ghost" for="fileInput">Enviar</label>
                <a class="btn small" id="btnGrid">Grid</a>
                <a class="btn small" id="btnList">Lista</a>
            </div>
        </div>

        <div class="panel">
            <?php if (!empty($msg)) echo "<div class='msg'>" . htmlspecialchars($msg) . "</div>"; ?>

            <div class="upload-drop" id="uploadDrop">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                    <div>
                        <div style="font-weight:700">Arraste e solte arquivos aqui</div>
                        <div style="font-size:13px;color:#6b7280;margin-top:6px">Os arquivos serão enviados para sua pasta pessoal.</div>
                    </div>
                    <div>
                        <form id="uploadForm" method="POST" enctype="multipart/form-data">
                            <input style="display:none" type="file" name="arquivo" id="fileInput" multiple>
                            <button class="btn primary" type="button" id="openPicker">Selecionar arquivos</button>
                        </form>
                    </div>
                </div>
                <div id="uploadProgress" style="margin-top:12px;display:none">
                    <div style="height:10px;background:#eef2ff;border-radius:6px;overflow:hidden"><div id="uploadBar" style="height:10px;width:0%"></div></div>
                    <div id="uploadText" style="font-size:13px;color:#6b7280;margin-top:6px"></div>
                </div>
            </div>

            <hr style="margin:18px 0;border:none;border-top:1px solid #f1f5f9">

            <div class="toolbar">
                <div style="font-weight:700">Arquivos disponíveis</div>
                <div style="font-size:13px;color:#6b7280">Exibindo pastas em <?php echo htmlspecialchars($baseDir); ?></div>
            </div>

            <ul id="filesGrid" class="grid">
                <?php
                // Renderização conforme tipo de usuário
                // Regras solicitadas (corrigidas):
                // - user: se ?atividades=1 mostra SOMENTE pastas de professores (somente leitura). Caso contrário, mostra apenas 'Meus arquivos'.
                // - professor: por padrão mostra 'Meus arquivos'. Se ?aluno=nome, mostra pasta do aluno. Se ?todas=1, vê todas as pastas.
                // - admin: por padrão mostra 'Meus arquivos'. Se ?todas=1, vê todas as pastas.

                if ($meuTipo === 'user') {
                    // Se o aluno escolheu ver Atividades, mostramos apenas pastas de professores
                    if (isset($_GET['atividades']) && $_GET['atividades'] == '1') {
                        $pastas = scandir($baseDir);
                        $found = false;
                        foreach ($pastas as $pasta) {
                            if ($pasta === "." || $pasta === ".." || $pasta === $meuUser) continue;
                            $full = $baseDir . $pasta . "/";
                            if (!is_dir($full)) continue;

                            $tipoDono = getUserType($conn, $pasta);
                            if ($tipoDono === 'professor') {
                                $found = true;
                                echo "<li class='file-card' style='grid-column:1/-1;background:linear-gradient(90deg,#fffaf6,#fff)'>
                                        <div style=\"font-weight:700\">Atividades - Prof. " . htmlspecialchars($pasta) . "</div>
                                    </li>";
                                listarArquivos($full, $meuTipo, $meuUser, $pasta);
                            }
                        }
                        if (!$found) {
                            echo "<li class='file-card' style='grid-column:1/-1'><div class='empty'>🚫 Nenhuma atividade encontrada (nenhuma pasta de professor).</div></li>";
                        }
                    } else {
                        // Exibe apenas a própria pasta
                        listarArquivos($userDir, $meuTipo, $meuUser, $meuUser);
                    }

                } elseif ($meuTipo === 'professor') {
                    // Minha pasta sempre exibida
                    listarArquivos($userDir, $meuTipo, $meuUser, $meuUser);

                    // Pesquisa por aluno (preservado)
                    if (isset($_GET['aluno']) && $_GET['aluno'] !== '') {
                        $aluno = $_GET['aluno'];
                        $pastaAluno = $baseDir . $aluno . "/";

                        $tipoAluno = getUserType($conn, $aluno);
                        if ($tipoAluno === 'user' && is_dir($pastaAluno)) {
                            echo "<li class='file-card' style='grid-column:1/-1'><div style=\"font-weight:700\">Pasta do aluno " . htmlspecialchars($aluno) . "</div></li>";
                            listarArquivos($pastaAluno, $meuTipo, $meuUser, $aluno);
                        } else {
                            echo "<li class='file-card' style='grid-column:1/-1'><div class='empty'>🚫 Nenhum aluno encontrado com este nome.</div></li>";
                        }
                    }

                    // Se professor quiser ver todas as pastas
                    if (isset($_GET['todas']) && $_GET['todas'] == '1') {
                        $pastas = scandir($baseDir);
                        foreach ($pastas as $pasta) {
                            if ($pasta === "." || $pasta === "..") continue;
                            $full = $baseDir . $pasta . "/";
                            if (!is_dir($full)) continue;

                            $tipoDono = getUserType($conn, $pasta);
                            $rotulo = $tipoDono ? " (" . htmlspecialchars($tipoDono) . ")" : "";
                            echo "<li class='file-card' style='grid-column:1/-1'><div style=\"font-weight:700\">Pasta de " . htmlspecialchars($pasta) . htmlspecialchars($rotulo) . "</div></li>";
                            listarArquivos($full, $meuTipo, $meuUser, $pasta);
                        }
                    }

                } else { // admin
                    // Mostrar minha pasta sempre
                    listarArquivos($userDir, $meuTipo, $meuUser, $meuUser);

                    // Se admin escolheu ver todas as pastas
                    if (isset($_GET['todas']) && $_GET['todas'] == '1') {
                        $pastas = scandir($baseDir);
                        foreach ($pastas as $pasta) {
                            if ($pasta === "." || $pasta === "..") continue;
                            $full = $baseDir . $pasta . "/";
                            if (!is_dir($full)) continue;

                            $tipoDono = getUserType($conn, $pasta);
                            $rotulo = $tipoDono ? " (" . htmlspecialchars($tipoDono) . ")" : "";
                            echo "<li class='file-card' style='grid-column:1/-1'><div style=\"font-weight:700\">Pasta de " . htmlspecialchars($pasta) . htmlspecialchars($rotulo) . "</div></li>";
                            listarArquivos($full, $meuTipo, $meuUser, $pasta);
                        }
                    }
                }
                ?>
            </ul>

            <?php if ($meuTipo === 'professor'): ?>
                <form method="GET" style="margin-top:12px">
                    <input type="search" name="aluno" placeholder="Pesquisar aluno (nome exato)" value="<?php echo isset($_GET['aluno']) ? htmlspecialchars($_GET['aluno']) : ''; ?>">
                    <button class="btn small" type="submit">Buscar</button>
                </form>
            <?php endif; ?>

        </div>

    </main>
</div>

<script>
// UI: pesquisa client-side
const searchInput = document.getElementById('searchInput');
const filesGrid = document.getElementById('filesGrid');

searchInput.addEventListener('input', ()=>{
    const q = searchInput.value.trim().toLowerCase();
    const cards = filesGrid.querySelectorAll('.file-card');
    cards.forEach(c=>{
        const name = c.getAttribute('data-name') || '';
        const ext = c.getAttribute('data-ext') || '';
        if (!q) { c.style.display='flex'; return; }
        if (name.indexOf(q) !== -1 || ext.indexOf(q) !== -1) c.style.display='flex'; else c.style.display='none';
    });
});

// View toggle (simple: grid/list)
document.getElementById('btnGrid').addEventListener('click', ()=>{ filesGrid.style.gridTemplateColumns='repeat(auto-fill,minmax(220px,1fr))'; });
document.getElementById('btnList').addEventListener('click', ()=>{ filesGrid.style.gridTemplateColumns='1fr'; });

// Upload: drag & drop + AJAX upload with progress
const uploadDrop = document.getElementById('uploadDrop');
const fileInput = document.getElementById('fileInput');
const openPicker = document.getElementById('openPicker');
const uploadForm = document.getElementById('uploadForm');
const uploadProgress = document.getElementById('uploadProgress');
const uploadBar = document.getElementById('uploadBar');
const uploadText = document.getElementById('uploadText');

openPicker.addEventListener('click', ()=> fileInput.click());

['dragenter','dragover'].forEach(ev=>{
    uploadDrop.addEventListener(ev,e=>{ e.preventDefault(); uploadDrop.style.borderColor = '#93c5fd'; uploadDrop.style.background = 'linear-gradient(180deg, rgba(99,102,241,0.04), transparent)'; });
});
['dragleave','drop'].forEach(ev=>{
    uploadDrop.addEventListener(ev,e=>{ e.preventDefault(); uploadDrop.style.borderColor = ''; uploadDrop.style.background=''; });
});

uploadDrop.addEventListener('drop', e=>{
    const files = e.dataTransfer.files;
    if (files.length) handleFiles(files);
});

fileInput.addEventListener('change', ()=>{
    if (fileInput.files.length) handleFiles(fileInput.files);
});

function handleFiles(files){
    const fd = new FormData();
    // note: current server expects 'arquivo' as the file field; if multiple, send one by one
    // we'll upload files sequentially to keep it simple
    const arr = Array.from(files);
    uploadProgress.style.display = 'block';
    let uploaded = 0;

    function next(){
        if (!arr.length) { uploadText.textContent = 'Envio concluído. Recarregando...'; setTimeout(()=>location.reload(),700); return; }
        const f = arr.shift();
        const single = new FormData();
        single.append('arquivo', f);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '', true); // envia para a mesma rota
        xhr.upload.onprogress = function(e){
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                uploadBar.style.width = pct + '%';
                uploadText.textContent = `Enviando ${f.name} — ${pct}%`;
            }
        };
        xhr.onload = function(){
            uploaded++;
            uploadBar.style.width = Math.round((uploaded / (uploaded + arr.length)) * 100) + '%';
            uploadText.textContent = `Arquivo ${f.name} enviado.`;
            setTimeout(next, 300);
        };
        xhr.onerror = function(){ alert('Erro ao enviar ' + f.name); setTimeout(next,300); };
        xhr.send(single);
    }
    next();
}

</script>
</body>
</html>
