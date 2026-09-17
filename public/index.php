<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$db = database();
$userId = currentUserId();
$statement = $db->prepare('SELECT nome FROM usuarios WHERE id = ?');
$statement->execute([$userId]);
$user = $statement->fetch();
$statement = $db->prepare(
    'SELECT c.*, COUNT(cp.participante_id) AS participantes
     FROM churrascos c LEFT JOIN churrasco_participante cp ON cp.churrasco_id = c.id
     WHERE c.usuario_id = ? GROUP BY c.id ORDER BY c.data_churrasco'
);
$statement->execute([$userId]);
$churrascos      = [];
$totalParticipantes = 0;
$totalEstimado   = 0.0;
while ($churrasco = $statement->fetch()) {
    $totalParticipantes += (int) $churrasco['participantes'];
    $churrascos[] = $churrasco;
}
$pageTitle = 'ChurrasTop | Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<section class="hero rounded-4 text-white p-4 p-md-5 mb-4">
    <div class="eyebrow mb-2">Seu painel de organização</div>
    <h1 class="display-6 fw-bold">Olá, <?= htmlspecialchars($user['nome'] ?? 'churrasqueiro') ?>! 🔥</h1>
    <p class="lead mb-0">Planeje o próximo encontro e deixe o churrasco no ponto.</p>
</section>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card stat-fire p-4"><div class="stat-label">Churrascos cadastrados</div><div class="stat-value"><?= count($churrascos) ?></div></div></div>
    <div class="col-md-4"><div class="card stat-card stat-users p-4"><div class="stat-label">Participantes confirmados</div><div class="stat-value"><?= $totalParticipantes ?></div></div></div>
    <div class="col-md-4"><div class="card stat-card stat-calendar p-4"><div class="stat-label">Próximo churrasco</div><div class="stat-value h4"><?= !empty($churrascos) ? date('d/m/Y', strtotime($churrascos[0]['data_churrasco'])) : 'Nenhum' ?></div></div></div>
</div>
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><div><div class="section-title">Meus churrascos</div><small class="text-muted">Acompanhe seus próximos encontros</small></div><a class="btn btn-primary" href="/pages/churrasco_form.php"><i class="bi bi-plus-lg me-1"></i> Novo churrasco</a></div>
    <?php if ($churrascos): ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nome</th><th>Data</th><th>Participantes</th><th>Tipo</th><th></th></tr></thead><tbody>
        <?php foreach ($churrascos as $churrasco): ?><tr><td class="fw-semibold"><?= htmlspecialchars($churrasco['nome']) ?></td><td><?= date('d/m/Y', strtotime($churrasco['data_churrasco'])) ?></td><td><?= (int) $churrasco['participantes'] ?></td><td><span class="badge text-bg-warning"><?= htmlspecialchars($churrasco['tipo']) ?></span></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-danger" href="/pages/calculadora.php?id=<?= (int) $churrasco['id'] ?>">Calculadora</a> <a class="btn btn-sm btn-outline-secondary" href="/pages/participantes.php?id=<?= (int) $churrasco['id'] ?>">Pessoas</a> <a class="btn btn-sm btn-warning" target="_blank" href="/participar.php?id=<?= (int) $churrasco['id'] ?>">Convite</a></td></tr><?php endforeach; ?>
    </tbody></table></div><?php else: ?><div class="empty-state"><div class="fs-1 mb-2">🔥</div><strong>Seu primeiro churrasco começa aqui</strong><p class="mb-3">Crie um evento e organize tudo em um só lugar.</p><a class="btn btn-primary" href="/pages/churrasco_form.php">Criar churrasco</a></div><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
