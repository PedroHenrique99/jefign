<?php
/**
 *  https://jefing.com/web_admin_setup.php
 */

// Verificar se já existe um admin
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/classes/AuthManager.php';

$auth = new AuthManager();
$message = '';
$success = false;

// Verificar se já existem usuários
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT COUNT(*) as count FROM admin_users");
    $result = $stmt->fetch();
    $hasUsers = $result['count'] > 0;
} catch (Exception $e) {
    $hasUsers = false;
    $message = "Erro ao verificar usuários: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$hasUsers) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validações
    if (empty($username) || empty($email) || empty($password)) {
        $message = "Todos os campos são obrigatórios.";
    } elseif (strlen($username) < 3) {
        $message = "Nome de usuário deve ter pelo menos 3 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email inválido.";
    } elseif (strlen($password) < 8) {
        $message = "Senha deve ter pelo menos 8 caracteres.";
    } elseif ($password !== $confirm_password) {
        $message = "Senhas não coincidem.";
    } else {
        // Criar usuário
        $result = $auth->createUser($username, $email, $password);
        if ($result['success']) {
            $success = true;
            $message = "✅ Usuário criado com sucesso! Credenciais: $username / $password";
        } else {
            $message = "❌ Erro: " . $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin - JEFIGN</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"] { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; 
        }
        button { background: #366BB3; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
        button:hover { background: #2a5490; }
        .message { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <h1>🔧 Setup Administrativo - JEFIGN</h1>
    
    <?php if ($hasUsers): ?>
        <div class="message warning">
            ⚠️ <strong>Já existem usuários administrativos no sistema.</strong><br>
            Este setup não pode ser usado novamente por segurança.<br><br>
            <strong>Para acessar:</strong><br>
            • URL: <a href="/admin">/admin</a><br>
            • Use suas credenciais existentes
        </div>
    <?php else: ?>
        
        <?php if ($message): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Nome de Usuário:</label>
                    <input type="text" id="username" name="username" required minlength="3">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Senha:</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Senha:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit">Criar Usuário Administrativo</button>
            </form>
        <?php else: ?>
            <div class="message success">
                <h3>✅ Setup Concluído!</h3>
                <p><strong>Próximos passos:</strong></p>
                <ol>
                    <li><strong>DELETE ESTE ARQUIVO</strong> (web_admin_setup.php) por segurança</li>
                    <li>Acesse: <a href="/admin">/admin</a></li>
                    <li>Faça login com suas credenciais</li>
                </ol>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <hr style="margin: 30px 0;">
    <p><small>⚠️ <strong>IMPORTANTE:</strong> Delete este arquivo após criar o usuário!</small></p>
</body>
</html>
