<?php
session_start();
require_once 'db/conexao.php';

$errors = [];
$success = false;

$nome_livro = $nome_autor = $email = $sinopse = $indicativo_etario = "";

// Corrigido: usar user_id que contém o CPF do usuário
$cpf = $_SESSION['user_id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_livro = filter_input(INPUT_POST, 'nome_livro', FILTER_SANITIZE_STRING);
    $nome_autor = filter_input(INPUT_POST, 'autor', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $sinopse = filter_input(INPUT_POST, 'sinopse', FILTER_SANITIZE_STRING);
    $indicativo_etario = filter_input(INPUT_POST, 'indicativo_etario', FILTER_SANITIZE_STRING);

    if (!$cpf) {
        $errors[] = "Usuário não autenticado. Faça login para solicitar.";
    }

    if (!$nome_livro) {
        $errors[] = "O campo Nome do Livro é obrigatório.";
    }
    if (!$nome_autor) {
        $errors[] = "O campo Autor é obrigatório.";
    }
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "E-mail inválido ou não preenchido.";
    }
    if (!$sinopse) {
        $errors[] = "O campo Sinopse é obrigatório.";
    }
    if (!$indicativo_etario) {
        $errors[] = "O campo Indicativo Etário é obrigatório.";
    }

    if (count($errors) === 0) {
        // Verificar se a conexão é válida
        if ($conexao === null) {
            $errors[] = "Erro de conexão com o banco de dados.";
        } else {
            try {
                // Query corrigida para a estrutura REAL da tabela
                $sql = "INSERT INTO solicitacao (nome_livro, nome_autor, sinopse, indicativo_etario, cpf) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conexao->prepare($sql);
                $stmt->execute([$nome_livro, $nome_autor, $sinopse, $indicativo_etario, $cpf]);

                $success = true;
                $mensagem = "Solicitação cadastrada com sucesso!";
                $nome_livro = $nome_autor = $email = $sinopse = $indicativo_etario = "";
            } catch (PDOException $e) {
                $errors[] = "Erro ao cadastrar solicitação: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Solicitação de Livros</title>
    <link hrf="img">
    <link rel="stylesheet" href="img" />
    <style>
 * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Georgia, serif;
        background-color: #ecb87f;
        color: #FFF;
    }

        .menu-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
    }

    .menu-icon img {
        width: 40px;
        height: 40px;
        cursor: pointer;
    }

    .side-menu {
        position: fixed;
        top: 0;
        left: -250px;
        width: 250px;
        height: 100%;
        background-color: #86541c;
        padding-top: 60px;
        box-shadow: 2px 0 5px rgba(0,0,0,0.5);
        transition: left 0.3s ease;
        z-index: 1000;
    }

    .side-menu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .side-menu ul li {
        margin: 20px 0;
        text-align: center;
    }

    .side-menu ul li a {
        color: #eab97f;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.1em;
    }

.side-menu ul li a:hover {
  color: #ffffff;
  transition: color 0.3s ease, background-color 0.3s ease;
}

    .side-menu.open {
        left: 0;
    }

    .close-icon {
        position: absolute;
        top: 15px;
        right: 15px;
        cursor: pointer;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .close-icon img {
        width: 100%;
        height: 100%;
    }

    .topo {
        background: #845c26;
        padding: 20px;
        text-align: center;
        font-size: 28px;
        font-weight: bold;
        position: relative;
    }


        .livros {
            background: url('img/image.png') no-repeat center;
            background-size: cover;
            height: 150px;
        }

        .conteudo {
            padding: 20px;
            max-width: 700px;
            margin: 20px auto;
            background: #e8c8a1;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        .conteudo p {
            margin-bottom: 20px;
            font-size: 18px;
            align-items:center;
        }

    .main-footer {
      text-align: center;
      padding: 14px 0;
  background: #86541c;
      color: #fff;
      font-size: 0.9rem;
    }

    .main-footer a {
      color: #fff;
      text-decoration: none;
      margin: 0 6px;
    }

        .mensagem {
            background: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .registration-form {
            width: 100%;
            max-width: 95vw;
            background: #e8c8a1;
            padding: 30px 30px 40px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .registration-form h1 {
            font-size: 40px;
            color: #d6a65a;
            font-weight: bold;
            text-align: center;
        }
        .custom-input {
            display: flex;
            align-items: center;
            background-color: #fff;
            border-radius: 50px;
            border: 1px solid #e9a863cc;
            padding: 10px 15px;
            color: #9D9375;
            font-weight: bold;
            box-sizing: border-box;
        }
        .custom-input .icon {
            width: 26px;
            height: 26px;
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            margin-right: 10px;
        }
        .usuario { background-image: url('imgs/vector (1).svg'); }
        .senha { background-image: url('imgs/vector.svg'); }
        .email { background-image: url('imgs/image 2.png'); }
        .nome-livro { background-image: url('imgs/image 14.png'); }
        .sinopse { background-image: url('imgs/image 12.png'); }
        .nome-autor { background-image: url('imgs/image 13.png'); }

        .custom-input input {
            border: none;
            outline: none;
            font-size: 1rem;
            color: #804D07;
            flex-grow: 1;
            background: transparent;
            font-weight: bold;
            padding: 0;
            min-width: 0;
        }
        .custom-input input:focus {
            color: #131212ff;
            
        }
        .registration-form button {
            background-color: #E9A863;
            color: #845c26;
            border: 2px solid #f7cb97ff;
            border-radius: 50px;
            padding: 10px 0;
            font-weight: 400;
            font-size: 20px;
            letter-spacing: 1px;
            transition: background-color 0.3s ease;
            margin-top: 10px;
            cursor: pointer;
        }
        .registration-form button:hover {
            background-color: #d1a25a;
        }

        h1 {
  font-size: 30px;
  margin: 0;
  color: #fff;
}

     p {
  font-size: 30px;
  margin: 0;
  color: #352714ff;
}

h2 {
  margin: 5px 0 20px;
  font-size: 22px;
  text-transform: uppercase;
  color: #E9A863;
}

    </style>
</head>
<body>

<div class="topo">
            <div class="menu-icon">   
          <img src="img/component 1.svg" alt="Abrir Menu" />
        </div>
SOLICITAÇÃO DE LIVROS</div>
    <nav id="side-menu" class="side-menu">
          <div class="close-icon">
    <img src="img/component 1.svg" alt="Fechar Menu"/>
  </div>
  <ul>
    <li><a href="view/inicio.php" style="color: antiquewhite;">Página Inicial</a></li>
    <li><a style="color: peru;">------------------------------</a></li>
    <li><a style="color: antiquewhite;">Filtros</a></li>
    <li><a href="genero.php">Gênero</a></li>
    <li><a href="autores.php">Autor</a></li>
    <li><a href="editora.php">Editora</a></li>
    <li><a href="faixaetaria.php">Faixa Etária</a></li>
    <li><a style="color: peru;">------------------------------</a></li>
    <li><a href="solicitacao.php" style="color: antiquewhite;">Solicitação de livros</a></li>
     <li><a style="color: peru;">------------------------------</a></li>
    <li><a href="view/meuperfil.php" style="color: antiquewhite;">Meu Perfil</a></li>
  </ul>
</nav>
<div class="livros"></div>

<div class="conteudo">


    <p>Parece que o livro que você procura ainda não está disponível no sistema.
        Você pode solicitar sua inclusão preenchendo os campos abaixo:</p>

<form class="registration-form" method="post">
    <?php if ($success): ?>
        <div class="mensagem"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <?php if (count($errors) > 0): ?>
        <div class="mensagem" style="background: #f44336;">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="custom-input">
        <div class="icon nome-livro" aria-hidden="true"></div>
        <input type="text" name="nome_livro" placeholder="Nome do livro" required value="<?= htmlspecialchars($nome_livro) ?>" />
    </div>

    <div class="custom-input">
        <div class="icon nome-autor" aria-hidden="true"></div>
        <input type="text" name="autor" placeholder="Autor(a)" required value="<?= htmlspecialchars($nome_autor) ?>" />
    </div>

    <div class="custom-input">
        <div class="icon email" aria-hidden="true"></div>
        <input type="email" name="email" placeholder="E-mail" required value="<?= htmlspecialchars($email) ?>" />
    </div>

    <div class="custom-input">
        <div class="icon sinopse" aria-hidden="true"></div>
        <input type="text" name="sinopse" placeholder="Sinopse" required value="<?= htmlspecialchars($sinopse) ?>" />
    </div>

    <div class="custom-input">
        <div class="icon usuario" aria-hidden="true"></div>
        <input type="text" name="indicativo_etario" placeholder="Indicativo Etário" required value="<?= htmlspecialchars($indicativo_etario) ?>" />
    </div>

    <button type="submit">CADASTRAR</button>
</form>

</div>

  <footer class="main-footer">
    <a href="view/politicaprivacidade.html">Política de Privacidade</a> |
    <a href="view/politicaprivacidade.html">Termos de Uso</a> |
    <span>Todos os direitos reservados (BR)</span>
  </footer>

<script>
const menuIcon = document.querySelector('.menu-icon');
const sideMenu = document.getElementById('side-menu');
const closeIcon = document.querySelector('.close-icon');

menuIcon.addEventListener('click', () => {
    sideMenu.classList.add('open'); 
});

closeIcon.addEventListener('click', () => {
    sideMenu.classList.remove('open');
});
</script>

</body>
</html>