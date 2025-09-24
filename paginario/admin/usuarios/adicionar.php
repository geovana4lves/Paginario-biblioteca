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

if ($_POST) {
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $nome_completo = trim($_POST['nome_completo']);
    $email = trim($_POST['email']);
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
    $login = trim($_POST['login']);
    $senha = $_POST['senha'];
    
    // Dados de endereço (opcionais)
    $rua = trim($_POST['rua']);
    $numero = $_POST['numero'];
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);

    // Validações
    if (strlen($cpf) !== 11) {
        $erro = "CPF deve ter exatamente 11 dígitos.";
    } elseif (empty($nome_completo) || empty($email) || empty($login) || empty($senha)) {
        $erro = "Todos os campos obrigatórios devem ser preenchidos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    } elseif (strlen($senha) < 6) {
        $erro = "Senha deve ter pelo menos 6 caracteres.";
    } else {
        try {
            $conexao->beginTransaction();
            
            // Verificar se CPF, email ou login já existem
            $stmt = $conexao->prepare("SELECT cpf, email, login FROM usuario WHERE cpf = ? OR email = ? OR login = ?");
            $stmt->execute([$cpf, $email, $login]);
            $existente = $stmt->fetch();
            
            if ($existente) {
                if ($existente['cpf'] === $cpf) {
                    throw new Exception("CPF já cadastrado.");
                } elseif ($existente['email'] === $email) {
                    throw new Exception("Email já cadastrado.");
                } else {
                    throw new Exception("Login já cadastrado.");
                }
            }
            
            // Hash da senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Inserir usuário
            $stmt = $conexao->prepare("INSERT INTO usuario (cpf, nome_completo, email, telefone, login, senha) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cpf, $nome_completo, $email, $telefone ?: null, $login, $senha_hash]);
            
            // Inserir endereço se fornecido
            if (!empty($rua) && !empty($numero) && !empty($bairro) && !empty($cidade)) {
                $stmt = $conexao->prepare("INSERT INTO endereco_usuario (cpf_usuario, rua, numero, bairro, cidade) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$cpf, $rua, $numero, $bairro, $cidade]);
            }
            
            $conexao->commit();
            $sucesso = "Usuário cadastrado com sucesso!";
            
            // Limpar formulário
            $_POST = [];
            
        } catch (Exception $e) {
            $conexao->rollback();
            $erro = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Usuário - Admin</title>
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
            max-width: 800px;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row-full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .required {
            color: #e74c3c;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"] {
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

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
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

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }

        .help-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        .optional-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>➕ Adicionar Usuário</h1>
            <p>Cadastre um novo usuário no sistema</p>
        </div>

        <div class="content">
            <?php if ($sucesso): ?>
                <div class="alert alert-success">
                    <strong>✅ Sucesso!</strong> <?= htmlspecialchars($sucesso) ?>
                </div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erro:</strong> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="section-title">
                    👤 Dados Pessoais
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cpf">CPF <span class="required">*</span></label>
                        <input type="text" id="cpf" name="cpf" 
                               value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>"
                               placeholder="000.000.000-00" maxlength="14" required>
                        <div class="help-text">Apenas números, será formatado automaticamente</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone" 
                               value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>"
                               placeholder="(11) 99999-9999" maxlength="15">
                    </div>
                </div>

                <div class="form-group">
                    <label for="nome_completo">Nome Completo <span class="required">*</span></label>
                    <input type="text" id="nome_completo" name="nome_completo" 
                           value="<?= htmlspecialchars($_POST['nome_completo'] ?? '') ?>"
                           placeholder="Digite o nome completo" required>
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="usuario@email.com" required>
                </div>

                <div class="section-title">
                    🔐 Dados de Acesso
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="login">Login <span class="required">*</span></label>
                        <input type="text" id="login" name="login" 
                               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                               placeholder="nome_usuario" required>
                        <div class="help-text">Será usado para fazer login no sistema</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="senha">Senha <span class="required">*</span></label>
                        <input type="password" id="senha" name="senha" 
                               placeholder="Mínimo 6 caracteres" required>
                        <div class="help-text">Mínimo 6 caracteres</div>
                    </div>
                </div>

                <div class="optional-section">
                    <div class="section-title">
                        📍 Endereço (Opcional)
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="rua">Rua</label>
                            <input type="text" id="rua" name="rua" 
                                   value="<?= htmlspecialchars($_POST['rua'] ?? '') ?>"
                                   placeholder="Nome da rua">
                        </div>
                        
                        <div class="form-group">
                            <label for="numero">Número</label>
                            <input type="number" id="numero" name="numero" 
                                   value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>"
                                   placeholder="123">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="bairro">Bairro</label>
                            <input type="text" id="bairro" name="bairro" 
                                   value="<?= htmlspecialchars($_POST['bairro'] ?? '') ?>"
                                   placeholder="Nome do bairro">
                        </div>
                        
                        <div class="form-group">
                            <label for="cidade">Cidade</label>
                            <input type="text" id="cidade" name="cidade" 
                                   value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>"
                                   placeholder="Nome da cidade">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">💾 Salvar Usuário</button>
                    <a href="listar.php" class="btn btn-back">🔙 Voltar à Lista</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Máscara para CPF
        document.getElementById('cpf').addEventListener('input', function() {
            let value = this.value.replace(/\\D/g, '');
            value = value.replace(/(\\d{3})(\\d)/, '$1.$2');
            value = value.replace(/(\\d{3})(\\d)/, '$1.$2');
            value = value.replace(/(\\d{3})(\\d{1,2})$/, '$1-$2');
            this.value = value;
        });

        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function() {
            let value = this.value.replace(/\\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\\d{2})(\\d)/, '($1) $2');
                value = value.replace(/(\\d{5})(\\d)/, '$1-$2');
            }
            this.value = value;
        });
    </script>
</body>
</html>

