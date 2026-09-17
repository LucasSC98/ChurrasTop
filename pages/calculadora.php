<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../functions/calculadora.php';
require_once __DIR__ . '/../../functions/produtos.php';
requireLogin();

$db = database();
$churrascoId = (int) ($_GET['id'] ?? 0);
$statement = $db->prepare('SELECT * FROM churrascos WHERE id = ? AND usuario_id = ?');
$statement->execute([$churrascoId, currentUserId()]);
$churrasco = $statement->fetch();
if (!$churrasco) {
    header('Location: /');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'salvar_lista') {
    $db->beginTransaction();
    try {
        $db->prepare('DELETE FROM churrasco_produto WHERE churrasco_id = ?')->execute([$churrascoId]);
        $insert = $db->prepare('INSERT INTO churrasco_produto (churrasco_id, produto_id, quantidade) VALUES (?, ?, ?)');
        foreach ((array) ($_POST['quantidades'] ?? []) as $produtoId => $quantidade) {
            if ((float) $quantidade >= 0) {
                $insert->execute([$churrascoId, (int) $produtoId, (float) $quantidade]);
            }
        }
        $db->commit();
    } catch (PDOException $exception) {
        $db->rollBack();
        throw $exception;
    }
    header('Location: /pages/lista_compras.php?id=' . $churrascoId);
    exit;
}

$busca      = trim((string) ($_GET['busca'] ?? ''));
$categoria  = trim((string) ($_GET['categoria'] ?? ''));
$precoMin   = (isset($_GET['preco_min']) && $_GET['preco_min'] !== '') ? (float) $_GET['preco_min'] : null;
$precoMax   = (isset($_GET['preco_max']) && $_GET['preco_max'] !== '') ? (float) $_GET['preco_max'] : null;

$todosProdutos  = $db->query('SELECT * FROM produtos WHERE ativo = 1 ORDER BY categoria, nome')->fetchAll();
$categorias = array_unique(array_column($todosProdutos, 'categoria'));
sort($categorias);

if ($busca !== '' || $categoria !== '' || $precoMin !== null || $precoMax !== null) {
    $produtos = filtrarProdutos($todosProdutos, $busca, $categoria, $precoMin, $precoMax);
} else {
    $produtos = $todosProdutos;
}

$errosValidacao = validarArrayProdutos($produtos);

if ($errosValidacao === []) {
    $lista = calcularLista($produtos, (int) $churrasco['quantidade_adultos'], (int) $churrasco['quantidade_criancas'], $churrasco['tipo'], $churrasco['duracao']);
    $total = totalLista($lista);
} else {
    $lista = [];
    $total = 0.0;
}

$pessoas   = (int) $churrasco['quantidade_adultos'] + (int) $churrasco['quantidade_criancas'];
$pageTitle = 'Calculadora | ChurrasTop';
require __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/" class="text-danger text-decoration-none">← Dashboard</a>
        <h1 class="h3 mb-0"><?= htmlspecialchars($churrasco['nome']) ?></h1>
    </div>
    <span class="badge text-bg-warning"><?= htmlspecialchars($churrasco['tipo']) ?></span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card p-3"><small class="text-muted">Pessoas equivalentes</small><h2><?= number_format((float) $churrasco['quantidade_adultos'] + ((int) $churrasco['quantidade_criancas'] * .5), 1, ',', '.') ?></h2></div></div>
    <div class="col-md-4"><div class="card p-3"><small class="text-muted">Total estimado</small><h2><?= moeda($total) ?></h2></div></div>
    <div class="col-md-4"><div class="card p-3"><small class="text-muted">Valor por participante</small><h2><?= $pessoas ? moeda($total / $pessoas) : 'R$ 0,00' ?></h2></div></div>
</div>

<?php if ($errosValidacao !== []): ?>
    <div class="alert alert-danger">
        <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Atenção — Lista não calculada:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach ($errosValidacao as $erro): ?>
                <li><?= htmlspecialchars($erro) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card p-4 mb-4">
    <h2 class="h5 mb-3"><i class="bi bi-funnel-fill me-1 text-danger"></i> Filtrar produtos</h2>
    <form method="get" class="row g-2 align-items-end">
        <input type="hidden" name="id" value="<?= $churrascoId ?>">
        <div class="col-md-4">
            <label class="form-label">Buscar por nome</label>
            <input class="form-control" type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Ex: carne, cerveja…">
        </div>
        <div class="col-md-3">
            <label class="form-label">Categoria</label>
            <select class="form-select" name="categoria">
                <option value="">Todas</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Preço mín. (R$)</label>
            <input class="form-control" type="number" step="0.01" min="0" name="preco_min" value="<?= $precoMin !== null ? htmlspecialchars((string) $precoMin) : '' ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Preço máx. (R$)</label>
            <input class="form-control" type="number" step="0.01" min="0" name="preco_max" value="<?= $precoMax !== null ? htmlspecialchars((string) $precoMax) : '' ?>">
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button class="btn btn-danger w-100" type="submit"><i class="bi bi-search"></i></button>
            <a class="btn btn-outline-secondary" href="/pages/calculadora.php?id=<?= $churrascoId ?>"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
    <?php if ($busca !== '' || $categoria !== '' || $precoMin !== null || $precoMax !== null): ?>
        <small class="text-muted mt-2 d-block">Exibindo <strong><?= count($produtos) ?></strong> de <strong><?= count($todosProdutos) ?></strong> produtos.</small>
    <?php endif; ?>
</div>

<div class="card p-4">
    <h2 class="h4">Lista de compras sugerida</h2>
    <?php if ($lista): ?>
    <form method="post">
        <input type="hidden" name="action" value="salvar_lista">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Produto</th><th>Categoria</th><th>Quantidade</th><th>Preço unitário</th><th>Subtotal</th></tr></thead>
                <tbody>
                <?php foreach ($lista as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nome']) ?></td>
                        <td><?= htmlspecialchars($item['categoria']) ?></td>
                        <td><input class="form-control form-control-sm" style="max-width:120px" type="number" min="0" step="0.1" name="quantidades[<?= (int) $item['id'] ?>]" value="<?= htmlspecialchars((string) $item['quantidade']) ?>"> <?= htmlspecialchars($item['unidade_medida']) ?></td>
                        <td><?= moeda((float) $item['preco']) ?></td>
                        <td><?= moeda((float) $item['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr class="fw-bold"><td colspan="4" class="text-end">Total sugerido</td><td><?= moeda($total) ?></td></tr></tfoot>
            </table>
        </div>
        <button class="btn btn-danger">Salvar lista de compras</button>
        <a class="btn btn-outline-secondary ms-2" href="/pages/lista_compras.php?id=<?= $churrascoId ?>">Ver lista salva</a>
    </form>
    <?php elseif ($errosValidacao === []): ?>
        <p class="text-muted">Nenhum produto corresponde ao filtro aplicado.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
