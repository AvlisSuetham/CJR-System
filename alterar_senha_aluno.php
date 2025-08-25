<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Apenas professor e admin podem acessar
if (!in_array($_SESSION['type'], ['professor', 'admin'])) {
    die("🚫 Acesso negado.");
}

include 'db.php'; // Conexão com o banco

$msg = "";

// Buscar todos os alunos (type = 'user')
$alunos = [];
$result = $conn->query("SELECT username FROM usuarios WHERE type='user' ORDER BY username");
while ($row = $result->fetch_assoc()) {
    $alunos[] = $row['username'];
}

// Processar alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aluno'], $_POST['nova_senha'])) {
    $aluno = $_POST['aluno'];
    $novaSenha = $_POST['nova_senha'];

    if (in_array($aluno, $alunos) && !empty($novaSenha)) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE usuarios SET senha=? WHERE username=?");
        if (!$stmt) {
            die("Erro no prepare: " . $conn->error);
        }

        $stmt->bind_param("ss", $senhaHash, $aluno);
        if ($stmt->execute()) {
            $msg = "✅ Senha do aluno '$aluno' alterada com sucesso!";
        } else {
            $msg = "❌ Erro ao alterar a senha.";
        }
        $stmt->close();
    } else {
        $msg = "❌ Dados inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar senha do aluno</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #fff5e6; /* fundo laranja bem suave */
            margin:0; 
            padding:0; 
        }
        .container { 
            max-width: 500px; 
            margin: 50px auto; 
            background: #fffaf0; /* fundo da caixa */
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 12px rgba(255,140,0,0.3); /* sombra laranja */
            border: 1px solid #ffa500;
        }
        h2 { 
            text-align: center; 
            color: #ff6600; /* título laranja mais escuro */
        }
        form { 
            margin-top: 20px; 
        }
        select, input[type="password"], button { 
            width: 100%; 
            padding: 10px; 
            margin: 10px 0; 
            border-radius: 5px; 
            border: 1px solid #ffa500; 
        }
        button { 
            background: #ff8c00; 
            color: white; 
            border: none; 
            cursor: pointer; 
            font-weight: bold;
            transition: background 0.3s;
        }
        button:hover {
            background: #ff7000;
        }
        .msg { 
            padding: 10px; 
            background: #ffe5b4; 
            border-radius: 5px; 
            margin-bottom: 15px; 
            color: #8b4000;
        }
        a { 
            text-decoration: none; 
            color: #ff6600; 
            display: block; 
            text-align: center; 
            margin-top: 15px; 
            font-weight: bold;
        }
        a:hover {
            color: #ff4500;
        }
        label {
            font-weight: bold;
            color: #ff6600;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Alterar senha do aluno</h2>

    <?php if(!empty($msg)) echo "<div class='msg'>{$msg}</div>"; ?>

    <form method="POST">
        <label>Selecione o aluno:</label>
        <select name="aluno" required>
            <option value="">-- Escolha o aluno --</option>
            <?php foreach($alunos as $a): ?>
                <option value="<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Nova senha:</label>
        <input type="password" name="nova_senha" placeholder="Digite a nova senha" required>

        <button type="submit">Alterar senha</button>
    </form>

    <a href="dashboard.php">⬅ Voltar ao Dashboard</a>
</div>
</body>
</html>