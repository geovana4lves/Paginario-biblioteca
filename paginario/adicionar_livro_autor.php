<?php
session_start();
require_once 'db/conexao.php';

// Verificar se usuário está logado (pode ser admin ou usuário comum que é autor)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_cpf'])) {
    header('Location: entrar-usuario.php');
    exit();
}

$errors = [];
$success = false;
$titulo = $autor = $ano_publicacao = $editor = $genero = $formato = $sinopse = "";
$genero_nome = ""; // Inicializar variável do gênero
$classificacao_indicativa = "";
$capa_path = "";
$link_arquivo_path = "";

// Buscar gêneros disponíveis para o dropdown
$generos_disponveis = [];
try {
    // Verificar se a conexão está disponível
    if (!$conexao) {
        throw new Exception("Erro na conexão com o banco de dados.");
    }
    
    $stmt_generos = $conexao->prepare("SELECT id_genero, nome_genero FROM genero ORDER BY nome_genero");
    $stmt_generos->execute();
    $generos_disponveis = $stmt_generos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Erro ao carregar gêneros: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo'] ?? '');
    $autor = trim($_POST['autor'] ?? '');
    $ano_publicacao = trim($_POST['ano_publicacao'] ?? '');
    $editor = trim($_POST['editor'] ?? '');
    $genero_nome = trim($_POST['genero'] ?? '');
    $formato = trim($_POST['formato'] ?? '');
    $sinopse = trim($_POST['sinopse'] ?? '');
    $classificacao_indicativa = trim($_POST['classificacao_indicativa'] ?? '');

    if (!$titulo) $errors[] = "O campo título é obrigatório.";
    if (!$autor) $errors[] = "O campo autor é obrigatório.";
    if (!$genero_nome) $errors[] = "O campo gênero é obrigatório.";
    if (!$sinopse) $errors[] = "O campo sinopse é obrigatório.";
    if (!$classificacao_indicativa && $classificacao_indicativa !== '0') $errors[] = "A classificação indicativa é obrigatória.";

    if (isset($_FILES['capa']) && $_FILES['capa']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_types_img = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['capa']['type'], $allowed_types_img)) {
            if ($_FILES['capa']['size'] <= 2 * 1024 * 1024) { // 2MB
                $ext = pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION);
                $nome_arquivo = uniqid('capa_') . '.' . $ext;
                $destino = 'uploads/' . $nome_arquivo;
                if (!move_uploaded_file($_FILES['capa']['tmp_name'], $destino)) {
                    $errors[] = "Falha ao salvar a imagem da capa.";
                } else {
                    $capa_path = $destino;
                }
            } else {
                $errors[] = "A imagem da capa deve ter no máximo 2MB.";
            }
        } else {
            $errors[] = "Tipo de arquivo da capa inválido. Use JPG, PNG ou GIF.";
        }
    } else {
        $errors[] = "É obrigatório enviar uma imagem de capa.";
    }

    if (isset($_FILES['link_arquivo']) && $_FILES['link_arquivo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_types_file = ['application/pdf', 'application/epub+zip'];
        if (in_array($_FILES['link_arquivo']['type'], $allowed_types_file)) {
            if ($_FILES['link_arquivo']['size'] <= 10 * 1024 * 1024) { // 10MB
                $ext = pathinfo($_FILES['link_arquivo']['name'], PATHINFO_EXTENSION);
                $nome_pdf = uniqid('livro_') . '.' . $ext;
                $destino_pdf = 'uploads/' . $nome_pdf;
                if (!move_uploaded_file($_FILES['link_arquivo']['tmp_name'], $destino_pdf)) {
                    $errors[] = "Falha ao salvar o arquivo do livro.";
                } else {
                    $link_arquivo_path = $destino_pdf;
                }
            } else {
                $errors[] = "O arquivo do livro deve ter no máximo 10MB.";
            }
        } else {
            $errors[] = "O arquivo do livro deve ser PDF ou EPUB.";
        }
    } else {
        $errors[] = "É obrigatório enviar o arquivo PDF ou EPUB do livro.";
    }

    $genero_id = null;
    if ($genero_nome) {
        // Verificar se a conexão está disponível
        if (!$conexao) {
            $errors[] = "Erro na conexão com o banco de dados.";
        } else {
            $genero_stmt = $conexao->prepare('SELECT id_genero FROM genero WHERE nome_genero = ?');
            $genero_stmt->execute([$genero_nome]);
            $genero_result = $genero_stmt->fetch(PDO::FETCH_ASSOC);
            if ($genero_result) {
                $genero_id = $genero_result['id_genero'];
            } else {
                $errors[] = 'Gênero não encontrado.';
            }
        }
    }

    if (count($errors) === 0) {
        // Verificar se a conexão está disponível
        if (!$conexao) {
            $errors[] = "Erro na conexão com o banco de dados.";
        } else {
            $sql = "INSERT INTO Livro 
                (titulo, autor, ano_publicacao, editor, genero, formato, link_arquivo, sinopse, classificacao_indicativa, cpf_administrador, capa, genero_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
            $stmt = $conexao->prepare($sql);
            try {
                $stmt->execute([
                    $titulo,
                    $autor,
                    $ano_publicacao ?: null,
                    $editor ?: null,
                    $genero_nome, // Adicionar o nome do gênero
                    $formato ?: null,
                    $link_arquivo_path,
                    $sinopse,
                    $classificacao_indicativa,
                    $_SESSION['user_cpf'], // Pode ser admin ou usuário autor
                    $capa_path,
                    $genero_id
                ]);
                $success = true;
                header("Location: view/detalhes_livro.php?id=" . $conexao->lastInsertId());
                exit();
            } catch (PDOException $e) {
                $errors[] = "Erro ao cadastrar livro: " . $e->getMessage();
                if ($capa_path && file_exists($capa_path)) unlink($capa_path);
                if ($link_arquivo_path && file_exists($link_arquivo_path)) unlink($link_arquivo_path);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Cadastro de Livro - Autor</title>
    <style>
        * {margin:0; padding:0; box-sizing:border-box;}
        body, html {
            height:100%;
            color:#d6a65a;
            display:flex;
            flex-direction:column;
        }
        .background {
            background:url('img/image.png') no-repeat center center;
            background-size:cover;
            position:fixed;
            top:0; left:0;
            width:100vw; height:100vh;
            z-index:-1;
            filter:brightness(0.6);
        }
        .header {
            background:#86541c;
            padding:15px 20px;
            color:white;
            text-align:center;
            margin-bottom:20px;
        }
        .header h1 {
            font-size:28px;
            margin-bottom:5px;
        }
        .header p {
            font-size:16px;
            opacity:0.9;
        }
        main {
            flex:1;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:20px;
        }
        .registration-form {
            width:500px;
            max-width:95vw;
            background:#86541c;
            padding:30px 30px 40px;
            border-radius:15px;
            display:flex;
            flex-direction:column;
            gap:20px;
        }
        .registration-form h1 {
            font-size:36px;
            color:#d6a65a;
            font-weight:bold;
            text-align:center;
        }
        .custom-input {
            display:flex;
            align-items:center;
            background-color:#fff;
            border-radius:50px;
            border:1px solid #e9a863cc;
            padding:10px 15px;
            color:#9D9375;
            font-weight:bold;
            box-sizing:border-box;
            margin-bottom:10px;
        }
        .custom-input input, .custom-input textarea, .custom-input select {
            border:none;
            outline:none;
            font-size:1rem;
            color:#804D07;
            flex-grow:1;
            background:transparent;
            font-weight:bold;
            padding:0;
            min-width:0;
        }
        .registration-form button {
            background-color:#E9A863;
            color:#804D07;
            border:2px solid #fff;
            border-radius:50px;
            padding:12px 0;
            font-weight:800;
            font-size:20px;
            letter-spacing:1px;
            transition:background-color 0.3s ease;
            margin-top:10px;
            cursor:pointer;
        }
        .registration-form button:hover {
            background-color:#d1a25a;
        }
        .error-message {
            color:red;
            background-color:white;
            padding:8px;
            border-radius:50px;
            margin:5px 0;
            font-weight:bold;
            font-size:0.9rem;
            text-align:center;
        }
        .back-link {
            text-align:center;
            margin-top:20px;
        }
        .back-link a {
            color:#d6a65a;
            text-decoration:none;
            font-weight:bold;
        }
        .back-link a:hover {
            text-decoration:underline;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="header">
        <h1>📚 Publicar Seu Livro</h1>
        <p>Compartilhe sua obra com o mundo através da Biblioteca Paginário</p>
    </div>
    <main>
        <form class="registration-form" method="post" action="" enctype="multipart/form-data" autocomplete="off" novalidate>
            <h1>Dados do Livro</h1>
            <?php if (count($errors) > 0): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="custom-input">
                <input type="text" name="titulo" placeholder="Título da Obra" value="<?= htmlspecialchars($titulo) ?>" required />
            </div>
            <div class="custom-input">
                <input type="text" name="autor" placeholder="Nome do Autor" value="<?= htmlspecialchars($autor) ?>" required />
            </div>
            <div class="custom-input">
                <input type="number" name="ano_publicacao" placeholder="Ano da Publicação" value="<?= htmlspecialchars($ano_publicacao) ?>" />
            </div>
            <div class="custom-input">
                <input type="text" name="editor" placeholder="Editora" value="<?= htmlspecialchars($editor) ?>" />
            </div>
            <div class="custom-input">
                <select name="genero" required>
                    <option value="">Selecione o Gênero Literário</option>
                    <?php foreach ($generos_disponveis as $genero_opt): ?>
                        <option value="<?= htmlspecialchars($genero_opt['nome_genero']) ?>" 
                                <?= ($genero_nome == $genero_opt['nome_genero']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($genero_opt['nome_genero']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="custom-input">
                <input type="text" name="formato" placeholder="Formato (Ex: Paperback, Hardcover)" value="<?= htmlspecialchars($formato) ?>" />
            </div>
            <div class="custom-input">
                <input type="file" name="capa" accept="image/png, image/jpeg, image/gif" required />
            </div>
            <div class="custom-input">
                <input type="file" name="link_arquivo" accept="application/pdf,application/epub+zip" required />
            </div>
            <div class="custom-input">
                <textarea name="sinopse" placeholder="Sinopse do Livro" required><?= htmlspecialchars($sinopse) ?></textarea>
            </div>
            <div class="custom-input">
                <input type="number" name="classificacao_indicativa" placeholder="Classificação Indicativa (0-18 anos)" min="0" max="18" value="<?= htmlspecialchars($classificacao_indicativa) ?>" required />
            </div>
            <button type="submit">PUBLICAR LIVRO</button>
        </form>
    </main>
    <div class="back-link">
        <a href="view/inicio_autor.php">← Voltar para Biblioteca</a>
    </div>
</body>
</html>