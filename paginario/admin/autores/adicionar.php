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
    $nome_completo = trim($_POST['nome_completo']);
    $nacionalidade = trim($_POST['nacionalidade']);
    $data_nascimento = $_POST['data_nascimento'];
    $biografia = trim($_POST['biografia']);

    // Validações
    if (empty($nome_completo)) {
        $erro = "O nome completo é obrigatório.";
    } elseif (!empty($data_nascimento) && strtotime($data_nascimento) > time()) {
        $erro = "A data de nascimento não pode ser no futuro.";
    } else {
        try {
            // Verificar se autor já existe
            $stmt = $conexao->prepare("SELECT id_autor FROM autor WHERE nome_completo = ?");
            $stmt->execute([$nome_completo]);
            
            if ($stmt->fetch()) {
                throw new Exception("Já existe um autor cadastrado com este nome.");
            }
            
            // Inserir autor
            $stmt = $conexao->prepare("INSERT INTO autor (nome_completo, nacionalidade, data_nascimento, biografia) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $nome_completo,
                $nacionalidade ?: null,
                $data_nascimento ?: null,
                $biografia ?: null
            ]);
            
            $sucesso = "Autor cadastrado com sucesso!";
            
            // Limpar formulário
            $_POST = [];
            
        } catch (Exception $e) {
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
    <title>Adicionar Autor - Admin</title>
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
            max-width: 700px;
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
            margin-bottom: 25px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
        input[type="date"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #86541c;
            box-shadow: 0 0 0 3px rgba(134, 84, 28, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.5;
        }

        .char-count {
            text-align: right;
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
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

        .preview-card {
            background: white;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .preview-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .preview-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #86541c, #E9A863);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            margin-right: 15px;
            font-weight: bold;
        }

        .preview-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .preview-info {
            font-size: 0.9rem;
            color: #666;
            margin-top: 10px;
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

            .preview-header {
                flex-direction: column;
                text-align: center;
            }

            .preview-avatar {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>➕ Adicionar Autor</h1>
            <p>Cadastre um novo autor no sistema</p>
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

            <form method="POST" id="autorForm">
                <div class="form-section">
                    <div class="section-title">
                        👤 Informações Básicas
                    </div>

                    <div class="form-group">
                        <label for="nome_completo">Nome Completo <span class="required">*</span></label>
                        <input type="text" id="nome_completo" name="nome_completo" 
                               value="<?= htmlspecialchars($_POST['nome_completo'] ?? '') ?>"
                               placeholder="Digite o nome completo do autor" 
                               required maxlength="100">
                        <div class="help-text">Nome que aparecerá nos livros e listagens</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nacionalidade">Nacionalidade</label>
                            <input type="text" id="nacionalidade" name="nacionalidade" 
                                   value="<?= htmlspecialchars($_POST['nacionalidade'] ?? '') ?>"
                                   placeholder="Ex: Brasileira, Portuguesa, etc." 
                                   maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" 
                                   value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-title">
                        📝 Biografia
                    </div>

                    <div class="form-group">
                        <label for="biografia">Biografia do Autor</label>
                        <textarea id="biografia" name="biografia" 
                                  placeholder="Escreva uma breve biografia do autor, incluindo principais obras, prêmios, formação acadêmica, etc."
                                  maxlength="1000"><?= htmlspecialchars($_POST['biografia'] ?? '') ?></textarea>
                        <div class="char-count">
                            <span id="charCount">0</span>/1000 caracteres
                        </div>
                        <div class="help-text">Informações que aparecem no perfil do autor</div>
                    </div>
                </div>

                <!-- Preview do autor -->
                <div class="form-section">
                    <div class="section-title">
                        👁️ Visualização
                    </div>
                    
                    <div class="preview-card">
                        <div class="preview-header">
                            <div class="preview-avatar" id="previewAvatar">A</div>
                            <div>
                                <div class="preview-name" id="previewName">Nome do Autor</div>
                                <div class="preview-info" id="previewInfo">Aguardando informações...</div>
                            </div>
                        </div>
                        <div id="previewBio" style="font-size: 0.9rem; color: #555; line-height: 1.5; margin-top: 15px;">
                            Biografia aparecerá aqui...
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">💾 Salvar Autor</button>
                    <a href="listar.php" class="btn btn-back">🔙 Voltar à Lista</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Elementos do formulário
        const nomeInput = document.getElementById('nome_completo');
        const nacionalidadeInput = document.getElementById('nacionalidade');
        const dataInput = document.getElementById('data_nascimento');
        const biografiaInput = document.getElementById('biografia');
        
        // Elementos do preview
        const previewAvatar = document.getElementById('previewAvatar');
        const previewName = document.getElementById('previewName');
        const previewInfo = document.getElementById('previewInfo');
        const previewBio = document.getElementById('previewBio');
        const charCount = document.getElementById('charCount');

        // Atualizar preview em tempo real
        function updatePreview() {
            // Nome
            const nome = nomeInput.value.trim();
            if (nome) {
                previewName.textContent = nome;
                previewAvatar.textContent = nome.charAt(0).toUpperCase();
            } else {
                previewName.textContent = 'Nome do Autor';
                previewAvatar.textContent = 'A';
            }

            // Informações
            const info = [];
            if (nacionalidadeInput.value.trim()) {
                info.push('🌍 ' + nacionalidadeInput.value.trim());
            }
            if (dataInput.value) {
                const data = new Date(dataInput.value);
                info.push('📅 ' + data.toLocaleDateString('pt-BR'));
            }
            
            previewInfo.textContent = info.length > 0 ? info.join(' • ') : 'Aguardando informações...';

            // Biografia
            const bio = biografiaInput.value.trim();
            previewBio.textContent = bio || 'Biografia aparecerá aqui...';
            
            // Contador de caracteres
            charCount.textContent = bio.length;
        }

        // Event listeners
        nomeInput.addEventListener('input', updatePreview);
        nacionalidadeInput.addEventListener('input', updatePreview);
        dataInput.addEventListener('change', updatePreview);
        biografiaInput.addEventListener('input', updatePreview);

        // Inicializar preview
        updatePreview();

        // Validação da data
        dataInput.addEventListener('change', function() {
            if (this.value) {
                const selectedDate = new Date(this.value);
                const today = new Date();
                
                if (selectedDate > today) {
                    alert('A data de nascimento não pode ser no futuro.');
                    this.value = '';
                    updatePreview();
                }
            }
        });

        // Foco no primeiro campo
        document.addEventListener('DOMContentLoaded', function() {
            nomeInput.focus();
        });
    </script>
</body>
</html>
