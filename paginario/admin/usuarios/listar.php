<?php
session_start();
require_once '../../db/conexao.php';

// Verificar se é admin
if (!isset($_SESSION['user_nome']) || $_SESSION['user_tipo'] !== 'admin') {
    header('Location: ../../entrar-administrador.php');
    exit();
}

// Buscar usuários
$busca = $_GET['busca'] ?? '';
$sql = "SELECT u.*, e.rua, e.numero, e.bairro, e.cidade 
        FROM usuario u 
        LEFT JOIN endereco_usuario e ON u.cpf = e.cpf_usuario";

if ($busca) {
    $sql .= " WHERE u.nome_completo LIKE :busca OR u.email LIKE :busca OR u.login LIKE :busca";
}

$sql .= " ORDER BY u.nome_completo";

try {
    $stmt = $conexao->prepare($sql);
    if ($busca) {
        $stmt->bindValue(':busca', "%$busca%");
    }
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro = "Erro ao buscar usuários: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Admin</title>
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

        .table-container {
            overflow-x: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th {
            background: #f8f9fa;
            color: #2c3e50;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .user-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .user-email {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .user-login {
            color: #3498db;
            font-size: 0.9rem;
        }

        .address-info {
            font-size: 0.9rem;
            color: #555;
            line-height: 1.4;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
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

        .cpf-mask {
            font-family: monospace;
            background: #f8f9fa;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: none;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 10px 8px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👥 Gerenciar Usuários</h1>
            <p>Visualize, edite e gerencie todos os usuários do sistema</p>
        </div>

        <div class="content">
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger">
                    <strong>Erro:</strong> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <div class="toolbar">
                <form method="GET" class="search-box">
                    <input type="text" name="busca" placeholder="Buscar por nome, email ou login..." 
                           value="<?= htmlspecialchars($busca) ?>">
                    <button type="submit" class="btn btn-primary">🔍 Buscar</button>
                </form>
                
                <div style="display: flex; gap: 10px;">
                    <a href="adicionar.php" class="btn btn-success">➕ Novo Usuário</a>
                    <a href="../../painel_admin.php" class="btn btn-back">🔙 Voltar</a>
                </div>
            </div>

            <?php if (empty($usuarios)): ?>
                <div class="alert alert-info">
                    <strong>Nenhum usuário encontrado.</strong>
                    <?php if ($busca): ?>
                        <br>Tente uma busca diferente ou <a href="listar.php">visualize todos os usuários</a>.
                    <?php else: ?>
                        <br><a href="adicionar.php">Clique aqui para adicionar o primeiro usuário</a>.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>CPF</th>
                                <th>Informações do Usuário</th>
                                <th>Contato</th>
                                <th>Endereço</th>
                                <th width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td>
                                        <span class="cpf-mask">
                                            <?= substr($usuario['cpf'], 0, 3) . '.' . 
                                                substr($usuario['cpf'], 3, 3) . '.' . 
                                                substr($usuario['cpf'], 6, 3) . '-' . 
                                                substr($usuario['cpf'], 9, 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-name"><?= htmlspecialchars($usuario['nome_completo']) ?></div>
                                            <div class="user-email">📧 <?= htmlspecialchars($usuario['email']) ?></div>
                                            <div class="user-login">👤 <?= htmlspecialchars($usuario['login']) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($usuario['telefone']): ?>
                                            📞 <?= htmlspecialchars($usuario['telefone']) ?>
                                        <?php else: ?>
                                            <span style="color: #999;">Não informado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($usuario['rua']): ?>
                                            <div class="address-info">
                                                📍 <?= htmlspecialchars($usuario['rua']) ?>, <?= htmlspecialchars($usuario['numero']) ?><br>
                                                <?= htmlspecialchars($usuario['bairro']) ?> - <?= htmlspecialchars($usuario['cidade']) ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #999;">Não informado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="editar.php?cpf=<?= urlencode($usuario['cpf']) ?>" 
                                               class="btn btn-warning" title="Editar usuário">
                                                ✏️ Editar
                                            </a>
                                            <a href="excluir.php?cpf=<?= urlencode($usuario['cpf']) ?>" 
                                               class="btn btn-danger" title="Excluir usuário"
                                               onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                🗑️ Excluir
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 20px; text-align: center; color: #666;">
                    <strong><?= count($usuarios) ?></strong> usuário(s) encontrado(s)
                    <?php if ($busca): ?>
                        para "<strong><?= htmlspecialchars($busca) ?></strong>"
                        | <a href="listar.php">Ver todos</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
