# CJR-System

![CJR-System](https://img.shields.io/badge/CJR--System-ready-brightgreen) ![PHP](https://img.shields.io/badge/PHP-%3E%3D8.0-8892BF) ![XAMPP](https://img.shields.io/badge/XAMPP-local-orange)

**CJR-System** é um sistema de gerenciamento de arquivos desenvolvido em PHP, pensado especialmente para uso educacional. Ele reúne funções essenciais como cadastro e autenticação de usuários, CRUD de usuários, upload/gestão de arquivos e um **painel inicial com ferramentas úteis para alunos**.

---

## Sobre o projeto

Desenvolvido por **Matheus Pereira da Silva** (CEO da Sophira) para o **Centro da Juventude e Restinga**.

Mais sobre o desenvolvedor e outros projetos: [https://sophira.me](https://sophira.me)

---

## Funcionalidades principais

* Cadastro e autenticação de usuários (login / logout).
* CRUD completo de usuários (criar, ler, atualizar, remover).
* Upload, download e exclusão de arquivos.
* Organização de arquivos por pastas e permissões básicas por usuário.
* Interface inicial (dashboard) com ferramentas úteis voltadas para alunos.
* Pronto para rodar em ambiente local com XAMPP.

---

## Tecnologias

* PHP (procedural / MVC leve — adapte ao seu estilo)
* MySQL / MariaDB (via XAMPP)
* HTML / CSS / JavaScript (front-end básico)

---

## Requisitos

* Sistema operacional: Windows (recomendado para XAMPP), Linux ou macOS.
* XAMPP (Apache + PHP + MySQL). Use uma versão do XAMPP que contenha PHP compatível com o projeto (ex.: PHP 8.x).
* Navegador moderno (Chrome, Firefox, Edge).

---

## Instalação (Rápido — XAMPP no Windows)

**1. Instale o XAMPP**

* Baixe em [https://www.apachefriends.org](https://www.apachefriends.org) e instale.
* Abra o XAMPP Control Panel e inicie **Apache** e **MySQL**.

**2. Copie o projeto para `htdocs`**

* Coloque a pasta `cjr-system` em `C:/xampp/htdocs/cjr-system`.

**3. Crie o banco de dados**

* Acesse `http://localhost/phpmyadmin/` e crie o banco `cjr_system` (utf8mb4\_unicode\_ci).
* Importe `database/dba.sql` (se existir) ou execute os scripts em `database/`.

**4. Ajuste as configurações**

* Abra `config.php` (ou `app/config.php`) e configure as credenciais do banco.

```php
// Exemplo de config.php
$db_host = '127.0.0.1';
$db_name = 'cjr_system';
$db_user = 'root';
$db_pass = ''; // senha padrão do XAMPP é vazia

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erro ao conectar ao banco: ' . $e->getMessage());
}
```

**5. Crie a pasta de uploads**

* `C:/xampp/htdocs/cjr-system/uploads`
* Garanta permissão de escrita (no Windows normalmente não é necessário alterar).

**6. Abra a aplicação**

* Acesse: `http://localhost/cjr-system/` e registre-se ou faça login.

---

## Estrutura sugerida de pastas

```
cjr-system/
├─ app/
│  ├─ controllers/
│  ├─ models/
│  └─ views/
├─ public/
│  ├─ assets/
│  └─ index.php
├─ uploads/
├─ database/
│  └─ dba.sql
├─ config.php
└─ README.md
```

---

## Boas práticas de segurança

* Nunca use `root` com senha vazia em produção.
* Armazene senhas com `password_hash()` (bcrypt) e verifique com `password_verify()`.
* Proteja contra SQL Injection (use prepared statements) e XSS (escape em saídas).
* Valide tipos e tamanho de arquivos no upload.
* Não exponha caminhos absolutos em downloads; sirva arquivos via script que verifica permissões.
* Use HTTPS em produção.

---

## Dicas rápidas para debug

* Erro de conexão: verifique `config.php` e se o MySQL está rodando.
* Uploads falhando: confira `upload_max_filesize` e `post_max_size` no `php.ini` e permissões da pasta `uploads`.
* Error 500 / tela branca: habilite display de erros em ambiente de desenvolvimento ou cheque `apache/error.log`.

---

## Contribuições

Contribuições são bem-vindas:

1. Fork do repositório.
2. `git checkout -b feat/nova-funcionalidade`
3. `git commit -m "Descrição do que foi feito"`
4. Abra um Pull Request.

---

## Recursos futuros (ideias)

* Roles & permissions mais detalhados.
* API REST para gerenciar arquivos.
* Versões e histórico de arquivos.
* Integração com serviços de nuvem (opcional).

---

## Créditos e contato

Desenvolvido por **Matheus Pereira da Silva** (CEO da Sophira) para o **Centro da Juventude e Restinga**.

Website: [https://sophira.me](https://sophira.me)

E-mail de contato: **[matheuspsghx@gmail.com](mailto:matheuspsghx@gmail.com)**

---

## Licença

Escolha uma licença apropriada (ex.: MIT):

```
MIT License
Copyright (c) YEAR Matheus Pereira da Silva
```

---