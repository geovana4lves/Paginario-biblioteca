<?php
session_start();
require_once '../../db/conexao.php';

// Verificar se é admin
if (!isset($_SESSION['user_nome']) || $_SESSION['user_tipo'] !== 'admin') {
    header('Location: ../../entrar-administrador.php');
    exit();
}

// Buscar livros
$busca = $_GET['busca'] ?? '';
$genero_filtro = $_GET['genero'] ?? '';
$sql = "SELECT l.*, g.nome_genero, a.nome_completo as admin_nome 
        FROM livro l 
        LEFT JOIN genero g ON l.genero_id = g.id_genero
        LEFT JOIN administrador a ON l.cpf_administrador = a.cpf_administrador
        WHERE 1=1";

$params = [];

if ($busca) {
    $sql .= " AND (l.titulo LIKE ? OR l.autor LIKE ? OR l.sinopse LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

if ($genero_filtro) {
    $sql .= " AND l.genero_id = ?";
    $params[] = $genero_filtro;
}

$sql .= " ORDER BY l.titulo";

try {
    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    $livros = $stmt->fetchAll();
    
    // Buscar gêneros para filtro
    $stmt_generos = $conexao->query("SELECT * FROM genero ORDER BY nome_genero");
    $generos = $stmt_generos->fetchAll();
    
} catch (PDOException $e) {
    $erro = "Erro ao buscar livros: " . $e->getMessage();
}

// Excluir livro
if ($_POST && isset($_POST['excluir'])) {
    $id_livro = $_POST['id_livro'];
    
    try {
        $conexao->beginTransaction();
        
        // Buscar dados do livro para remover arquivos
        $stmt = $conexao->prepare("SELECT capa, link_arquivo FROM livro WHERE id_livro = ?");
        $stmt->execute([$id_livro]);
        $livro_dados = $stmt->fetch();
        
        // Remover relacionamentos
        $stmt = $conexao->prepare("DELETE FROM le WHERE id_livro = ?");
        $stmt->execute([$id_livro]);
        
        $stmt = $conexao->prepare("DELETE FROM publica WHERE id_livro = ?");
        $stmt->execute([$id_livro]);
        
        $stmt = $conexao->prepare("DELETE FROM envio_livro WHERE livro_id = ?");
        $stmt->execute([$id_livro]);
        
        // Remover livro
        $stmt = $conexao->prepare("DELETE FROM livro WHERE id_livro = ?");
        $stmt->execute([$id_livro]);
        
        // Tentar remover arquivos do servidor
        if ($livro_dados) {
            if ($livro_dados['capa'] && file_exists("../../uploads/" . $livro_dados['capa'])) {
                unlink("../../uploads/" . $livro_dados['capa']);
            }
            if ($livro_dados['link_arquivo'] && file_exists("../../arquivos-livros/" . $livro_dados['link_arquivo'])) {
                unlink("../../arquivos-livros/" . $livro_dados['link_arquivo']);
            }
        }
        
        $conexao->commit();
        
        header('Location: listar.php?exclusao=sucesso');
        exit();
        
    } catch (Exception $e) {
        $conexao->rollback();
        $erro = "Erro ao excluir livro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Livros - Admin</title>
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
            max-width: 1400px;
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

        .filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 2px solid #ddd;
        }

        .filter-row {
            display: grid;
            grid-template-columns: 2fr 1fr auto auto;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        input[type="text"],
        select {
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus,
        select:focus {
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

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .book-card {
            background: white;
            border: 2px solid #eee;
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border-color: #86541c;
        }

        .book-header {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .book-cover {
            width: 80px;
            height: 120px;
            background: linear-gradient(135deg, #86541c, #E9A863);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .book-info {
            flex: 1;
        }

        .book-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .book-author {
            color: #7f8c8d;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .book-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 0.85rem;
        }

        .meta-item {
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 12px;
            color: #666;
        }

        .genre-tag {
            background: #e8f4fd;
            color: #2980b9;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .book-synopsis {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 0.9rem;
            line-height: 1.4;
            color: #555;
            max-height: 60px;
            overflow: hidden;
            position: relative;
        }

        .book-synopsis.expandable::after {
            content: '...';
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: #f8f9fa;
            padding-left: 20px;
        }

        .book-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
            font-size: 0.9rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .detail-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .detail-value {
            color: #666;
        }

        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 15px;
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

        .classification {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #e74c3c;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .classification.livre {
            background: #27ae60;
        }

        .classification.dez {
            background: #f39c12;
        }

        .classification.doze {
            background: #e67e22;
        }

        .classification.catorze {
            background: #e74c3c;
        }

        .classification.dezesseis {
            background: #8e44ad;
        }

        .classification.dezoito {
            background: #2c3e50;
        }

        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }

            .books-grid {
                grid-template-columns: 1fr;
            }

            .book-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .book-details {
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
            <h1>📚 Gerenciar Livros</h1>
            <p>Visualize, edite e gerencie todo o acervo de livros</p>
        </div>

        <div class="content">
            <?php if (isset($_GET['exclusao']) && $_GET['exclusao'] === 'sucesso'): ?>
                <div class="alert alert-success">
                    <strong>✅ Sucesso!</strong> Livro excluído com sucesso.
                </div>
            <?php endif; ?>

            <?php if (isset($erro)): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erro:</strong> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <!-- Filtros -->
            <div class="filters">
                <form method="GET">
                    <div class="filter-row">
                        <div class="form-group">
                            <label for="busca">Buscar Livros</label>
                            <input type="text" id="busca" name="busca" 
                                   placeholder="Título, autor ou sinopse..." 
                                   value="<?= htmlspecialchars($busca) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="genero">Filtrar por Gênero</label>
                            <select id="genero" name="genero">
                                <option value="">Todos os gêneros</option>
                                <?php foreach ($generos as $genero): ?>
                                    <option value="<?= $genero['id_genero'] ?>" 
                                            <?= $genero_filtro == $genero['id_genero'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($genero['nome_genero']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                        <a href="../../adicionar_livro.php" class="btn btn-success">➕ Novo Livro</a>
                    </div>
                </form>
                
                <div style="margin-top: 15px; text-align: center;">
                    <a href="../../painel_admin.php" class="btn btn-back">🔙 Voltar ao Painel</a>
                </div>
            </div>

            <?php if (empty($livros)): ?>
                <div class="no-data">
                    <div class="no-data-icon">📚</div>
                    <h3>Nenhum livro encontrado</h3>
                    <?php if ($busca || $genero_filtro): ?>
                        <p>Tente alterar os filtros ou <a href="listar.php">visualize todos os livros</a>.</p>
                    <?php else: ?>
                        <p><a href="../../adicionar_livro.php">Clique aqui para adicionar o primeiro livro</a>.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="books-grid">
                    <?php foreach ($livros as $livro): ?>
                        <div class="book-card">
                            <div class="classification <?= 
                                $livro['classificacao_indicativa'] == 0 ? 'livre' : 
                                ($livro['classificacao_indicativa'] <= 10 ? 'dez' :
                                ($livro['classificacao_indicativa'] <= 12 ? 'doze' :
                                ($livro['classificacao_indicativa'] <= 14 ? 'catorze' :
                                ($livro['classificacao_indicativa'] <= 16 ? 'dezesseis' : 'dezoito'))))
                            ?>">
                                <?= $livro['classificacao_indicativa'] == 0 ? 'LIVRE' : $livro['classificacao_indicativa'] . '+' ?>
                            </div>

                            <div class="book-header">
                                <div class="book-cover">
                                    <?php if ($livro['capa'] && file_exists("../../uploads/" . $livro['capa'])): ?>
                                        <img src="../../uploads/<?= htmlspecialchars($livro['capa']) ?>" 
                                             alt="Capa do livro">
                                    <?php else: ?>
                                        📖
                                    <?php endif; ?>
                                </div>
                                
                                <div class="book-info">
                                    <div class="book-title"><?= htmlspecialchars($livro['titulo']) ?></div>
                                    <div class="book-author">✍️ <?= htmlspecialchars($livro['autor']) ?></div>
                                    
                                    <div class="book-meta">
                                        <?php if ($livro['ano_publicacao']): ?>
                                            <span class="meta-item">📅 <?= $livro['ano_publicacao'] ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if ($livro['formato']): ?>
                                            <span class="meta-item">📄 <?= strtoupper($livro['formato']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($livro['nome_genero']): ?>
                                        <div style="margin-top: 8px;">
                                            <span class="genre-tag"><?= htmlspecialchars($livro['nome_genero']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($livro['sinopse']): ?>
                                <div class="book-synopsis <?= strlen($livro['sinopse']) > 100 ? 'expandable' : '' ?>">
                                    <?= htmlspecialchars(strlen($livro['sinopse']) > 100 ? 
                                        substr($livro['sinopse'], 0, 100) : $livro['sinopse']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="book-details">
                                <?php if ($livro['editor']): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Editor:</span>
                                        <span class="detail-value"><?= htmlspecialchars($livro['editor']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="detail-item">
                                    <span class="detail-label">ID:</span>
                                    <span class="detail-value">#<?= $livro['id_livro'] ?></span>
                                </div>
                                
                                <?php if ($livro['admin_nome']): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Cadastrado por:</span>
                                        <span class="detail-value"><?= htmlspecialchars($livro['admin_nome']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($livro['link_arquivo']): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Arquivo:</span>
                                        <span class="detail-value">
                                            <a href="../../arquivos-livros/<?= htmlspecialchars($livro['link_arquivo']) ?>" 
                                               target="_blank" style="color: #3498db;">
                                                📥 Download
                                            </a>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="actions">
                                <a href="editar.php?id=<?= $livro['id_livro'] ?>" 
                                   class="btn btn-warning" title="Editar livro">
                                    ✏️ Editar
                                </a>
                                
                                <button onclick="confirmDelete(<?= $livro['id_livro'] ?>, '<?= htmlspecialchars($livro['titulo'], ENT_QUOTES) ?>')" 
                                        class="btn btn-danger" title="Excluir livro">
                                    🗑️ Excluir
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 30px; text-align: center; color: #666;">
                    <strong><?= count($livros) ?></strong> livro(s) encontrado(s)
                    <?php if ($busca): ?>
                        para "<strong><?= htmlspecialchars($busca) ?></strong>"
                    <?php endif; ?>
                    <?php if ($genero_filtro): ?>
                        <?php 
                        $genero_nome = '';
                        foreach ($generos as $g) {
                            if ($g['id_genero'] == $genero_filtro) {
                                $genero_nome = $g['nome_genero'];
                                break;
                            }
                        }
                        ?>
                        no gênero "<strong><?= htmlspecialchars($genero_nome) ?></strong>"
                    <?php endif; ?>
                    <?php if ($busca || $genero_filtro): ?>
                        | <a href="listar.php">Ver todos</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formulário oculto para exclusão -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="excluir" value="1">
        <input type="hidden" id="deleteId" name="id_livro">
    </form>

    <script>
        // Expandir sinopses longas ao clicar
        document.querySelectorAll('.book-synopsis.expandable').forEach(synopsis => {
            synopsis.style.cursor = 'pointer';
            synopsis.title = 'Clique para ver mais';
            
            synopsis.addEventListener('click', function() {
                if (this.style.maxHeight === 'none') {
                    this.style.maxHeight = '60px';
                    this.classList.add('expandable');
                } else {
                    this.style.maxHeight = 'none';
                    this.classList.remove('expandable');
                }
            });
        });

        // Confirmar exclusão
        function confirmDelete(id, titulo) {
            if (confirm(`Tem certeza que deseja excluir o livro "${titulo}"?\\n\\nEsta ação não pode ser desfeita e removerá:\\n- O livro do banco de dados\\n- Todos os registros relacionados\\n- Os arquivos do servidor`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>

