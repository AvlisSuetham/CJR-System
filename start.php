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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Página Inicial</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Roboto', Arial, sans-serif;
        background: linear-gradient(135deg, #fff5e6, #ffe0b3);
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    /* Topo */
    .topbar {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 15px 30px;
        position: absolute;
        top: 0;
        left: 0;
        gap: 10px;
    }

    /* Botões */
    .shortcut-btn {
        padding: 10px 18px;
        background: #ff6600;
        color: #fff;
        font-size: 0.95em;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.3s, transform 0.2s;
    }

    .shortcut-btn:hover {
        background-color: #e05500;
        transform: translateY(-2px);
    }

    /* Menu hamburguer */
    .menu-container {
        position: relative;
        display: inline-block;
    }

    .menu-btn {
        padding: 10px 18px;
        background: #ff6600;
        color: #fff;
        font-size: 0.95em;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .menu-btn:hover { background-color: #e05500; }

    .hamburger {
        width: 20px;
        height: 2px;
        background: #fff;
        position: relative;
    }

    .hamburger::before,
    .hamburger::after {
        content: "";
        width: 20px;
        height: 2px;
        background: #fff;
        position: absolute;
        left: 0;
    }
    .hamburger::before { top: -6px; }
    .hamburger::after  { top:  6px; }

    .dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 45px;
        background: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        border-radius: 8px;
        overflow: hidden;
        min-width: 160px;
        z-index: 10;
    }

    .dropdown a {
        display: block;
        padding: 12px 16px;
        text-decoration: none;
        color: #333;
        transition: background 0.2s;
    }
    .dropdown a:hover { background: #ffe0b3; }
    .menu-container.open .dropdown { display: block; }

    /* Conteúdo central */
    .container {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        margin-top: 40px;
    }
    h1 {
        color: #ff6600;
        font-size: 3em;
        font-weight: 700;
        margin-bottom: 5px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    h2 {
        font-size: 1.2em;
        color: #666;
        margin-bottom: 25px;
        font-weight: 400;
    }

    /* Barra de pesquisa */
    .search-bar {
        background-color: #ffffff;
        padding: 10px;
        border-radius: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 600px;
        display: flex;
        align-items: center;
        transition: box-shadow 0.3s ease;
        margin-bottom: 20px;
    }
    .search-bar:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
    .search-bar input[type="text"] {
        flex-grow: 1;
        border: none;
        outline: none;
        font-size: 1.1em;
        padding: 0 15px;
        color: #333;
        background-color: transparent;
    }
    .search-bar button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0 15px;
    }
    .search-bar button img {
        height: 20px;
        opacity: 0.6;
        transition: opacity 0.2s;
    }
    .search-bar button:hover img { opacity: 1; }

    .about {
        font-size: 1em;
        color: #444;
        max-width: 600px;
        margin-top: 10px;
        line-height: 1.5em;
    }

    footer {
        padding: 15px;
        font-size: 0.9em;
        color: #888;
        text-align: center;
    }
    .highlight { color: #ff6600; font-weight: bold; }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex; align-items: center; justify-content: center;
    }
    .modal-content {
        background: #fff;
        padding: 20px 30px;
        border-radius: 10px;
        text-align: center;
        font-family: 'Roboto', sans-serif;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .modal-content h3 { color: #ff6600; margin-bottom: 15px; }
</style>
</head>
<body>

<?php if ($needsUpdate): ?>
<div id="updateModal" class="modal" style="display:flex;">
    <div class="modal-content">
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

<!-- Topo -->
<div class="topbar">
    <div class="menu-container" id="menu">
        <button class="menu-btn">
            <span>CJR Cloud</span>
            <div class="hamburger"></div>
        </button>
        <div class="dropdown">
            <a href="register.php">Cadastro</a>
            <a href="login.php">Login</a>
        </div>
    </div>
    <a class="shortcut-btn" href="sobre.html">Sobre</a>
</div>

<!-- Conteúdo -->
<div class="container">
    <h1>Centro da Juventude Restinga</h1>
    <h2>powered by Sophira</h2>

    <form action="https://www.google.com/search" method="get" class="search-bar">
        <input type="text" name="q" placeholder="Pesquisar no Google..." autofocus>
        <button type="submit">
            <img src="search.png" alt="Pesquisar">
        </button>
    </form>

    <p class="about">
        Bem-vindo à plataforma oficial do <span class="highlight">Centro da Juventude Restinga</span>.  
        Aqui você encontra informações, ferramentas e serviços digitais desenvolvidos para a comunidade, 
        com foco em aprendizado, inovação e inclusão.  
    </p>
</div>

<footer>
    Criado por <span class="highlight">Matheus Pereira da Silva</span><br>
    <span class="highlight">Centro da Juventude Restinga @2025</span>
</footer>

<script>
document.getElementById("menu").querySelector(".menu-btn").addEventListener("click", function() {
    document.getElementById("menu").classList.toggle("open");
});
</script>
</body>
</html>
