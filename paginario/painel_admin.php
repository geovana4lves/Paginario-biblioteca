<?php
session_start();

// Verificar se é admin
if (!isset($_SESSION['admin_logado']) || !$_SESSION['admin_logado']) {
    header('Location: entrar-administrador.php');
    exit();
}

require_once 'db/conexao.php';

// Função para buscar estatísticas do banco
function buscarEstatisticas($conexao) {
    $stats = [];
    
    try {
        // Total de livros
        $stmt = $conexao->query("SELECT COUNT(*) as total FROM livro");
        $stats['livros'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de usuários
        $stmt = $conexao->query("SELECT COUNT(*) as total FROM usuario");
        $stats['usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de autores
        $stmt = $conexao->query("SELECT COUNT(*) as total FROM autor");
        $stats['autores'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de editoras
        $stmt = $conexao->query("SELECT COUNT(*) as total FROM editora");
        $stats['editoras'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de gêneros
        $stmt = $conexao->query("SELECT COUNT(*) as total FROM genero");
        $stats['generos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de solicitações
        $stmt = $conexao->query("SELECT COUNT(*) as total FROM solicitacao");
        $stats['solicitacoes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Últimos livros adicionados
        $stmt = $conexao->query("SELECT titulo, autor, DATE_FORMAT(NOW(), '%d/%m/%Y') as data_add FROM livro ORDER BY id_livro DESC LIMIT 5");
        $stats['ultimos_livros'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Últimas solicitações
        $stmt = $conexao->query("SELECT s.nome_livro, s.nome_autor, u.nome_completo as usuario FROM solicitacao s JOIN usuario u ON s.cpf = u.cpf ORDER BY s.id_solicitacao DESC LIMIT 5");
        $stats['ultimas_solicitacoes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Livros por gênero
        $stmt = $conexao->query("SELECT g.nome_genero, COUNT(l.id_livro) as total FROM genero g LEFT JOIN livro l ON g.id_genero = l.genero_id GROUP BY g.id_genero, g.nome_genero ORDER BY total DESC LIMIT 5");
        $stats['livros_por_genero'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        // Em caso de erro, retorna valores padrão
        $stats = [
            'livros' => 0,
            'usuarios' => 0,
            'autores' => 0,
            'editoras' => 0,
            'generos' => 0,
            'solicitacoes' => 0,
            'ultimos_livros' => [],
            'ultimas_solicitacoes' => [],
            'livros_por_genero' => []
        ];
    }
    
    return $stats;
}

$estatisticas = buscarEstatisticas($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Paginário</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .admin-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #86541c, #E9A863);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .admin-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 10px;
            margin-top: 15px;
            display: inline-block;
        }

        .content {
            padding: 40px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border-color: #86541c;
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #86541c, #E9A863);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
            color: white;
        }

        .card h3 {
            color: #2c3e50;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .card p {
            color: #7f8c8d;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: center;
            flex: 1;
            min-width: 100px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
            transform: translateY(-2px);
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #e67e22;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .recent-activity {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .activity-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            border-left: 4px solid #86541c;
        }

        .activity-card h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .activity-list {
            list-style: none;
        }

        .activity-list li {
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-list li:last-child {
            border-bottom: none;
        }

        .activity-info {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .activity-subtitle {
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-top: 2px;
        }

        .activity-count {
            background: #86541c;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .stats {
            background: #ecf0f1;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .stats h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .stat-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid #86541c;
        }

        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #86541c;
            display: block;
            animation: countUp 0.8s ease-out;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        @keyframes countUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .footer {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .footer a {
            color: #E9A863;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .grid {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                flex: none;
            }
            
            .recent-activity {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="header">
            <h1>🛠️ Painel Administrativo</h1>
            <p>Gerencie todas as tabelas e dados do sistema Paginário</p>
            <div class="admin-info">
                👤 Bem-vindo, <?php echo htmlspecialchars($_SESSION['admin_nome'] ?? 'Administrador'); ?>
            </div>
        </div>

        <div class="content">
            <div class="stats">
                <h3>📊 Estatísticas do Sistema (Dados Reais)</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $estatisticas['livros']; ?></span>
                        <div class="stat-label">Total de Livros</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $estatisticas['usuarios']; ?></span>
                        <div class="stat-label">Usuários Cadastrados</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $estatisticas['autores']; ?></span>
                        <div class="stat-label">Autores Registrados</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $estatisticas['editoras']; ?></span>
                        <div class="stat-label">Editoras Parceiras</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $estatisticas['generos']; ?></span>
                        <div class="stat-label">Gêneros Disponíveis</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $estatisticas['solicitacoes']; ?></span>
                        <div class="stat-label">Solicitações Pendentes</div>
                    </div>
                </div>
            </div>

            <div class="grid">
                <!-- Gerenciamento de Usuários -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">👥</div>
                        <h3>Usuários (<?php echo $estatisticas['usuarios']; ?>)</h3>
                    </div>
                    <p>Gerencie todos os usuários do sistema, incluindo informações pessoais e endereços.</p>
                    <div class="btn-group">
                        <a href="admin/usuarios/listar.php" class="btn btn-primary">📋 Listar</a>
                        <a href="admin/usuarios/adicionar.php" class="btn btn-success">➕ Adicionar</a>
                    </div>
                </div>

                <!-- Gerenciamento de Livros -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">📚</div>
                        <h3>Livros (<?php echo $estatisticas['livros']; ?>)</h3>
                    </div>
                    <p>Controle completo do catálogo de livros, incluindo edição, remoção e adição de novos títulos.</p>
                    <div class="btn-group">
                        <a href="admin/livros/listar.php" class="btn btn-primary">📋 Listar</a>
                        <a href="adicionar_livro.php" class="btn btn-success">➕ Adicionar</a>
                    </div>
                </div>

                <!-- Gerenciamento de Autores -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">✍️</div>
                        <h3>Autores (<?php echo $estatisticas['autores']; ?>)</h3>
                    </div>
                    <p>Cadastre e gerencie informações dos autores, incluindo biografia e nacionalidade.</p>
                    <div class="btn-group">
                        <a href="admin/autores/listar.php" class="btn btn-primary">📋 Listar</a>
                        <a href="admin/autores/adicionar.php" class="btn btn-success">➕ Adicionar</a>
                    </div>
                </div>

                <!-- Gerenciamento de Editoras -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">🏢</div>
                        <h3>Editoras (<?php echo $estatisticas['editoras']; ?>)</h3>
                    </div>
                    <p>Administre editoras parceiras, incluindo dados de contato e endereços.</p>
                    <div class="btn-group">
                        <a href="admin/editoras/listar.php" class="btn btn-primary">📋 Listar</a>
                        <a href="admin/editoras/adicionar.php" class="btn btn-success">➕ Adicionar</a>
                    </div>
                </div>

                <!-- Gerenciamento de Gêneros -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">🎭</div>
                        <h3>Gêneros (<?php echo $estatisticas['generos']; ?>)</h3>
                    </div>
                    <p>Organize e gerencie os gêneros literários disponíveis no sistema.</p>
                    <div class="btn-group">
                        <a href="admin/generos/listar.php" class="btn btn-primary">📋 Listar</a>
                        <a href="admin/generos/adicionar.php" class="btn btn-success">➕ Adicionar</a>
                    </div>
                </div>

                <!-- Gerenciamento de Solicitações -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">📝</div>
                        <h3>Solicitações (<?php echo $estatisticas['solicitacoes']; ?>)</h3>
                    </div>
                    <p>Visualize e gerencie solicitações de livros feitas pelos usuários.</p>
                    <div class="btn-group">
                        <a href="admin/solicitacoes/listar.php" class="btn btn-primary">📋 Listar</a>
                        <a href="admin/solicitacoes/gerenciar.php" class="btn btn-warning">⚙️ Gerenciar</a>
                    </div>
                </div>
            </div>

            <!-- Atividade Recente com Dados Reais -->
            <div class="recent-activity">
                <?php if (!empty($estatisticas['ultimos_livros'])): ?>
                <div class="activity-card">
                    <h4>📚 Últimos Livros Adicionados</h4>
                    <ul class="activity-list">
                        <?php foreach ($estatisticas['ultimos_livros'] as $livro): ?>
                        <li>
                            <div class="activity-info">
                                <div class="activity-title"><?php echo htmlspecialchars($livro['titulo']); ?></div>
                                <div class="activity-subtitle">por <?php echo htmlspecialchars($livro['autor']); ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($estatisticas['ultimas_solicitacoes'])): ?>
                <div class="activity-card">
                    <h4>📝 Últimas Solicitações</h4>
                    <ul class="activity-list">
                        <?php foreach ($estatisticas['ultimas_solicitacoes'] as $solicitacao): ?>
                        <li>
                            <div class="activity-info">
                                <div class="activity-title"><?php echo htmlspecialchars($solicitacao['nome_livro']); ?></div>
                                <div class="activity-subtitle">Solicitado por <?php echo htmlspecialchars($solicitacao['usuario']); ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($estatisticas['livros_por_genero'])): ?>
                <div class="activity-card">
                    <h4>🎭 Livros por Gênero</h4>
                    <ul class="activity-list">
                        <?php foreach ($estatisticas['livros_por_genero'] as $genero): ?>
                        <li>
                            <div class="activity-info">
                                <div class="activity-title"><?php echo htmlspecialchars($genero['nome_genero']); ?></div>
                                <div class="activity-subtitle">Gênero literário</div>
                            </div>
                            <div class="activity-count"><?php echo $genero['total']; ?></div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="logout.php" class="btn btn-danger" style="padding: 15px 30px; font-size: 1.1rem; margin-right: 15px;">
                    � Sair do Sistema
                </a>
                <a href="index.php" class="btn" style="padding: 15px 30px; font-size: 1.1rem; background: #6c757d; color: white;">
                    🏠 Página Inicial
                </a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; 2025 Paginário - Sistema de Biblioteca Digital | 
               <a href="politicaprivacidade.html">Política de Privacidade</a>
            </p>
        </div>
    </div>

    <script>
        // Efeito de carregamento das estatísticas
        document.addEventListener('DOMContentLoaded', function() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            statNumbers.forEach(stat => {
                const finalValue = parseInt(stat.textContent);
                stat.textContent = '0';
                
                const increment = finalValue / 30;
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= finalValue) {
                        stat.textContent = finalValue;
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(current);
                    }
                }, 50);
            });
        });
    </script>
</body>
</html>