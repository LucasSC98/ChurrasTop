<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$db = database();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $adultos = (int) ($_POST['adultos'] ?? 0);
    $criancas = (int) ($_POST['criancas'] ?? 0);
    $data = (string) ($_POST['data_churrasco'] ?? '');
    $duracao = (string) ($_POST['duracao'] ?? '');
    $tipo = (string) ($_POST['tipo'] ?? '');
    $duracoesValidas = ['2 horas', '4 horas', '6 horas ou mais'];
    $tiposValidos = ['Econômico', 'Tradicional', 'ChurrasTop'];
    $dataValida = DateTime::createFromFormat('Y-m-d', $data);
    if (
        $nome === ''
        || $adultos < 0
        || $criancas < 0
        || $adultos + $criancas === 0
        || $data === ''
        || !$dataValida
        || $dataValida->format('Y-m-d') !== $data
        || !in_array($duracao, $duracoesValidas, true)
        || !in_array($tipo, $tiposValidos, true)
    ) {
        $error = 'Preencha o nome, data e informe pelo menos um participante.';
    } else {
        $statement = $db->prepare(
            'INSERT INTO churrascos (nome, data_churrasco, quantidade_adultos, quantidade_criancas, duracao, tipo, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $statement->execute([$nome, $data, $adultos, $criancas, $duracao, $tipo, currentUserId()]);
        header('Location: /');
        exit;
    }
}
$pageTitle = 'Novo churrasco';
require __DIR__ . '/../../includes/header.php';
?>
<div class="card p-4 col-lg-8 mx-auto">
    <h1 class="h3">Criar churrasco</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" class="row g-3">
        <div class="col-md-8"><label class="form-label">Nome</label><input class="form-control" name="nome" required></div>
        <div class="col-md-4"><label class="form-label">Data</label><input class="form-control" type="date" name="data_churrasco" required></div>
        <div class="col-md-4"><label class="form-label">Adultos</label><input class="form-control" type="number" min="0" name="adultos" value="1"></div>
        <div class="col-md-4"><label class="form-label">Crianças</label><input class="form-control" type="number" min="0" name="criancas" value="0"></div>
        <div class="col-md-4"><label class="form-label">Duração</label><select class="form-select" name="duracao"><option>2 horas</option><option selected>4 horas</option><option>6 horas ou mais</option></select></div>
        <div class="col-md-6"><label class="form-label">Tipo</label><select class="form-select" name="tipo"><option>Econômico</option><option selected>Tradicional</option><option>ChurrasTop</option></select></div>
        <div class="col-12"><button class="btn btn-danger">Salvar churrasco</button><a class="btn btn-outline-secondary ms-2" href="/">Cancelar</a></div>
    </form>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
