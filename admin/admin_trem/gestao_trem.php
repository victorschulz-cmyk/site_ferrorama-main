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
    $id_trem              = !empty($_POST['id_trem']) ? intval($_POST['id_trem']) : null;
    $prefixo_trem         = trim($_POST['prefixo_trem'] ?? '');
    $modelo_trem          = trim($_POST['modelo_trem'] ?? '');
    $ano_fabricacao       = !empty($_POST['ano_fabricacao']) ? intval($_POST['ano_fabricacao']) : null;
    $capacidade_toneladas = !empty($_POST['capacidade_toneladas']) ? floatval($_POST['capacidade_toneladas']) : 0.00;
    $situacao_trem        = $_POST['situacao_trem'] ?? 'ativo';

    if (empty($prefixo_trem) || empty($modelo_trem)) {
        $mensagem = "Os campos Prefixo e Modelo são obrigatórios!";
        $tipo_mensagem = "erro";
    } else {
        if ($id_trem) {
            $stmt = $conexao->prepare("UPDATE trens SET prefixo_trem = ?, modelo_trem = ?, ano_fabricacao = ?, capacidade_toneladas = ?, situacao_trem = ? WHERE id_trem = ?");
            $stmt->bind_param("ssidsi", $prefixo_trem, $modelo_trem, $ano_fabricacao, $capacidade_toneladas, $situacao_trem, $id_trem);
            
            if ($stmt->execute()) {
                $mensagem = "Trem atualizado com sucesso!";
                $tipo_mensagem = "sucesso";
            } else {
                $mensagem = "Erro ao atualizar o trem no banco de dados.";
                $tipo_mensagem = "erro";
            }
        } else {
            $stmt = $conexao->prepare("INSERT INTO trens (prefixo_trem, modelo_trem, ano_fabricacao, capacidade_toneladas, situacao_trem) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssids", $prefixo_trem, $modelo_trem, $ano_fabricacao, $capacidade_toneladas, $situacao_trem);
            
            if ($stmt->execute()) {
                $mensagem = "Trem cadastrado com sucesso!";
                $tipo_mensagem = "sucesso";
            } else {
                $mensagem = "Erro ao cadastrar o trem. Prefixo pode ser duplicado.";
                $tipo_mensagem = "erro";
            }
        }
    }
}

if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $stmt = $conexao->prepare("DELETE FROM trens WHERE id_trem = ?");
    $stmt->bind_param("i", $id_excluir);
    
    if ($stmt->execute()) {
        $mensagem = "Trem removido com sucesso!";
        $tipo_mensagem = "sucesso";
    } else {
        $mensagem = "Não foi possível excluir o trem. Verifique se existem dependências vinculadas.";
        $tipo_mensagem = "erro";
    }
}

$termo_busca = trim($_GET['busca'] ?? '');
if (!empty($termo_busca)) {
    $stmt = $conexao->prepare("SELECT * FROM trens WHERE prefixo_trem LIKE ? OR modelo_trem LIKE ? ORDER BY id_trem DESC");
    $param = "%{$termo_busca}%";
    $stmt->bind_param("ss", $param, $param);
    $stmt->execute();
    $trens = $stmt->get_result();
} else {
    $trens = $conexao->query("SELECT * FROM trens ORDER BY id_trem DESC");
}

$trem_edicao = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $stmt = $conexao->prepare("SELECT * FROM trens WHERE id_trem = ?");
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $trem_edicao = $stmt->get_result()->fetch_assoc();
}

$nome_completo = $_SESSION['nome'] ?? $_SESSION['login'] ?? 'Admin';
$primeiro_nome = explode(' ', trim($nome_completo))[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Trens - GTT</title>
    <link rel="stylesheet" href="gestao_trem.css">
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
        
        <h1 class="page-title">Gestão de Trens</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="alert-msg <?php echo $tipo_mensagem; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <section class="form-section">
            <h2><?php echo $trem_edicao ? 'Editar Trem' : 'Cadastrar Novo Trem'; ?></h2>
            <form method="POST" action="gestao_trem.php" class="form-trem">
                <input type="hidden" name="id_trem" value="<?php echo $trem_edicao['id_trem'] ?? ''; ?>">
                
                <div class="form-group">
                    <label>Prefixo do Trem *</label>
                    <input type="text" name="prefixo_trem" placeholder="Ex: LOC-1001" required value="<?php echo htmlspecialchars($trem_edicao['prefixo_trem'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Modelo *</label>
                    <input type="text" name="modelo_trem" placeholder="Ex: GE AC44" required value="<?php echo htmlspecialchars($trem_edicao['modelo_trem'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Ano de Fabricação</label>
                    <input type="number" name="ano_fabricacao" min="1900" max="2099" placeholder="Ex: 2014" value="<?php echo htmlspecialchars($trem_edicao['ano_fabricacao'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Capacidade (Toneladas)</label>
                    <input type="number" step="0.01" name="capacidade_toneladas" min="0" placeholder="Ex: 1200.00" value="<?php echo htmlspecialchars($trem_edicao['capacidade_toneladas'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Situação</label>
                    <select name="situacao_trem">
                        <option value="ativo" <?php echo ($trem_edicao['situacao_trem'] ?? '') === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                        <option value="manutencao" <?php echo ($trem_edicao['situacao_trem'] ?? '') === 'manutencao' ? 'selected' : ''; ?>>Manutenção</option>
                        <option value="inativo" <?php echo ($trem_edicao['situacao_trem'] ?? '') === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" name="acao_salvar" class="btn-submit">
                        <?php echo $trem_edicao ? 'Atualizar Trem' : 'Cadastrar Trem'; ?>
                    </button>
                    <?php if ($trem_edicao): ?>
                        <a href="gestao_trem.php" class="btn-cancelar">Cancelar Edição</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="search-section">
            <form method="GET" action="gestao_trem.php" class="search-form">
                <input type="text" name="busca" placeholder="Buscar por prefixo ou modelo..." value="<?php echo htmlspecialchars($termo_busca); ?>">
                <button type="submit" class="btn-search">Buscar</button>
                <?php if (!empty($termo_busca)): ?>
                    <a href="gestao_trem.php" class="btn-clear">Limpar</a>
                <?php endif; ?>
            </form>
        </section>

        <section class="table-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Prefixo</th>
                        <th>Modelo</th>
                        <th>Ano Fab.</th>
                        <th>Capacidade (Ton)</th>
                        <th>Situação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($trens && $trens->num_rows > 0): ?>
                        <?php while ($row = $trens->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id_trem']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['prefixo_trem']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['modelo_trem']); ?></td>
                                <td><?php echo $row['ano_fabricacao'] ?? '-'; ?></td>
                                <td><?php echo number_format($row['capacidade_toneladas'], 2, ',', '.'); ?> t</td>
                                <td>
                                    <span class="badge badge-<?php echo $row['situacao_trem']; ?>">
                                        <?php 
                                            if ($row['situacao_trem'] == 'ativo') echo 'Ativo';
                                            elseif ($row['situacao_trem'] == 'manutencao') echo 'Manutenção';
                                            else echo 'Inativo';
                                        ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <a href="gestao_trem.php?editar=<?php echo $row['id_trem']; ?>" class="btn-edit">Editar</a>
                                    <a href="gestao_trem.php?excluir=<?php echo $row['id_trem']; ?>" class="btn-delete" onclick="return confirm('Deseja realmente excluir este trem?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-table">Nenhum trem encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>