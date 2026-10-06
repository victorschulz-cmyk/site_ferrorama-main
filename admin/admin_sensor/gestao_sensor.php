<?php
session_start();
require_once __DIR__ . '/../../config/conexao.php';

if (!isset($_SESSION['id_usuario']) || ($_SESSION['papel'] ?? '') !== 'administrador') {
    header("Location: ../../pagina-inicial/index.php");
    exit;
}

$mensagem = "";
$tipo_mensagem = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_salvar'])) {
    $id_leitura           = !empty($_POST['id_leitura']) ? intval($_POST['id_leitura']) : null;
    $fk_id_trem           = intval($_POST['fk_id_trem'] ?? 0);
    $data_hora            = trim($_POST['data_hora'] ?? '');
    $velocidade_kmh       = floatval($_POST['velocidade_kmh'] ?? 0);
    $temperatura_motor_c  = floatval($_POST['temperatura_motor_c'] ?? 0);
    $consumo_litros_hora  = floatval($_POST['consumo_litros_hora'] ?? 0);
    $vibracao_mm_s        = floatval($_POST['vibracao_mm_s'] ?? 0);

    if (empty($fk_id_trem) || empty($data_hora)) {
        $mensagem = "Selecione um trem e informe a data/hora da leitura!";
        $tipo_mensagem = "erro";
    } else {
        if ($id_leitura) {
            $stmt = $conexao->prepare("UPDATE leitura_sensor SET fk_id_trem = ?, data_hora = ?, velocidade_kmh = ?, temperatura_motor_c = ?, consumo_litros_hora = ?, vibracao_mm_s = ? WHERE id_leitura = ?");
            $stmt->bind_param("isddddi", $fk_id_trem, $data_hora, $velocidade_kmh, $temperatura_motor_c, $consumo_litros_hora, $vibracao_mm_s, $id_leitura);
            
            if ($stmt->execute()) {
                header("Location: gestao_sensor.php?msg=atualizado");
                exit;
            } else {
                $mensagem = "Erro ao atualizar a leitura no banco de dados.";
                $tipo_mensagem = "erro";
            }
        } else {
            $stmt = $conexao->prepare("INSERT INTO leitura_sensor (fk_id_trem, data_hora, velocidade_kmh, temperatura_motor_c, consumo_litros_hora, vibracao_mm_s) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isdddd", $fk_id_trem, $data_hora, $velocidade_kmh, $temperatura_motor_c, $consumo_litros_hora, $vibracao_mm_s);
            
            if ($stmt->execute()) {
                header("Location: gestao_sensor.php?msg=criado");
                exit;
            } else {
                $mensagem = "Erro ao registrar leitura do sensor.";
                $tipo_mensagem = "erro";
            }
        }
    }
}

if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $stmt = $conexao->prepare("DELETE FROM leitura_sensor WHERE id_leitura = ?");
    $stmt->bind_param("i", $id_excluir);
    
    if ($stmt->execute()) {
        header("Location: gestao_sensor.php?msg=excluido");
        exit;
    } else {
        $mensagem = "Não foi possível excluir a leitura selecionada.";
        $tipo_mensagem = "erro";
    }
}


if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'criado') {
        $mensagem = "Nova leitura do sensor registrada com sucesso!";
        $tipo_mensagem = "sucesso";
    } elseif ($_GET['msg'] === 'atualizado') {
        $mensagem = "Leitura do sensor atualizada com sucesso!";
        $tipo_mensagem = "sucesso";
    } elseif ($_GET['msg'] === 'excluido') {
        $mensagem = "Leitura excluída com sucesso!";
        $tipo_mensagem = "sucesso";
    }
}


$lista_trens = $conexao->query("SELECT id_trem, prefixo_trem, modelo_trem FROM trens ORDER BY prefixo_trem ASC");


$termo_busca = trim($_GET['busca'] ?? '');
if (!empty($termo_busca)) {
    $stmt = $conexao->prepare("SELECT l.*, t.prefixo_trem, t.modelo_trem 
                               FROM leitura_sensor l 
                               INNER JOIN trens t ON l.fk_id_trem = t.id_trem 
                               WHERE t.prefixo_trem LIKE ? OR t.modelo_trem LIKE ? OR l.id_leitura = ?
                               ORDER BY l.data_hora DESC");
    $param_like = "%{$termo_busca}%";
    $param_id = intval($termo_busca);
    $stmt->bind_param("ssi", $param_like, $param_like, $param_id);
    $stmt->execute();
    $leituras = $stmt->get_result(); 
} else {
    $leituras = $conexao->query("SELECT l.*, t.prefixo_trem, t.modelo_trem 
                                 FROM leitura_sensor l 
                                 INNER JOIN trens t ON l.fk_id_trem = t.id_trem 
                                 ORDER BY l.data_hora DESC");
}

$leitura_edicao = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $stmt = $conexao->prepare("SELECT * FROM leitura_sensor WHERE id_leitura = ?");
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $leitura_edicao = $stmt->get_result()->fetch_assoc();
}

$nome_completo = $_SESSION['nome'] ?? $_SESSION['login'] ?? 'Admin';
$primeiro_nome = explode(' ', trim($nome_completo))[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Leituras dos Sensores - GTT</title>
    <link rel="stylesheet" href="gestao_sensor.css">
</head>
<body>

    <nav class="sticky-bar">
        <div class="nav-left">
            <div class="logo-container">
                <a href="../admin.php">
                    <img src="../../images/GTT-logo.png" alt="Logo" class="nav-logo">
                </a>
            </div>
            <ul class="nav-links">
                <li class="nav-item"><a href="../admin.php">Análise</a></li>
                <li class="nav-item active"><a href="../controle.php">Controle</a></li>
            </ul>
        </div>
        <div class="nav-right">
            <a href="../../dashboard/Barra/usuario/usuario.php" class="user-btn">
                <span><?php echo htmlspecialchars($primeiro_nome); ?></span>
                <svg class="user-icon" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </a>
        </div>
    </nav>

    <main class="main-content">
        <a href="../controle.php" class="btn-voltar">&larr; Voltar ao Painel</a>
        
        <h1 class="page-title">Gestão de Leituras dos Sensores</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="alert-msg <?php echo $tipo_mensagem; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <section class="form-section">
            <h2><?php echo $leitura_edicao ? 'Editar Leitura de Sensor' : 'Registrar Nova Leitura'; ?></h2>
            <form method="POST" action="gestao_sensor.php" class="form-sensor">
                <input type="hidden" name="id_leitura" value="<?php echo $leitura_edicao['id_leitura'] ?? ''; ?>">
                
                <div class="form-group">
                    <label>Trem *</label>
                    <select name="fk_id_trem" required>
                        <option value="">-- Selecione o Trem --</option>
                        <?php if ($lista_trens && $lista_trens->num_rows > 0): ?>
                            <?php 
                            $lista_trens->data_seek(0); 
                            while ($trem = $lista_trens->fetch_assoc()): 
                                $selected = ($leitura_edicao['fk_id_trem'] ?? '') == $trem['id_trem'] ? 'selected' : '';
                            ?>
                                <option value="<?php echo $trem['id_trem']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($trem['prefixo_trem'] . ' - ' . $trem['modelo_trem']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data e Hora *</label>
                    <input type="datetime-local" name="data_hora" required value="<?php echo isset($leitura_edicao['data_hora']) ? date('Y-m-d\TH:i', strtotime($leitura_edicao['data_hora'])) : date('Y-m-d\TH:i'); ?>">
                </div>

                <div class="form-group">
                    <label>Velocidade (km/h)</label>
                    <input type="number" step="0.01" name="velocidade_kmh" min="0" placeholder="Ex: 75.50" value="<?php echo htmlspecialchars($leitura_edicao['velocidade_kmh'] ?? '0.00'); ?>">
                </div>

                <div class="form-group">
                    <label>Temperatura do Motor (°C)</label>
                    <input type="number" step="0.01" name="temperatura_motor_c" placeholder="Ex: 82.30" value="<?php echo htmlspecialchars($leitura_edicao['temperatura_motor_c'] ?? '0.00'); ?>">
                </div>

                <div class="form-group">
                    <label>Consumo (Litros/Hora)</label>
                    <input type="number" step="0.01" name="consumo_litros_hora" min="0" placeholder="Ex: 45.20" value="<?php echo htmlspecialchars($leitura_edicao['consumo_litros_hora'] ?? '0.00'); ?>">
                </div>

                <div class="form-group">
                    <label>Vibração (mm/s)</label>
                    <input type="number" step="0.01" name="vibracao_mm_s" min="0" placeholder="Ex: 2.15" value="<?php echo htmlspecialchars($leitura_edicao['vibracao_mm_s'] ?? '0.00'); ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" name="acao_salvar" class="btn-submit">
                        <?php echo $leitura_edicao ? 'Atualizar Leitura' : 'Salvar Leitura'; ?>
                    </button>
                    <?php if ($leitura_edicao): ?>
                        <a href="gestao_sensor.php" class="btn-cancelar">Cancelar Edição</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="search-section">
            <form method="GET" action="gestao_sensor.php" class="search-form">
                <input type="text" name="busca" placeholder="Buscar por prefixo do trem, modelo ou ID..." value="<?php echo htmlspecialchars($termo_busca); ?>">
                <button type="submit" class="btn-search">Buscar</button>
                <?php if (!empty($termo_busca)): ?>
                    <a href="gestao_sensor.php" class="btn-clear">Limpar</a>
                <?php endif; ?>
            </form>
        </section>

        <section class="table-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Trem</th>
                        <th>Data / Hora</th>
                        <th>Velocidade</th>
                        <th>Temp. Motor</th>
                        <th>Consumo</th>
                        <th>Vibração</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($leituras && $leituras->num_rows > 0): ?>
                        <?php while ($row = $leituras->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id_leitura']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['prefixo_trem']); ?></strong> <small>(<?php echo htmlspecialchars($row['modelo_trem']); ?>)</small></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['data_hora'])); ?></td>
                                <td><?php echo number_format($row['velocidade_kmh'], 2, ',', '.'); ?> km/h</td>
                                <td>
                                    <span class="temp-badge <?php echo ($row['temperatura_motor_c'] > 90) ? 'temp-alta' : 'temp-normal'; ?>">
                                        <?php echo number_format($row['temperatura_motor_c'], 1, ',', '.'); ?> °C
                                    </span>
                                </td>
                                <td><?php echo number_format($row['consumo_litros_hora'], 2, ',', '.'); ?> L/h</td>
                                <td><?php echo number_format($row['vibracao_mm_s'], 2, ',', '.'); ?> mm/s</td>
                                <td class="actions-cell">
                                    <a href="gestao_sensor.php?editar=<?php echo $row['id_leitura']; ?>" class="btn-edit">Editar</a>
                                    <a href="gestao_sensor.php?excluir=<?php echo $row['id_leitura']; ?>" class="btn-delete" onclick="return confirm('Deseja realmente excluir esta leitura?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-table">Nenhuma leitura de sensor encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>