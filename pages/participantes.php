<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../functions/produtos.php';
requireLogin();
$db = database();
$id = (int) ($_GET['id'] ?? 0);
$owner = $db->prepare('SELECT id, nome FROM churrascos WHERE id = ? AND usuario_id = ?');
$owner->execute([$id, currentUserId()]);
$churrasco = $owner->fetch();
if (!$churrasco) { header('Location: /'); exit; }
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'add') {
        $name = trim((string) ($_POST['nome'] ?? ''));
        $phone = trim((string) ($_POST['telefone'] ?? ''));
        if ($name !== '' && $phone !== '') {
            $insert = $db->prepare('INSERT INTO participantes (nome, telefone, tipo) VALUES (?, ?, ?)');
            $insert->execute([$name, $phone, $_POST['tipo'] === 'crianca' ? 'crianca' : 'adulto']);
            $participantId = (int) $db->lastInsertId();
            $link = $db->prepare('INSERT INTO churrasco_participante (churrasco_id, participante_id, valor_contribuicao) VALUES (?, ?, ?)');
            $link->execute([$id, $participantId, max(0, (float) ($_POST['valor'] ?? 0))]);
            $message = 'Participante adicionado.';
        }
    } elseif ($action === 'payment') {
        $update = $db->prepare('UPDATE churrasco_participante SET status_pagamento = ?, valor_contribuicao = ? WHERE churrasco_id = ? AND participante_id = ?');
        $update->execute([$_POST['status'] === 'pago' ? 'pago' : 'pendente', max(0, (float) $_POST['valor']), $id, (int) $_POST['participante_id']]);
        $message = 'Pagamento atualizado.';
    }
}
$statement = $db->prepare('SELECT p.*, cp.valor_contribuicao, cp.status_pagamento FROM participantes p INNER JOIN churrasco_participante cp ON cp.participante_id = p.id WHERE cp.churrasco_id = ? ORDER BY p.nome');
$statement->execute([$id]);
$participantes = $statement->fetchAll();
$pageTitle = 'Participantes | ChurrasTop';
require __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4"><div><a href="/" class="text-danger text-decoration-none">← Dashboard</a><h1 class="h3"><?= htmlspecialchars($churrasco['nome']) ?></h1></div><a class="btn btn-warning" href="/pages/solicitacoes.php?id=<?= $id ?>">Pedidos</a></div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<div class="row g-4"><div class="col-lg-4"><div class="card p-4"><h2 class="h5">Adicionar participante</h2><form method="post"><input type="hidden" name="action" value="add"><label class="form-label">Nome</label><input class="form-control mb-2" name="nome" required><label class="form-label">Telefone</label><input class="form-control mb-2" name="telefone" required><label class="form-label">Tipo</label><select class="form-select mb-2" name="tipo"><option value="adulto">Adulto</option><option value="crianca">Criança</option></select><label class="form-label">Contribuição</label><input class="form-control mb-3" name="valor" type="number" min="0" step="0.01" value="0"><button class="btn btn-danger w-100">Adicionar</button></form></div></div>
<div class="col-lg-8"><div class="card p-4"><h2 class="h5">Participantes e pagamentos</h2><?php if ($participantes): ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nome</th><th>Tipo</th><th>Contribuição</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($participantes as $p): ?><tr><td><?= htmlspecialchars($p['nome']) ?><small class="d-block text-muted"><?= htmlspecialchars($p['telefone']) ?></small></td><td><?= $p['tipo'] === 'crianca' ? 'Criança' : 'Adulto' ?></td><td><?= moeda((float) $p['valor_contribuicao']) ?></td><td><span class="badge text-bg-<?= $p['status_pagamento'] === 'pago' ? 'success' : 'warning' ?>"><?= ucfirst($p['status_pagamento']) ?></span></td><td><form method="post"><input type="hidden" name="action" value="payment"><input type="hidden" name="participante_id" value="<?= (int) $p['id'] ?>"><input type="hidden" name="valor" value="<?= htmlspecialchars((string) $p['valor_contribuicao']) ?>"><input type="hidden" name="status" value="<?= $p['status_pagamento'] === 'pago' ? 'pendente' : 'pago' ?>"><button class="btn btn-sm btn-outline-secondary"><?= $p['status_pagamento'] === 'pago' ? 'Marcar pendente' : 'Marcar pago' ?></button></form></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><p class="text-muted">Nenhum participante ainda.</p><?php endif; ?></div></div></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
