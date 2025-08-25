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

        // admin/professor podem tudo
        if ($_SESSION['type'] === 'admin' || $_SESSION['type'] === 'professor') {
            $podeExcluir = true;
        }
        // user só pode excluir o que é dele
        elseif ($_SESSION['type'] === 'user' && $donoArquivo === $_SESSION['username']) {
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

/** Lista arquivos de uma pasta, mostrando botão excluir quando permitido */
function listarArquivos(string $pasta, string $tipoUsuario, string $usuarioLogado, string $dono) {
    if (!is_dir($pasta)) return;

    $itens = scandir($pasta);
    foreach ($itens as $arq) {
        if ($arq === "." || $arq === "..") continue;

        $caminho = $pasta . $arq;
        if (!is_file($caminho)) continue;

        $href = $pasta . rawurlencode($arq);
        $label = htmlspecialchars($arq, ENT_QUOTES, 'UTF-8');

        echo "<li>
                <a href='{$href}' download>{$label}</a>";

        $podeExcluir = false;
        if ($tipoUsuario === 'admin' || $tipoUsuario === 'professor') {
            $podeExcluir = true;
        } elseif ($tipoUsuario === 'user' && $dono === $usuarioLogado) {
            $podeExcluir = true;
        }

        if ($podeExcluir) {
            $hiddenPath = htmlspecialchars($caminho, ENT_QUOTES, 'UTF-8');
            $hiddenDono = htmlspecialchars($dono, ENT_QUOTES, 'UTF-8');
            echo " <form method='POST' style='display:inline; margin-left:10px;'>
                    <input type='hidden' name='excluir' value='{$hiddenPath}'>
                    <input type='hidden' name='dono' value='{$hiddenDono}'>
                    <button type='submit' onclick=\"return confirm('Tem certeza que deseja excluir este arquivo?')\">Excluir</button>
                   </form>";
        }

        echo "</li>";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #fff3e0; /* laranja muito claro */
            margin: 0; 
            padding: 0; 
        }

        header { 
            background: #e65100; /* laranja escuro */
            color: white; 
            padding: 15px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }

        header h2 { 
            margin: 0; 
        }

        .btn { 
            padding: 8px 15px; 
            border: none; 
            border-radius: 5px; 
            text-decoration: none; 
            font-weight: bold; 
            cursor: pointer; 
        }

        .btn-logout { 
            background: #ff7043; /* laranja suave */
            color: white; 
        }

        .btn-password { 
            background: #ff9800; /* laranja vibrante */
            color: white; 
            margin-left: 10px; 
        }

        .container { 
            max-width: 900px; 
            margin: 30px auto; 
            background: #fff8f0; /* fundo do card em laranja claro */
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 8px rgba(255, 87, 34, 0.2); /* sombra laranja suave */
        }

        form { 
            margin: 10px 0; 
        }

        input[type="file"] { 
            margin-bottom: 10px; 
        }

        button { 
            background: #ff7043; /* laranja médio */
            color: white; 
            padding: 6px 12px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }

        form button { 
            background: #ff5722; /* laranja forte */
        }

        h3 { 
            margin-top: 30px; 
            border-bottom: 2px solid #ff9800; /* laranja vibrante */
            padding-bottom: 5px; 
        }

        ul { 
            list-style: none; 
            padding: 0; 
        }

        ul li { 
            padding: 5px 0; 
        }

        ul li strong { 
            color: #e65100; /* laranja escuro */
        }

        .msg { 
            margin: 10px 0; 
            padding: 10px; 
            border-radius: 5px; 
            background: #ffe0b2; /* laranja claro para mensagens */
        }

        .search-box { 
            margin: 15px 0; 
            padding: 10px; 
            background: #fff3e0; 
            border: 1px solid #ffb74d; 
            border-radius: 8px; 
        }

        .search-box input { 
            padding: 8px; 
            border: 1px solid #ffb74d; 
            border-radius: 5px; 
            width: 220px; 
        }

        .search-box button { 
            margin-left: 5px; 
            background: #ff9800; 
            color: white;
        }

        .muted { 
            color: #bf360c; /* laranja escuro para texto secundário */
            font-size: 0.9em; 
        }
    </style>
</head>
<body>
<header>
    <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['type']); ?>)</h2>
    <div>
        <a class="btn btn-password" href="alterar_senha.php">Alterar Senha</a>

        <?php if ($_SESSION['type'] === 'professor'): ?>
            <a class="btn btn-password" href="alterar_senha_aluno.php">Senha de Alunos</a>
        <?php endif; ?>

        <a class="btn btn-logout" href="logout.php">Sair</a>
    </div>
</header>

<div class="container">
    <?php if (!empty($msg)) echo "<div class='msg'>{$msg}</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <p><strong>Selecione um arquivo para enviar:</strong></p>
        <input type="file" name="arquivo" required>
        <button type="submit">Enviar</button>
    </form>

    <h3>Arquivos disponíveis:</h3>
    <ul>
        <?php
        $meuTipo = $_SESSION['type'];
        $meuUser = $_SESSION['username'];

        if ($meuTipo === 'user') {
            // 1) Minha pasta
            listarArquivos($userDir, $meuTipo, $meuUser, $meuUser);

            // 2) Pastas de professores (somente leitura)
            $pastas = scandir($baseDir);
            foreach ($pastas as $pasta) {
                if ($pasta === "." || $pasta === ".." || $pasta === $meuUser) continue;
                $full = $baseDir . $pasta . "/";
                if (!is_dir($full)) continue;

                $tipoDono = getUserType($conn, $pasta);
                if ($tipoDono === 'professor') {
                    echo "<li><strong>Pasta de " . htmlspecialchars($pasta) . " <span class='muted'>(somente leitura)</span></strong><ul>";
                    listarArquivos($full, $meuTipo, $meuUser, $pasta);
                    echo "</ul></li>";
                }
            }

        } elseif ($meuTipo === 'professor') {
            // 1) Minha pasta
            listarArquivos($userDir, $meuTipo, $meuUser, $meuUser);

            // 2) Barra de pesquisa de aluno (apenas mostra se informado)
            echo "<div class='search-box'>
                    <form method='GET'>
                        <label>Pesquisar aluno:</label>
                        <input type='text' name='aluno' placeholder='Digite o nome exato do aluno' value='". (isset($_GET['aluno']) ? htmlspecialchars($_GET['aluno']) : "") ."'>
                        <button type='submit'>Buscar</button>
                    </form>
                    <div class='muted'>Dica: o nome do aluno é o mesmo da pasta/username.</div>
                  </div>";

            if (isset($_GET['aluno']) && $_GET['aluno'] !== '') {
                $aluno = $_GET['aluno'];
                $pastaAluno = $baseDir . $aluno . "/";

                $tipoAluno = getUserType($conn, $aluno);
                if ($tipoAluno === 'user' && is_dir($pastaAluno)) {
                    echo "<li><strong>Pasta do aluno " . htmlspecialchars($aluno) . "</strong><ul>";
                    listarArquivos($pastaAluno, $meuTipo, $meuUser, $aluno);
                    echo "</ul></li>";
                } else {
                    echo "<p>🚫 Nenhum aluno encontrado com este nome.</p>";
                }
            }

        } else { // admin
            // Admin vê todas as pastas
            $pastas = scandir($baseDir);
            foreach ($pastas as $pasta) {
                if ($pasta === "." || $pasta === "..") continue;
                $full = $baseDir . $pasta . "/";
                if (!is_dir($full)) continue;

                $tipoDono = getUserType($conn, $pasta);
                $rotulo = $tipoDono ? " ($tipoDono)" : "";
                echo "<li><strong>Pasta de " . htmlspecialchars($pasta) . htmlspecialchars($rotulo) . "</strong><ul>";
                listarArquivos($full, $meuTipo, $meuUser, $pasta);
                echo "</ul></li>";
            }
        }
        ?>
    </ul>
</div>
</body>
</html>
