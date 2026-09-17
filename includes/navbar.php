<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
$isLoggedIn = isset($_SESSION['usuario_id']);
$userName = $_SESSION['usuario_nome'] ?? null;

if ($isLoggedIn && !$userName && function_exists('database')) {
    $st = database()->prepare('SELECT nome FROM usuarios WHERE id = ?');
    $st->execute([$_SESSION['usuario_id']]);
    $u = $st->fetch();
    if ($u && !empty($u['nome'])) {
        $userName = $u['nome'];
        $_SESSION['usuario_nome'] = $userName;
    }
}

$userInitial = !empty($userName) ? mb_strtoupper(mb_substr(trim($userName), 0, 1)) : '🔥';
$firstName = !empty($userName) ? explode(' ', trim($userName))[0] : 'Churrasqueiro';
$isHome = ($currentUri === '/' || str_starts_with($currentUri, '/index.php'));
?>
<div class="header-top-accent"></div>

<header class="app-header sticky-top">
    <nav class="navbar navbar-expand-lg app-navbar py-2 py-lg-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="/">
                <div class="brand-emblem-wrap" style="width:42px;height:42px;min-width:42px;display:flex;align-items:center;justify-content:center;">
                    <img src="/assets/icone.png" alt="Logo ChurrasTop" class="brand-icon-img" style="width:34px;height:34px;max-width:34px;max-height:34px;object-fit:contain;display:block;">
                </div>
                <div class="d-flex flex-column brand-info">
                    <div class="d-flex align-items-center gap-2">
                        <span class="brand-title">Churras<span class="brand-highlight">Top</span></span>
                    </div>
                    <span class="brand-subtitle">Gestão Inteligente de Churrasco</span>
                </div>
            </a>

            <button class="navbar-toggler custom-toggler border-0 shadow-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Alternar navegação">
                <span class="toggler-icon-box"><i class="bi bi-list fs-3 text-white"></i></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <?php if ($isLoggedIn): ?>
                    <ul class="navbar-nav mx-lg-auto mb-3 mb-lg-0 gap-1 gap-lg-2 pt-3 pt-lg-0">
                        <li class="nav-item">
                            <a class="nav-link custom-nav-link <?= $isHome ? 'active' : '' ?>" href="/">
                                <i class="bi bi-grid-1x2-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                    </ul>

                    <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3 pt-2 pt-lg-0 border-top-mobile">
                        <a class="btn btn-create-churras text-nowrap" href="/pages/churrasco_form.php">
                            <span class="btn-icon-pulse"><i class="bi bi-plus-lg"></i></span>
                            <span>Novo Churrasco</span>
                        </a>

                        <div class="user-chip d-flex align-items-center gap-2">
                            <div class="user-avatar" title="<?= htmlspecialchars($userName ?? 'Churrasqueiro') ?>">
                                <?= htmlspecialchars($userInitial) ?>
                            </div>
                            <div class="d-none d-sm-flex flex-column user-details">
                                <span class="user-name-text"><?= htmlspecialchars($firstName) ?></span>
                                <span class="user-badge-role"><i class="bi bi-patch-check-fill text-warning"></i> Anfitrião</span>
                            </div>
                        </div>

                        <a class="btn-nav-logout" href="/logout.php" title="Sair da conta">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="d-lg-none ms-1 fw-semibold">Sair</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="navbar-nav ms-auto align-items-lg-center gap-2 pt-3 pt-lg-0">
                        <a class="nav-link custom-nav-link <?= str_starts_with($currentUri, '/login.php') ? 'active' : '' ?>" href="/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                        </a>
                        <a class="btn btn-create-churras text-nowrap" href="/cadastro.php">
                            <i class="bi bi-person-plus-fill me-1"></i> Criar Conta Grátis
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
