<?php
session_start();
require_once '../../db/conexao.php';

// Verificar se é admin
if (!isset($_SESSION['user_nome']) || $_SESSION['user_tipo'] !== 'admin') {
    header('Location: ../../entrar-administrador.php');
    exit();
}

// Buscar solicitações
$sql = "SELECT s.*, u.nome_completo, u.email 
        FROM solicitacao s 
        INNER JOIN usuario u ON s.cpf = u.cpf 
        ORDER BY s.id_solicitacao DESC";

try {
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    $solicitacoes = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro = "Erro ao buscar solicitações: " . $e->getMessage();
}

// Excluir solicitação
if ($_POST && isset($_POST['excluir'])) {
    $id_solicitacao = $_POST['id_solicitacao'];
    
    try {
        $stmt = $conexao->prepare("DELETE FROM solicitacao WHERE id_solicitacao = ?");
        $stmt->execute([$id_solicitacao]);
        
        header('Location: listar.php?exclusao=sucesso');
        exit();
        
    } catch (Exception $e) {
        $erro = "Erro ao excluir solicitação: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações de Livros - Admin</title>
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
            max-width: 1200px;
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
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
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

        .solicitations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
        }

        .solicitation-card {
            background: white;
            border: 2px solid #eee;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
        }

        .solicitation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border-color: #86541c;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }

        .solicitation-id {
            background: #f8f9fa;
            color: #666;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .book-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .book-author {
            color: #7f8c8d;
            font-size: 1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .user-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .user-email {
            color: #666;
            font-size: 0.9rem;
        }

        .synopsis {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #3498db;
        }

        .synopsis-title {
            font-weight: 600;
            color: #2980b9;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .synopsis-text {
            line-height: 1.5;
            color: #555;
            font-size: 0.95rem;
        }

        .age-rating {
            display: inline-block;
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: bold;
            margin: 10px 0;
        }

        .age-rating.livre {
            background: #27ae60;
        }

        .age-rating.dez {
            background: #f39c12;
        }

        .card-actions {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #eee;
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

        @media (max-width: 768px) {
            .solicitations-grid {
                grid-template-columns: 1fr;
            }

            .card-header {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Solicitações de Livros</h1>
            <p>Gerencie as solicitações de novos livros dos usuários</p>
        </div>

        <div class="content">
            <?php if (isset($_GET['exclusao']) && $_GET['exclusao'] === 'sucesso'): ?>
                <div class="alert alert-success">
                    <strong>✅ Sucesso!</strong> Solicitação removida com sucesso.
                </div>
            <?php endif; ?>

            <?php if (isset($erro)): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erro:</strong> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <div class="toolbar">
                <div>
                    <h3 style="color: #2c3e50;">📊 Total: <?= count($solicitacoes) ?> solicitação(ões)</h3>
                </div>
                <a href="../../painel_admin.php" class="btn btn-back">🔙 Voltar ao Painel</a>
            </div>

            <?php if (empty($solicitacoes)): ?>
                <div class="no-data">
                    <div class="no-data-icon">📝</div>
                    <h3>Nenhuma solicitação encontrada</h3>
                    <p>Os usuários podem fazer solicitações de livros através do sistema.</p>
                </div>
            <?php else: ?>
                <div class="solicitations-grid">
                    <?php foreach ($solicitacoes as $solicitacao): ?>
                        <div class="solicitation-card">
                            <div class="card-header">
                                <div class="solicitation-id">#<?= $solicitacao['id_solicitacao'] ?></div>
                            </div>

                            <div class="book-title">
                                📚 <?= htmlspecialchars($solicitacao['nome_livro']) ?>
                            </div>

                            <div class="book-author">
                                ✍️ <?= htmlspecialchars($solicitacao['nome_autor']) ?>
                            </div>

                            <div class="user-info">
                                <div class="user-name">
                                    👤 <?= htmlspecialchars($solicitacao['nome_completo']) ?>
                                </div>
                                <div class="user-email">
                                    📧 <?= htmlspecialchars($solicitacao['email']) ?>
                                </div>
                            </div>

                            <div class="age-rating <?= 
                                strtolower($solicitacao['indicativo_etario']) == 'livre' ? 'livre' : 
                                (is_numeric($solicitacao['indicativo_etario']) && $solicitacao['indicativo_etario'] <= 10 ? 'dez' : '')
                            ?>">
                                🔞 <?= htmlspecialchars($solicitacao['indicativo_etario']) ?>
                            </div>

                            <div class="synopsis">
                                <div class="synopsis-title">📖 Sinopse:</div>
                                <div class="synopsis-text">
                                    <?= nl2br(htmlspecialchars($solicitacao['sinopse'])) ?>
                                </div>
                            </div>

                            <div class="card-actions">
                                <button onclick="confirmDelete(<?= $solicitacao['id_solicitacao'] ?>, '<?= htmlspecialchars($solicitacao['nome_livro'], ENT_QUOTES) ?>')" 
                                        class="btn btn-danger" title="Remover solicitação">
                                    🗑️ Remover Solicitação
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 30px; text-align: center; color: #666;">
                    <div class="alert alert-info">
                        <strong>💡 Dica:</strong> Use essas solicitações como referência para adicionar novos livros ao acervo. 
                        Após adicionar um livro solicitado, você pode remover a solicitação correspondente.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formulário oculto para exclusão -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="excluir" value="1">
        <input type="hidden" id="deleteId" name="id_solicitacao">
    </form>

    <script>
        function confirmDelete(id, titulo) {
            if (confirm(`Tem certeza que deseja remover a solicitação do livro "${titulo}"?\\n\\nEsta ação não pode ser desfeita.`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>

