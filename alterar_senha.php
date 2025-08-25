<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha</title>
    <style>
        /* Estilos CSS */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffe8d6; /* Um laranja muito claro para o fundo */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            padding: 50px; /* Aumenta o padding para mais espaço */
            border-radius: 12px; /* Bordas mais suaves */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); /* Sombra mais destacada */
            text-align: center;
            width: 380px; /* Aumenta a largura do container */
        }

        h2 {
            color: #ff5722; /* Laranja mais vivo e vibrante */
            margin-bottom: 25px;
            font-size: 2.2em;
            text-transform: uppercase;
            letter-spacing: 1px; /* Espaçamento entre letras para melhor leitura */
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 18px; /* Aumenta o espaçamento entre os campos */
        }

        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 15px; /* Aumenta o padding dos inputs */
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.4s ease-in-out;
            font-size: 1em;
        }

        input[type="password"]:focus,
        input[type="text"]:focus {
            border-color: #ff9800; /* Laranja vibrante no foco */
            outline: none;
        }

        button[type="submit"] {
            background-color: #ff5722;
            color: #fff;
            padding: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: bold;
            transition: background-color 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        button[type="submit"]:hover {
            background-color: #ff7043; /* Laranja levemente mais claro no hover */
        }

        .message {
            margin-top: 25px;
            font-size: 1em;
            font-weight: bold;
        }

        .message.error {
            color: #f44336; /* Vermelho mais suave */
        }

        .message.success {
            color: #4caf50; /* Verde mais suave */
        }

        .link-back {
            margin-top: 25px;
            font-size: 1em;
        }

        .link-back a {
            color: #ff5722;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .link-back a:hover {
            color: #ff9800;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <?php
    session_start();
    include 'db.php';

    // Verifica se o usuário está logado
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];
        $confirma = $_POST['confirma'];

        // Confere se nova senha e confirmação batem
        if ($nova_senha !== $confirma) {
            echo "<div class='message error'>A nova senha e a confirmação não coincidem!</div>";
        } else {
            // Busca a senha atual no banco
            $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id=?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $stmt->bind_result($senha_hash);
            $stmt->fetch();
            $stmt->close();

            // Verifica senha atual
            if (!password_verify($senha_atual, $senha_hash)) {
                echo "<div class='message error'>Senha atual incorreta!</div>";
            } else {
                // Atualiza a senha
                $nova_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE usuarios SET senha=? WHERE id=?");
                $stmt->bind_param("si", $nova_hash, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    // Redireciona para o dashboard após o sucesso
                    header("Location: dashboard.php");
                    exit();
                } else {
                    echo "<div class='message error'>Erro ao atualizar senha!</div>";
                }
                $stmt->close();
            }
        }
    }
    ?>

    <h2>Alterar Senha</h2>
    <form method="POST">
        <input type="password" name="senha_atual" placeholder="Senha atual" required>
        <input type="password" name="nova_senha" placeholder="Nova senha" required>
        <input type="password" name="confirma" placeholder="Confirmar nova senha" required>
        <button type="submit">Alterar</button>
    </form>
    
    <div class="link-back">
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </div>
</div>

</body>
</html>