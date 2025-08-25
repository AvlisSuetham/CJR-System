# Neno Drive

Neno Drive é um sistema de gerenciamento de arquivos e usuários, desenvolvido para fins escolares. Ele permite que alunos, professores e administradores cadastrem-se, façam upload de arquivos, visualizem pastas e gerenciem senhas de forma segura e organizada.

---

## Funcionalidades

* **Cadastro de usuários**
  Alunos podem se cadastrar diretamente no sistema.
  Professores e administradores podem gerenciar senhas de alunos.

* **Login seguro**
  Sistema de login com senha criptografada utilizando `password_hash()`.

* **Dashboard personalizado**

  * Alunos: acessam apenas seus próprios arquivos.
  * Professores: acessam todas as pastas e podem pesquisar pastas de alunos.
  * Administradores: acesso completo a todas as pastas e arquivos.

* **Upload e gerenciamento de arquivos**

  * Cada usuário tem sua própria pasta de uploads.
  * Professores e administradores podem visualizar e excluir arquivos.
  * Alunos podem visualizar e excluir apenas seus próprios arquivos.

* **Alteração de senha**

  * Alunos podem alterar suas próprias senhas.
  * Professores e administradores podem alterar a senha de qualquer aluno.

* **Interface moderna e responsiva**

  * Tema laranja consistente.
  * Layout limpo e profissional.
  * Botões com efeitos de hover e sombras sutis.

---

## Tecnologias Utilizadas

* PHP 8+
* MySQL / MariaDB (via XAMPP)
* HTML5 / CSS3
* Fontes do Google Fonts (Roboto)
* Sessions para autenticação

---

## Estrutura de Pastas

```
neno-drive/
│
├─ index.php          # Tela inicial com título e botões de login/cadastro
├─ register.php       # Cadastro de usuários
├─ login.php          # Tela de login
├─ dashboard.php      # Área principal do usuário
├─ alterar_senha.php  # Alteração de senha do usuário logado
├─ alterar_senha_aluno.php # Professores/Admin podem alterar senha de alunos
├─ db.php             # Conexão com banco de dados
├─ uploads/           # Pasta para armazenar arquivos dos usuários
└─ README.md          # Este arquivo
```

---

## Banco de Dados

**Tabela `usuarios`**:

```sql
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `senha` varchar(255) NOT NULL,
  `type` enum('user','professor','admin') DEFAULT 'user',
  PRIMARY KEY (`id`)
);
```

---

## Como Usar

1. Instale o [XAMPP](https://www.apachefriends.org/) ou outro servidor local PHP + MySQL.
2. Crie um banco de dados e importe a tabela `usuarios`.
3. Configure a conexão com o banco em `db.php`.
4. Coloque todos os arquivos na pasta `htdocs` do XAMPP.
5. Acesse `http://localhost/neno-drive/` pelo navegador.
6. Faça o cadastro ou login e comece a usar o sistema.

---

## Autor

**Matheus Pereira da Silva (Suetham)**
Neno Drive - Sistema de gerenciamento de arquivos escolares.

---

## Licença

Este projeto é gratuito e pode ser utilizado e modificado livremente para fins educacionais.
