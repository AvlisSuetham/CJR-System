<?php
// ==============================
// Configurações do repositório
// ==============================
$repoUser = "AvlisSuetham";
$repoName = "CJR-System";
$branch   = "main";
$localVersionFile = __DIR__ . "/.version";
$rawVersionUrl    = "https://raw.githubusercontent.com/$repoUser/$repoName/$branch/.version";

// Função para buscar URL
function fetchUrl($url) {
    $opts = ['http' => ['header' => "User-Agent: CJR-Updater\r\n"]];
    $context = stream_context_create($opts);
    return @file_get_contents($url, false, $context) ?: false;
}

// Lê versão local
$localVersion = file_exists($localVersionFile) ? trim(file_get_contents($localVersionFile)) : "";
// Busca versão remota
$remoteVersion = fetchUrl($rawVersionUrl);
$remoteVersion = $remoteVersion !== false ? trim($remoteVersion) : null;

// Precisa atualizar?
$needsUpdate = ($remoteVersion && $remoteVersion !== $localVersion);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Centro da Juventude Restinga</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
    /* Reset + base */
    *{box-sizing:border-box;margin:0;padding:0}
    :root{
        --accent:#ff6600;    /* mantive sua cor */
        --accent-dark:#e05500;
        --muted:#666;
        --bg-a:#fff5e6;
        --bg-b:#ffe0b3;
        --glass: rgba(255,255,255,0.8);
    }
    html,body{height:100%}
    body{
        font-family:"Roboto", Arial, sans-serif;
        background: linear-gradient(135deg, var(--bg-a), var(--bg-b));
        -webkit-font-smoothing:antialiased;
        -moz-osx-font-smoothing:grayscale;
        color:#222;
        display:flex;
        flex-direction:column;
    }

    /* Top bar minimal (à direita estilo Google) */
    .topbar{
        width:100%;
        padding:14px 28px;
        display:flex;
        justify-content:flex-end;
        align-items:center;
        gap:10px;
        position:fixed;
        top:0;
        left:0;
        z-index:50;
    }
    .topbar a {
        color:#222;
        text-decoration:none;
        font-size:0.95rem;
        margin-left:12px;
        padding:6px 10px;
        border-radius:6px;
        transition:background .12s, transform .08s;
    }
    .topbar a:hover{ background: rgba(255,255,255,0.6); transform:translateY(-2px) }

    /* CJR Cloud compact menu kept */
    .cjr-btn{
        background:var(--accent);
        color:#fff;
        padding:7px 12px;
        border-radius:8px;
        font-weight:500;
        display:inline-flex;
        gap:8px;
        align-items:center;
        text-decoration:none;
        border:none;
        cursor:pointer;
    }
    .cjr-btn:hover{ background:var(--accent-dark) }

    /* Central area (Google-like) */
    .main {
        flex:1;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:120px 16px 80px; /* espaço para topbar fixa */
    }
    .center {
        width:100%;
        max-width:760px;
        text-align:center;
    }

    /* Logo grande (texto estilizado) */
    .logo {
        font-size:48px;
        font-weight:700;
        margin-bottom:14px;
        color:var(--accent);
        letter-spacing:0.6px;
    }
    .sub {
        color:var(--muted);
        font-weight:500;
        margin-bottom:28px;
    }

    /* Caixa de pesquisa grande e limpa */
    .search-wrap{
        display:flex;
        justify-content:center;
    }
    form.search {
        width:100%;
        max-width:640px;
        display:flex;
        align-items:center;
        background: #fff;
        padding:14px 18px;
        border-radius:999px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.10);
        border:1px solid rgba(0,0,0,0.04);
        transition: box-shadow .15s, transform .08s;
    }
    form.search:focus-within{ box-shadow: 0 18px 50px rgba(0,0,0,0.12); transform:translateY(-2px) }
    .search input[type="text"]{
        flex:1;
        font-size:1.05rem;
        border:none;
        outline:none;
        padding:6px 10px;
        color:#222;
        background:transparent;
    }
    .search .icon {
        width:40px;
        height:40px;
        display:flex;
        align-items:center;
        justify-content:center;
        margin-right:8px;
        opacity:.9;
    }
    .search button[type="submit"]{
        background:transparent;
        border:none;
        padding:8px 10px;
        cursor:pointer;
        font-weight:600;
        color:var(--muted);
    }

    /* Botões estilo Google */
    .actions {
        margin-top:18px;
        display:flex;
        justify-content:center;
        gap:12px;
    }
    .btn {
        background: rgba(255,255,255,0.95);
        border:1px solid rgba(0,0,0,0.06);
        padding:10px 16px;
        border-radius:6px;
        cursor:pointer;
        font-weight:500;
        box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        transition:transform .08s, box-shadow .12s;
    }
    .btn:hover{ transform:translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.08) }

    /* Quick links similar ao Google (embaixo da pesquisa) */
    .quick {
        margin-top:24px;
        display:flex;
        justify-content:center;
        gap:18px;
        flex-wrap:wrap;
        color:var(--muted);
        font-size:0.95rem;
    }
    .quick a { text-decoration:none; color:inherit; padding:6px 10px; border-radius:6px }
    .quick a:hover{ background: rgba(255,255,255,0.6) }

    /* Footer pequeno e leve */
    footer {
        text-align:center;
        padding:18px 8px;
        color:var(--muted);
        font-size:0.9rem;
        border-top: 1px solid rgba(0,0,0,0.03);
        background: linear-gradient(180deg, rgba(255,255,255,0.02), transparent);
    }

    /* Modal de atualização (mantive como antes, só estilizei) */
    .modal {
        display:none;
        position:fixed; left:0; top:0; right:0; bottom:0;
        background: rgba(0,0,0,0.5);
        align-items:center; justify-content:center;
        z-index:9999;
    }
    .modal .content{
        background:#fff; padding:18px 22px; border-radius:12px; text-align:center;
        box-shadow: 0 18px 64px rgba(0,0,0,0.26);
        min-width:300px;
    }
    .modal .content h3{ color:var(--accent); margin-bottom:8px }

    /* Responsivo */
    @media (max-width:600px){
        .logo{ font-size:34px }
        .sub{ font-size:0.95rem }
        .topbar{ padding:10px 14px }
        .search .icon{ display:none }
    }
</style>
</head>
<body>

<?php if ($needsUpdate): ?>
<div id="updateModal" class="modal" style="display:flex;">
    <div class="content">
        <h3>Aplicando atualização do sistema</h3>
        <p>Versão <strong><?php echo htmlspecialchars($remoteVersion); ?></strong></p>
        <p>Aguarde, atualizando automaticamente...</p>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    fetch("update.php")
        .then(() => location.reload())
        .catch(() => alert("Erro ao aplicar atualização!"));
});
</script>
<?php endif; ?>

<!-- Topbar com links pequenos (estilo Google) -->
<div class="topbar" role="navigation" aria-label="Top navigation">
    <!-- Mantive o CJR Cloud (botão laranja) -->
    

    <!-- Links rápidos estilo Google (abrir em nova aba) -->
    <a href="https://chat.openai.com/" target="_blank" rel="noopener noreferrer">ChatGPT</a>
    <a href="https://mail.google.com/" target="_blank" rel="noopener noreferrer">Gmail</a>
    <a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer">YouTube</a>
    <a href="sobre.html">Sobre</a>
    <a href="#" class="cjr-btn" onclick="document.getElementById('menu').classList.toggle('open'); return false;">Portal do Aluno ▾</a>
</div>

<!-- (Menu flutuante simples, preserva Cadastro / Login) -->
<div id="menu" style="position:fixed; right:18px; top:56px; display:none;">
    <div style="background:rgba(255,255,255,0.98); padding:10px; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.12);">
        <a href="register.php" style="display:block;padding:8px 12px;text-decoration:none;color:#222">Cadastro</a>
        <a href="login.php" style="display:block;padding:8px 12px;text-decoration:none;color:#222">Login</a>
    </div>
</div>

<!-- Conteúdo central ao estilo Google -->
<main class="main" role="main">
    <section class="center" aria-labelledby="title">
        <div class="logo" id="title">Centro da Juventude Restinga</div>
        <div class="sub">powered by Sophira</div>

        <div class="search-wrap" style="margin-top:28px;">
            <form class="search" action="https://www.google.com/search" method="get" role="search" aria-label="Pesquisar no Google">
                <div class="icon" aria-hidden="true">
                    <!-- pequeno ícone circular estilizado -->
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" stroke="#ccc" stroke-width="1.2"/>
                        <path d="M8 12h8" stroke="#ccc" stroke-width="1.4" stroke-linecap="round"/>
                    </svg>
                </div>

                <input type="text" name="q" placeholder="Pesquisar no Google..." autocomplete="off" autofocus>
                <button type="submit" aria-label="Pesquisar">Pesquisar</button>
            </form>
        </div>

        <div class="actions" role="region" aria-label="Ações">
            <button class="btn" onclick="document.querySelector('.search input[name=q]').value && document.querySelector('.search').submit()">
                Pesquisa Google
            </button>
            <button class="btn" onclick="window.location.href='sobre.html';">
                Sobre
            </button>
        </div>

        <div class="quick" aria-label="Mensagem de boas-vindas" style="max-width:600px; margin:24px auto 0; font-size:1rem; color:#444; line-height:1.5em;">
            <span style="font-size:0.9rem; color:#888;">Versão do Sistema: <strong><?php echo htmlspecialchars($localVersion ?: 'desconhecida'); ?></strong></span>
        </div>
    </section>
</main>

<footer>
    Criado por <strong style="color:var(--accent)">Matheus Pereira da Silva</strong> — Centro da Juventude Restinga ©2025
</footer>

<script>
/* Mantém o menu flutuante simples (preserva Cadastro/Login/Sobre) */
(function(){
    const menuBtn = document.querySelector('.cjr-btn');
    const menu = document.getElementById('menu');

    menuBtn && menuBtn.addEventListener('click', function(e){
        e.preventDefault();
        const open = menu.style.display === 'block';
        menu.style.display = open ? 'none' : 'block';
    });

    // fecha clicando fora
    document.addEventListener('click', function(ev){
        if (!menu.contains(ev.target) && !menuBtn.contains(ev.target)) {
            menu.style.display = 'none';
        }
    });

    // recolhe menu em resize
    window.addEventListener('resize', function(){ menu.style.display = 'none' });
})();
</script>
</body>
</html>