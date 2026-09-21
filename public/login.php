<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = database();
    $statement = $db->prepare('SELECT id, nome, senha FROM usuarios WHERE email = ?');
    $statement->execute([trim((string) ($_POST['email'] ?? ''))]);
    $user = $statement->fetch();
    if ($user && password_verify((string) ($_POST['senha'] ?? ''), $user['senha'])) {
        $_SESSION['usuario_id'] = (int) $user['id'];
        $_SESSION['usuario_nome'] = (string) ($user['nome'] ?? '');
        header('Location: /');
        exit;
    }
    $error = 'Email ou senha inválidos.';
}
$pageTitle = 'Entrar | ChurrasTop';
require __DIR__ . '/../includes/header.php';
?>
<div class="card p-4 col-md-6 col-lg-4 mx-auto"><h1 class="h3">Entrar</h1>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post"><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required><label class="form-label">Senha</label><input class="form-control mb-3" type="password" name="senha" required><button class="btn btn-danger w-100">Entrar</button></form>
<p class="mt-3 mb-0">Ainda não tem conta? <a href="/cadastro.php">Cadastre-se</a></p></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
