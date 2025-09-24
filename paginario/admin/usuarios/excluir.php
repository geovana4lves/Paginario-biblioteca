<?php
session_start();
require_once '../../db/conexao.php';

// Verificar se é admin
if (!isset($_SESSION['user_nome']) || $_SESSION['user_tipo'] !== 'admin') {
    header('Location: ../../entrar-administrador.php');
    exit();
}

$cpf_usuario = $_GET['cpf'] ?? '';
if (empty($cpf_usuario)) {
    header('Location: listar.php');
    exit();
}

$sucesso = '';
$erro = '';

// Buscar dados do usuário
try {
    $stmt = $conexao->prepare("SELECT u.*, e.rua, e.numero, e.bairro, e.cidade 
                           FROM usuario u 
                           LEFT JOIN endereco_usuario e ON u.cpf = e.cpf_usuario 
                           WHERE u.cpf = ?");
    $stmt->execute([$cpf_usuario]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        header('Location: listar.php');
        exit();
    }
} catch (PDOException $e) {
    $erro = "Erro ao buscar usuário: " . $e->getMessage();
}

// Verificar dependências
$dependencias = [];
try {
    // Verificar solicitações
    $stmt = $conexao->prepare("SELECT COUNT(*) FROM solicitacao WHERE cpf = ?");
    $stmt->execute([$cpf_usuario]);
    $solicitacoes = $stmt->fetchColumn();
    if ($solicitacoes > 0) {
        $dependencias[] = "$solicitacoes solicitação(ões) de livro";
    }
    
    // Verificar leituras
    $stmt = $conexao->prepare("SELECT COUNT(*) FROM le WHERE cpf_usuario = ?");
    $stmt->execute([$cpf_usuario]);
    $leituras = $stmt->fetchColumn();
    if ($leituras > 0) {
        $dependencias[] = "$leituras registro(s) de leitura";
    }
} catch (PDOException $e) {
    // Continua mesmo com erro nas dependências
}

if ($_POST && isset($_POST['confirmar'])) {
    try {
        $conexao->beginTransaction();
        
        // Remover endereço
        $stmt = $conexao->prepare("DELETE FROM endereco_usuario WHERE cpf_usuario = ?");
        $stmt->execute([$cpf_usuario]);
        
        // Remover leituras
        $stmt = $conexao->prepare("DELETE FROM le WHERE cpf_usuario = ?");
        $stmt->execute([$cpf_usuario]);
        
        // Remover solicitações
        $stmt = $conexao->prepare("DELETE FROM solicitacao WHERE cpf = ?");
        $stmt->execute([$cpf_usuario]);
        
        // Remover usuário
        $stmt = $conexao->prepare("DELETE FROM usuario WHERE cpf = ?");
        $stmt->execute([$cpf_usuario]);
        
        $conexao->commit();
        
        // Redirecionar com mensagem de sucesso
        header('Location: listar.php?exclusao=sucesso');
        exit();
        
    } catch (Exception $e) {
        $conexao->rollback();
        $erro = "Erro ao excluir usuário: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Usuário - Admin</title>
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
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .warning-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .content {
            padding: 30px;
        }

        .user-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #e74c3c;
        }

        .user-info {
            display: grid;
            gap: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            font-weight: 600;
            color: #2c3e50;
        }

        .value {
            color: #555;
            text-align: right;
            flex: 1;
            margin-left: 15px;
        }

        .cpf-display {
            font-family: monospace;
            background: #e3f2fd;
            padding: 4px 8px;
            border-radius: 4px;
            color: #1976d2;
            font-weight: bold;
        }

        .dependencies {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .dependencies h3 {
            color: #856404;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dependencies ul {
            list-style: none;
            padding: 0;
        }

        .dependencies li {
            background: white;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 5px;
            border-left: 3px solid #ffc107;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .warning-box {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .warning-box h3 {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .btn-danger {
            background: #e74c3c;
            color: white;
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

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }

        .confirmation-form {
            text-align: center;
        }

        .checkbox-container {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #ddd;
        }

        .checkbox-container input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
        }

        .checkbox-container label {
            font-weight: 600;
            color: #721c24;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .value {
                text-align: left;
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="warning-icon">⚠️</div>
            <h1>Excluir Usuário</h1>
            <p>Esta ação não pode ser desfeita!</p>
        </div>

        <div class="content">
            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erro:</strong> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <div class="user-card">
                <h3 style="margin-bottom: 15px; color: #2c3e50;">📋 Dados do Usuário</h3>
                <div class="user-info">
                    <div class="info-row">
                        <span class="label">CPF:</span>
                        <span class="value">
                            <span class="cpf-display">
                                <?= substr($usuario['cpf'], 0, 3) . '.' . 
                                    substr($usuario['cpf'], 3, 3) . '.' . 
                                    substr($usuario['cpf'], 6, 3) . '-' . 
                                    substr($usuario['cpf'], 9, 2) ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Nome:</span>
                        <span class="value"><?= htmlspecialchars($usuario['nome_completo']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email:</span>
                        <span class="value"><?= htmlspecialchars($usuario['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Login:</span>
                        <span class="value"><?= htmlspecialchars($usuario['login']) ?></span>
                    </div>
                    <?php if ($usuario['telefone']): ?>
                    <div class="info-row">
                        <span class="label">Telefone:</span>
                        <span class="value"><?= htmlspecialchars($usuario['telefone']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($usuario['rua']): ?>
                    <div class="info-row">
                        <span class="label">Endereço:</span>
                        <span class="value">
                            <?= htmlspecialchars($usuario['rua']) ?>, <?= htmlspecialchars($usuario['numero']) ?><br>
                            <?= htmlspecialchars($usuario['bairro']) ?> - <?= htmlspecialchars($usuario['cidade']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($dependencias)): ?>
            <div class="dependencies">
                <h3>🔗 Dados Relacionados</h3>
                <p style="margin-bottom: 15px; color: #856404;">
                    Os seguintes dados também serão removidos permanentemente:
                </p>
                <ul>
                    <?php foreach ($dependencias as $dep): ?>
                        <li>🗑️ <?= htmlspecialchars($dep) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="warning-box">
                <h3>⚠️ Atenção!</h3>
                <p><strong>Esta ação é irreversível!</strong></p>
                <p>Todos os dados do usuário e registros relacionados serão excluídos permanentemente do sistema.</p>
            </div>

            <form method="POST" class="confirmation-form">
                <div class="checkbox-container">
                    <input type="checkbox" id="confirmar_exclusao" required>
                    <label for="confirmar_exclusao">
                        Confirmo que desejo excluir este usuário e todos os seus dados relacionados
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" name="confirmar" value="1" class="btn btn-danger"
                            onclick="return confirm('TEM CERTEZA ABSOLUTA que deseja excluir este usuário? Esta ação NÃO PODE ser desfeita!')">
                        🗑️ CONFIRMAR EXCLUSÃO
                    </button>
                    <a href="listar.php" class="btn btn-back">🔙 Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Só habilitar o botão se checkbox estiver marcado
        const checkbox = document.getElementById('confirmar_exclusao');
        const button = document.querySelector('button[name="confirmar"]');
        
        function toggleButton() {
            button.disabled = !checkbox.checked;
            if (checkbox.checked) {
                button.style.opacity = '1';
                button.style.cursor = 'pointer';
            } else {
                button.style.opacity = '0.5';
                button.style.cursor = 'not-allowed';
            }
        }
        
        checkbox.addEventListener('change', toggleButton);
        toggleButton(); // Executar na inicialização
    </script>
</body>
</html>

