<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Banco de Dados dos Alunos</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
    /* Reset básico */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Roboto', Arial, sans-serif;
        background: linear-gradient(135deg, #fff5e6, #ffe0b3);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .container {
        background-color: #ffffff;
        padding: 50px 40px;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        text-align: center;
        width: 450px;
        max-width: 90%;
        transition: transform 0.3s;
    }

    .container:hover {
        transform: translateY(-5px);
    }

    h1 {
        color: #ff6600;
        font-size: 2.8em;
        margin-bottom: 15px;
        font-weight: 700;
    }

    p.description {
        font-size: 1.05em;
        color: #555;
        margin-bottom: 40px;
        line-height: 1.6;
    }

    .btn {
        display: block;
        width: 100%;
        padding: 15px;
        margin: 15px 0;
        background: linear-gradient(135deg, #ff6600, #ff8c00);
        color: #fff;
        font-size: 1.2em;
        font-weight: 700;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(255,140,0,0.3);
    }

    .btn:hover {
        background: linear-gradient(135deg, #ff8c00, #ffa500);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255,140,0,0.35);
    }

    footer {
        margin-top: 30px;
        font-size: 0.9em;
        color: #888;
    }
</style>
</head>
<body>

<div class="container">
    <h1>Banco de Dados dos Alunos</h1>
    <p class="description">
        Bem-vindo ao sistema de gerenciamento escolar!<br>
        Cadastre usuários, envie e organize arquivos, e acompanhe o progresso dos alunos de forma segura e intuitiva.<br>
        Professores e administradores têm acesso completo às pastas, enquanto alunos podem visualizar apenas seus próprios arquivos.
    </p>

    <a class="btn" href="register.php">Cadastrar</a>
    <a class="btn" href="login.php">Login</a>

    <footer>
        Criado por <span class="author">Matheus Pereira da Silva (Suetham)</span>
    </footer>

    <style>
        footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #888;
        }

        footer .author {
            color: #ff6600; /* Laranja */
            font-weight: bold;
        }
    </style>
</div>
</body>
</html>
