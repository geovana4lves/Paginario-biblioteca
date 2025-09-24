<?php
session_start();
require_once '../../db/conexao.php';

// Verificar se é admin
if (!isset($_SESSION['user_nome']) || $_SESSION['user_tipo'] !== 'admin') {
    header('Location: ../../entrar-administrador.php');
    exit();
}

$sucesso = '';
$erro = '';

// Buscar gêneros
$busca = $_GET['busca'] ?? '';
$sql = "SELECT g.*, COUNT(l.id_livro) as total_livros 
        FROM genero g 
        LEFT JOIN livro l ON g.id_genero = l.genero_id";

if ($busca) {
    $sql .= " WHERE g.nome_genero LIKE :busca";
}

$sql .= " GROUP BY g.id_genero ORDER BY g.nome_genero";

try {
    $stmt = $conexao->prepare($sql);
    if ($busca) {
        $stmt->bindValue(':busca', "%$busca%");
    }
    $stmt->execute();
    $generos = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro = "Erro ao buscar gêneros: " . $e->getMessage();
}

// Adicionar novo gênero
if ($_POST && isset($_POST['adicionar'])) {
    $nome_genero = trim($_POST['nome_genero']);
    
    if (empty($nome_genero)) {
        $erro = "O nome do gênero é obrigatório.";
    } else {
        try {
            // Verificar se gênero já existe
            $stmt = $conexao->prepare("SELECT id_genero FROM genero WHERE nome_genero = ?");
            $stmt->execute([$nome_genero]);
            
            if ($stmt->fetch()) {
                throw new Exception("Já existe um gênero com este nome.");
            }
            
            // Inserir gênero
            $stmt = $conexao->prepare("INSERT INTO genero (nome_genero) VALUES (?)");
            $stmt->execute([$nome_genero]);
            
            $sucesso = "Gênero '$nome_genero' adicionado com sucesso!";
            
            // Recarregar a lista
            header('Location: listar.php?sucesso=' . urlencode($sucesso));
            exit();
            
        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
    }
}

// Editar gênero
if ($_POST && isset($_POST['editar'])) {
    $id_genero = $_POST['id_genero'];
    $nome_genero = trim($_POST['nome_genero_edit']);
    
    if (empty($nome_genero)) {
        $erro = "O nome do gênero é obrigatório.";
    } else {
        try {
            // Verificar se já existe outro gênero com este nome
            $stmt = $conexao->prepare("SELECT id_genero FROM genero WHERE nome_genero = ? AND id_genero != ?");
            $stmt->execute([$nome_genero, $id_genero]);
            
            if ($stmt->fetch()) {
                throw new Exception("Já existe outro gênero com este nome.");
            }
            
            // Atualizar gênero
            $stmt = $conexao->prepare("UPDATE genero SET nome_genero = ? WHERE id_genero = ?");
            $stmt->execute([$nome_genero, $id_genero]);
            
            $sucesso = "Gênero atualizado com sucesso!";
            
            // Recarregar a lista
            header('Location: listar.php?sucesso=' . urlencode($sucesso));
            exit();
            
        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
    }
}

// Excluir gênero
if ($_POST && isset($_POST['excluir'])) {
    $id_genero = $_POST['id_genero'];
    
    try {
        // Verificar se há livros usando este gênero
        $stmt = $conexao->prepare("SELECT COUNT(*) FROM livro WHERE genero_id = ?");
        $stmt->execute([$id_genero]);
        $total_livros = $stmt->fetchColumn();
        
        if ($total_livros > 0) {
            throw new Exception("Não é possível excluir este gênero pois existem $total_livros livro(s) cadastrado(s) com ele.");
        }
        
        // Excluir gênero
        $stmt = $conexao->prepare("DELETE FROM genero WHERE id_genero = ?");
        $stmt->execute([$id_genero]);
        
        $sucesso = "Gênero excluído com sucesso!";
        
        // Recarregar a lista
        header('Location: listar.php?sucesso=' . urlencode($sucesso));
        exit();
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Gêneros - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #86541c, #E9A863);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .content {
            padding: 30px;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 300px;
        }

        .search-box input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        .add-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 2px solid #ddd;
        }

        .add-form h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #86541c;
            box-shadow: 0 0 0 3px rgba(134, 84, 28, 0.1);
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: inline-block;
            font-size: 1rem;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
            padding: 8px 12px;
            font-size: 0.85rem;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
            padding: 8px 12px;
            font-size: 0.85rem;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .genres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .genre-card {
            background: white;
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .genre-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: #86541c;
        }

        .genre-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .genre-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .genre-id {
            background: #f8f9fa;
            color: #666;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: normal;
        }

        .genre-stats {
            background: #e8f4fd;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-label {
            color: #2980b9;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .stat-value {
            color: #2980b9;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .genre-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-data-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }

        .close:hover {
            color: #000;
        }

        @media (max-width: 768px) {
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: none;
            }

            .form-row {
                flex-direction: column;
                align-items: stretch;
            }

            .genres-grid {
                grid-template-columns: 1fr;
            }

            .genre-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎭 Gerenciar Gêneros</h1>
            <p>Adicione, edite e gerencie os gêneros literários</p>
        </div>

        <div class="content">
            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success">
                    <strong>✅ Sucesso!</strong> <?= htmlspecialchars($_GET['sucesso']) ?>
                </div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erro:</strong> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <!-- Formulário para adicionar gênero -->
            <div class="add-form">
                <h3>➕ Adicionar Novo Gênero</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome_genero">Nome do Gênero</label>
                            <input type="text" id="nome_genero" name="nome_genero" 
                                   placeholder="Ex: Ficção Científica, Romance, Terror..."
                                   required maxlength="100">
                        </div>
                        <button type="submit" name="adicionar" value="1" class="btn btn-success">
                            ➕ Adicionar
                        </button>
                    </div>
                </form>
            </div>

            <div class="toolbar">
                <form method="GET" class="search-box">
                    <input type="text" name="busca" placeholder="Buscar gênero..." 
                           value="<?= htmlspecialchars($busca) ?>">
                    <button type="submit" class="btn btn-primary">🔍</button>
                </form>
                
                <a href="../../painel_admin.php" class="btn btn-back">🔙 Voltar</a>
            </div>

            <?php if (empty($generos)): ?>
                <div class="no-data">
                    <div class="no-data-icon">🎭</div>
                    <h3>Nenhum gênero encontrado</h3>
                    <?php if ($busca): ?>
                        <p>Tente uma busca diferente ou <a href="listar.php">visualize todos os gêneros</a>.</p>
                    <?php else: ?>
                        <p>Use o formulário acima para adicionar o primeiro gênero.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="genres-grid">
                    <?php foreach ($generos as $genero): ?>
                        <div class="genre-card">
                            <div class="genre-header">
                                <div class="genre-name">
                                    🎭 <?= htmlspecialchars($genero['nome_genero']) ?>
                                    <span class="genre-id">#<?= $genero['id_genero'] ?></span>
                                </div>
                            </div>

                            <div class="genre-stats">
                                <span class="stat-label">📚 Livros cadastrados:</span>
                                <span class="stat-value"><?= $genero['total_livros'] ?></span>
                            </div>

                            <div class="genre-actions">
                                <button onclick="editGenre(<?= $genero['id_genero'] ?>, '<?= htmlspecialchars($genero['nome_genero'], ENT_QUOTES) ?>')" 
                                        class="btn btn-warning">
                                    ✏️ Editar
                                </button>
                                
                                <?php if ($genero['total_livros'] == 0): ?>
                                    <button onclick="deleteGenre(<?= $genero['id_genero'] ?>, '<?= htmlspecialchars($genero['nome_genero'], ENT_QUOTES) ?>')" 
                                            class="btn btn-danger">
                                        🗑️ Excluir
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-danger" style="opacity: 0.5; cursor: not-allowed;" 
                                            title="Não pode ser excluído pois possui livros cadastrados">
                                        🔒 Excluir
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 30px; text-align: center; color: #666;">
                    <strong><?= count($generos) ?></strong> gênero(s) encontrado(s)
                    <?php if ($busca): ?>
                        para "<strong><?= htmlspecialchars($busca) ?></strong>"
                        | <a href="listar.php">Ver todos</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Editar Gênero</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" id="edit_id" name="id_genero">
                <div class="form-group">
                    <label for="nome_genero_edit">Nome do Gênero</label>
                    <input type="text" id="nome_genero_edit" name="nome_genero_edit" 
                           required maxlength="100">
                </div>
                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" name="editar" value="1" class="btn btn-warning">
                        💾 Salvar Alterações
                    </button>
                    <button type="button" onclick="closeModal()" class="btn btn-back">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Exclusão -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🗑️ Excluir Gênero</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div style="text-align: center; margin: 20px 0;">
                <p><strong>Tem certeza que deseja excluir o gênero:</strong></p>
                <p style="font-size: 1.2rem; color: #e74c3c; margin: 15px 0;" id="deleteGenreName"></p>
                <p style="color: #666; font-size: 0.9rem;">Esta ação não pode ser desfeita!</p>
            </div>
            <form method="POST">
                <input type="hidden" id="delete_id" name="id_genero">
                <div style="text-align: center;">
                    <button type="submit" name="excluir" value="1" class="btn btn-danger">
                        🗑️ Confirmar Exclusão
                    </button>
                    <button type="button" onclick="closeModal()" class="btn btn-back">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editGenre(id, nome) {
            document.getElementById('edit_id').value = id;
            document.getElementById('nome_genero_edit').value = nome;
            document.getElementById('editModal').style.display = 'block';
        }

        function deleteGenre(id, nome) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteGenreName').textContent = nome;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        }

        // Fechar modal com ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>

