<?php
session_start();
include 'db.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $type = "user"; // usuário padrão

    // Verificar se o username já existe
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE username=?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $msg = "❌ Usuário já existe!";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (username, senha, type) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $senha, $type);

        if ($stmt->execute()) {
            // Criar sessão automaticamente
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['type'] = $type;

            // Redireciona direto para o dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $msg = "❌ Erro ao cadastrar: " . $stmt->error;
        }

        $stmt->close();
    }

    $stmt_check->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff5e6; /* fundo laranja suave */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fffaf0;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(255,140,0,0.3);
            text-align: center;
            width: 350px;
            border: 1px solid #ffa500;
        }

        h2 {
            color: #ff6600;
            margin-bottom: 20px;
            font-size: 2em;
            text-transform: uppercase;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ffa500;
            border-radius: 5px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #ff8c00;
            outline: none;
        }

        button[type="submit"] {
            background-color: #ff6600;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #ff8c00;
        }

        .message {
            margin-top: 20px;
            font-size: 1em;
            color: #8b4000;
            font-weight: bold;
        }

        .login-link {
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
        }

        .login-link a {
            color: #ff6600;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Cadastro</h2>

    <?php if(!empty($msg)) echo "<div class='message'>{$msg}</div>"; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Usuário" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Cadastrar</button>
    </form>

    <div class="login-link">
        Já tem uma conta? <a href="login.php">Fazer login</a>
    </div>
</div>

</body>
</html>