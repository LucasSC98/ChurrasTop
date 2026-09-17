<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../functions/produtos.php';
requireLogin();
$db = database();
$id = (int) ($_GET['id'] ?? 0);
$statement = $db->prepare('SELECT c.* FROM churrascos c WHERE c.id = ? AND c.usuario_id = ?');
$statement->execute([$id, currentUserId()]);
$churrasco = $statement->fetch();
if (!$churrasco) { header('Location: /'); exit; }
$statement = $db->prepare('SELECT cp.quantidade, p.nome, p.categoria, p.preco, p.unidade_medida, cp.quantidade * p.preco AS subtotal FROM churrasco_produto cp INNER JOIN produtos p ON p.id = cp.produto_id WHERE cp.churrasco_id = ? ORDER BY p.categoria, p.nome');
$statement->execute([$id]);
$lista = $statement->fetchAll();
$total = 0.0;
foreach ($lista as $item) { $total += (float) $item['subtotal']; }
$pageTitle = 'Lista de compras | ChurrasTop';
require __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4"><div><a href="/pages/calculadora.php?id=<?= $id ?>" class="text-danger text-decoration-none">← Calculadora</a><h1 class="h3"><?= htmlspecialchars($churrasco['nome']) ?></h1></div><a class="btn btn-outline-danger" href="/pages/participantes.php?id=<?= $id ?>">Participantes</a></div>
<div class="card p-4"><h2 class="h4">Lista de compras salva</h2><?php if ($lista): ?><div class="table-responsive"><table class="table"><thead><tr><th>Produto</th><th>Categoria</th><th>Quantidade</th><th>Preço</th><th>Subtotal</th></tr></thead><tbody><?php foreach ($lista as $item): ?><tr><td><?= htmlspecialchars($item['nome']) ?></td><td><?= htmlspecialchars($item['categoria']) ?></td><td><?= number_format((float) $item['quantidade'], 1, ',', '.') ?> <?= htmlspecialchars($item['unidade_medida']) ?></td><td><?= moeda((float) $item['preco']) ?></td><td><?= moeda((float) $item['subtotal']) ?></td></tr><?php endforeach; ?></tbody><tfoot><tr class="fw-bold"><td colspan="4" class="text-end">Total</td><td><?= moeda($total) ?></td></tr></tfoot></table></div><?php else: ?><p class="text-muted">Salve a lista pela calculadora para vê-la aqui.</p><?php endif; ?></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
