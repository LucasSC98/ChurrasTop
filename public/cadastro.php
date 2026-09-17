<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');
    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
        $error = 'Informe nome, email válido e senha com pelo menos 6 caracteres.';
    } else {
        try {
            $statement = database()->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
            $statement->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
            header('Location: /login.php');
            exit;
        } catch (PDOException $exception) {
            $error = 'Este email já está cadastrado.';
        }
    }
}
$pageTitle = 'Cadastro | ChurrasTop';
require __DIR__ . '/../includes/header.php';
?>
<div class="card p-4 col-md-6 col-lg-4 mx-auto"><h1 class="h3">Criar conta</h1>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post"><label class="form-label">Nome</label><input class="form-control mb-3" name="nome" required><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required><label class="form-label">Senha</label><input class="form-control mb-3" type="password" name="senha" minlength="6" required><button class="btn btn-danger w-100">Cadastrar</button></form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
