<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
$db = database();
$id = (int) ($_GET['id'] ?? 0);
$statement = $db->prepare('SELECT id, nome FROM churrascos WHERE id = ? AND usuario_id = ?');
$statement->execute([$id, currentUserId()]);
$churrasco = $statement->fetch();
if (!$churrasco) { header('Location: /'); exit; }
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = (int) ($_POST['request_id'] ?? 0);
    $decision = $_POST['decision'] === 'aprovada' ? 'aprovada' : 'recusada';
    $request = $db->prepare('SELECT participante_id FROM solicitacoes_participacao WHERE id = ? AND churrasco_id = ? AND status = "pendente"');
    $request->execute([$requestId, $id]);
    $data = $request->fetch();
    if ($data) {
        $db->beginTransaction();
        if ($decision === 'aprovada') {
            $link = $db->prepare('INSERT IGNORE INTO churrasco_participante (churrasco_id, participante_id) VALUES (?, ?)');
            $link->execute([$id, (int) $data['participante_id']]);
        }
        $update = $db->prepare('UPDATE solicitacoes_participacao SET status = ? WHERE id = ?');
        $update->execute([$decision, $requestId]);
        $db->commit();
        $message = $decision === 'aprovada' ? 'Participante aprovado.' : 'Pedido recusado.';
    }
}
$statement = $db->prepare('SELECT s.id, s.status, p.nome, p.telefone, pr.nome produto FROM solicitacoes_participacao s INNER JOIN participantes p ON p.id = s.participante_id INNER JOIN produtos pr ON pr.id = s.produto_id WHERE s.churrasco_id = ? ORDER BY s.status, s.criado_em DESC');
$statement->execute([$id]);
$requests = $statement->fetchAll();
$pageTitle = 'Solicitações | ChurrasTop';
require __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4"><div><a href="/" class="text-danger text-decoration-none">← Dashboard</a><h1 class="h3"><?= htmlspecialchars($churrasco['nome']) ?></h1></div><a class="btn btn-outline-danger" target="_blank" href="/participar.php?id=<?= $id ?>">Abrir convite</a></div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<div class="card p-4"><h2 class="h4">Pedidos de participação</h2><?php if ($requests): ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Pessoa</th><th>Telefone</th><th>Vai levar</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($requests as $request): ?><tr><td><?= htmlspecialchars($request['nome']) ?></td><td><?= htmlspecialchars($request['telefone']) ?></td><td><span class="badge text-bg-warning"><?= htmlspecialchars($request['produto']) ?></span></td><td><?= ucfirst($request['status']) ?></td><td><?php if ($request['status'] === 'pendente'): ?><form method="post" class="d-inline"><input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>"><input type="hidden" name="decision" value="aprovada"><button class="btn btn-success btn-sm">Aprovar</button></form><form method="post" class="d-inline"><input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>"><input type="hidden" name="decision" value="recusada"><button class="btn btn-outline-danger btn-sm">Recusar</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><p class="text-muted">Nenhum pedido recebido.</p><?php endif; ?></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
