<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login de Usuário</title>
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

        .register-link {
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
        }

        .register-link a {
            color: #ff6600;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <?php
    session_start();
    include 'db.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST['username']);
        $senha = $_POST['senha'];

        $stmt = $conn->prepare("SELECT id, senha, type FROM usuarios WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hash, $type);
            $stmt->fetch();

            if (password_verify($senha, $hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['type'] = $type;
                header("Location: dashboard.php");
                exit();
            } else {
                echo "<div class='message' style='color: red;'>Senha incorreta!</div>";
            }
        } else {
            echo "<div class='message' style='color: red;'>Usuário não encontrado!</div>";
        }
        $stmt->close();
    }
    ?>

    <h2>Login</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Usuário" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>
    
    <div class="register-link">
        Não tem uma conta? <a href="register.php">Cadastre-se</a>
    </div>
</div>

</body>
</html>