<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pagina-inicial/index.php");
    exit;
}

if (isset($_SESSION['papel']) && $_SESSION['papel'] !== 'administrador') {
    header("Location: ../dashboard/dashboard.php");
    exit;
}

if (isset($_SESSION['nome']) && !empty($_SESSION['nome'])) {
    $nome_completo = $_SESSION['nome'];
} else {
    $id_usuario = $_SESSION['id_usuario'];
    $stmt = $conexao->prepare("SELECT nome FROM usuario WHERE id = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($user = $res->fetch_assoc()) {
        $nome_completo = $user['nome'];
    } else {
        $nome_completo = $_SESSION['login'] ?? 'Usuário';
    }
}

$partes_nome = explode(' ', trim($nome_completo));
$primeiro_nome = $partes_nome[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle - Admin</title>
    <link rel="stylesheet" href="controle.css">
</head>
<body>

    <nav class="sticky-bar">
        <div class="nav-left">
            <div class="logo-container">
                <a href="admin.php">
                    <img src="../images/GTT-logo.png" alt="Golden Train Track Logo" class="nav-logo">
                </a>
            </div>
            
            <ul class="nav-links">
                <li class="nav-item"><a href="admin.php">Análise</a></li>
                <li class="nav-item tab-controle active"><a href="controle.php">Controle</a></li>
            </ul>
        </div>

        <div class="nav-right">
            <a href="../dashboard/Barra/usuario/usuario.php" class="user-btn">
                <span><?php echo htmlspecialchars($primeiro_nome); ?></span>
                <svg class="user-icon" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </a>
        </div>
    </nav>

    <main class="main-content">
        
        <h1 class="page-title">Gestão e Controle do Sistema</h1>

        <section class="control-grid">
            
            <a href="admin_trem/gestao_trem.php" class="control-card">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="6" width="20" height="10" rx="2"/>
                        <circle cx="7" cy="18" r="2"/>
                        <circle cx="17" cy="18" r="2"/>
                        <path d="M6 10h12"/>
                    </svg>
                </div>
                <h2>Gestão de Trens</h2>
                <p>Listar, buscar, cadastrar, editar e excluir trens com validação.</p>
            </a>

            <a href="admin_sensor/gestao_sensor.php" class="control-card">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                </div>
                <h2>Gestão de Sensores</h2>
                <p>Cadastrar sensores vinculados aos trens, listar, editar e excluir.</p>
            </a>

            <a href="admin_cliente/gestao_cliente.php" class="control-card">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h2>Gestão de Usuários</h2>
                <p>Gerenciamento de contas, perfis e permissões dos usuários.</p>
            </a>

        </section>

    </main>

    <script src="controle.js"></script>
</body>
</html>