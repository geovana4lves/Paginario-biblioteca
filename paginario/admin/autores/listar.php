<?php
session_start();
require_once '../../db/conexao.php';

// Verificar se é admin
if (!isset($_SESSION['user_nome']) || $_SESSION['user_tipo'] !== 'admin') {
    header('Location: ../../entrar-administrador.php');
    exit();
}

// Buscar autores
$busca = $_GET['busca'] ?? '';
$sql = "SELECT a.*, COUNT(l.id_livro) as total_livros 
        FROM autor a 
        LEFT JOIN livro l ON a.nome_completo = l.autor";

if ($busca) {
    $sql .= " WHERE a.nome_completo LIKE :busca OR a.nacionalidade LIKE :busca";
}

$sql .= " GROUP BY a.id_autor ORDER BY a.nome_completo";

try {
    $stmt = $conexao->prepare($sql);
    if ($busca) {
        $stmt->bindValue(':busca', "%$busca%");
    }
    $stmt->execute();
    $autores = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro = "Erro ao buscar autores: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Autores - Admin</title>
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
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
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
            font-size: 0.9rem;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
            padding: 8px 12px;
            font-size: 0.9rem;
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

        .authors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .author-card {
            background: white;
            border: 2px solid #eee;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
        }

        .author-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border-color: #86541c;
        }

        .author-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .author-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #86541c, #E9A863);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-right: 15px;
            font-weight: bold;
        }

        .author-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .author-id {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .author-info {
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .info-icon {
            width: 20px;
            text-align: center;
        }

        .biography {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #555;
            max-height: 80px;
            overflow: hidden;
            position: relative;
        }

        .biography.expandable::after {
            content: '...';
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: #f8f9fa;
            padding-left: 20px;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #e8f4fd;
            border-radius: 8px;
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

        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: none;
            }

            .authors-grid {
                grid-template-columns: 1fr;
            }

            .actions {
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
            <h1>✍️ Gerenciar Autores</h1>
            <p>Visualize, edite e gerencie todos os autores cadastrados</p>
        </div>

        <div class="content">
            <?php if (isset($_GET['exclusao']) && $_GET['exclusao'] === 'sucesso'): ?>
                <div class="alert alert-success">
                    <strong>✅ Sucesso!</strong> Autor excluído com sucesso.
                </div>
            <?php endif; ?>

            <?php if (isset($erro)): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erro:</strong> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <div class="toolbar">
                <form method="GET" class="search-box">
                    <input type="text" name="busca" placeholder="Buscar por nome ou nacionalidade..." 
                           value="<?= htmlspecialchars($busca) ?>">
                    <button type="submit" class="btn btn-primary">🔍 Buscar</button>
                </form>
                
                <div style="display: flex; gap: 10px;">
                    <a href="adicionar.php" class="btn btn-success">➕ Novo Autor</a>
                    <a href="../../painel_admin.php" class="btn btn-back">🔙 Voltar</a>
                </div>
            </div>

            <?php if (empty($autores)): ?>
                <div class="no-data">
                    <div class="no-data-icon">✍️</div>
                    <h3>Nenhum autor encontrado</h3>
                    <?php if ($busca): ?>
                        <p>Tente uma busca diferente ou <a href="listar.php">visualize todos os autores</a>.</p>
                    <?php else: ?>
                        <p><a href="adicionar.php">Clique aqui para cadastrar o primeiro autor</a>.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="authors-grid">
                    <?php foreach ($autores as $autor): ?>
                        <div class="author-card">
                            <div class="author-header">
                                <div class="author-avatar">
                                    <?= strtoupper(substr($autor['nome_completo'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="author-name"><?= htmlspecialchars($autor['nome_completo']) ?></div>
                                    <div class="author-id">ID: #<?= $autor['id_autor'] ?></div>
                                </div>
                            </div>

                            <div class="author-info">
                                <?php if ($autor['nacionalidade']): ?>
                                <div class="info-item">
                                    <span class="info-icon">🌍</span>
                                    <span><?= htmlspecialchars($autor['nacionalidade']) ?></span>
                                </div>
                                <?php endif; ?>

                                <?php if ($autor['data_nascimento']): ?>
                                <div class="info-item">
                                    <span class="info-icon">📅</span>
                                    <span><?= date('d/m/Y', strtotime($autor['data_nascimento'])) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($autor['biografia']): ?>
                            <div class="biography <?= strlen($autor['biografia']) > 150 ? 'expandable' : '' ?>">
                                <?= htmlspecialchars(strlen($autor['biografia']) > 150 ? 
                                    substr($autor['biografia'], 0, 150) : $autor['biografia']) ?>
                            </div>
                            <?php endif; ?>

                            <div class="stats">
                                <span class="stat-label">📚 Livros no Sistema:</span>
                                <span class="stat-value"><?= $autor['total_livros'] ?></span>
                            </div>

                            <div class="actions">
                                <a href="editar.php?id=<?= $autor['id_autor'] ?>" 
                                   class="btn btn-warning" title="Editar autor">
                                    ✏️ Editar
                                </a>
                                <a href="excluir.php?id=<?= $autor['id_autor'] ?>" 
                                   class="btn btn-danger" title="Excluir autor"
                                   onclick="return confirm('Tem certeza que deseja excluir este autor?')">
                                    🗑️ Excluir
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 30px; text-align: center; color: #666;">
                    <strong><?= count($autores) ?></strong> autor(es) encontrado(s)
                    <?php if ($busca): ?>
                        para "<strong><?= htmlspecialchars($busca) ?></strong>"
                        | <a href="listar.php">Ver todos</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Expandir biografias longas ao clicar
        document.querySelectorAll('.biography.expandable').forEach(bio => {
            bio.style.cursor = 'pointer';
            bio.title = 'Clique para ver mais';
            
            bio.addEventListener('click', function() {
                if (this.style.maxHeight === 'none') {
                    this.style.maxHeight = '80px';
                    this.classList.add('expandable');
                } else {
                    this.style.maxHeight = 'none';
                    this.classList.remove('expandable');
                }
            });
        });
    </script>
</body>
</html>

