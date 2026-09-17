<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/produtos.php';
$db = database();
$id = (int) ($_GET['id'] ?? 0);
$statement = $db->prepare('SELECT id, nome, data_churrasco, tipo FROM churrascos WHERE id = ?');
$statement->execute([$id]);
$churrasco = $statement->fetch();
$message = null;
$messageType = 'success';
if ($churrasco && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $produtoId = (int) ($_POST['produto_id'] ?? 0);
    if ($nome === '' || $telefone === '' || $produtoId < 1) {
        $message = 'Informe seus dados e escolha o que vai levar.';
        $messageType = 'danger';
    } else {
        $product = $db->prepare('SELECT nome, limite_repeticao FROM produtos WHERE id = ? AND ativo = 1');
        $product->execute([$produtoId]);
        $productData = $product->fetch();
        $find = $db->prepare('SELECT id FROM participantes WHERE telefone = ? LIMIT 1');
        $find->execute([$telefone]);
        $participantId = $find->fetchColumn();
        $count = $db->prepare("SELECT COUNT(*) FROM solicitacoes_participacao WHERE churrasco_id = ? AND produto_id = ? AND status = 'pendente'");
        $count->execute([$id, $produtoId]);
        if (!$productData) {
            $message = 'Produto inválido.';
            $messageType = 'danger';
        } elseif ((int) $count->fetchColumn() >= (int) $productData['limite_repeticao']) {
            $message = 'Esse item já atingiu o limite de pessoas. Escolha outro para equilibrar a lista.';
            $messageType = 'danger';
        } elseif ($participantId) {
            $duplicate = $db->prepare('SELECT COUNT(*) FROM solicitacoes_participacao WHERE churrasco_id = ? AND participante_id = ? AND status <> "recusada"');
            $duplicate->execute([$id, (int) $participantId]);
            if ((int) $duplicate->fetchColumn() > 0) {
                $message = 'Você já enviou uma solicitação para este churrasco.';
                $messageType = 'danger';
            }
        }
        if (!$message) {
            if (!$participantId) {
                $insert = $db->prepare("INSERT INTO participantes (nome, telefone, tipo) VALUES (?, ?, 'adulto')");
                $insert->execute([$nome, $telefone]);
                $participantId = (int) $db->lastInsertId();
            }
            $insert = $db->prepare('INSERT INTO solicitacoes_participacao (churrasco_id, participante_id, produto_id) VALUES (?, ?, ?)');
            $insert->execute([$id, (int) $participantId, $produtoId]);
            $message = 'Pedido enviado! O organizador precisa aprovar sua participação.';
        }
    }
}
$products = [];
$counts = [];
if ($churrasco) {
    $products = $db->query('SELECT id, nome, categoria, limite_repeticao FROM produtos WHERE ativo = 1 ORDER BY categoria, nome')->fetchAll();
    $statement = $db->prepare("SELECT produto_id, COUNT(*) total FROM solicitacoes_participacao WHERE churrasco_id = ? AND status = 'pendente' GROUP BY produto_id");
    $statement->execute([$id]);
    foreach ($statement->fetchAll() as $row) { $counts[(int) $row['produto_id']] = (int) $row['total']; }
}
$pageTitle = 'Participar | ChurrasTop';
require __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center"><div class="col-lg-7"><div class="card p-4 p-md-5">
<?php if (!$churrasco): ?><div class="alert alert-danger">Churrasco não encontrado.</div><a href="/login.php">Entrar</a>
<?php else: ?><h1 class="h3">Participar de <?= htmlspecialchars($churrasco['nome']) ?></h1><p class="text-muted"><?= date('d/m/Y', strtotime($churrasco['data_churrasco'])) ?> · <?= htmlspecialchars($churrasco['tipo']) ?></p>
<?php if ($message): ?><div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<div class="alert alert-info">Escolha um item com vagas. O contador mostra os pedidos pendentes; cada produto tem um limite para evitar repetição.</div>
<form method="post"><label class="form-label">Nome</label><input class="form-control mb-3" name="nome" required><label class="form-label">Telefone/WhatsApp</label><input class="form-control mb-3" name="telefone" required><label class="form-label">O que você vai levar?</label><select class="form-select mb-3" name="produto_id" required><option value="">Escolha um produto</option><?php foreach ($products as $product): $used = $counts[(int) $product['id']] ?? 0; ?><option value="<?= (int) $product['id'] ?>" <?= $used >= (int) $product['limite_repeticao'] ? 'disabled' : '' ?>><?= htmlspecialchars($product['nome']) ?> — <?= $used ?>/<?= (int) $product['limite_repeticao'] ?> pessoas</option><?php endforeach; ?></select><button class="btn btn-danger">Enviar pedido</button></form>
<?php endif; ?></div></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
