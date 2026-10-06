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
    <title>Painel do Administrador - GTT</title>
    <link rel="stylesheet" href="admin.css">
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
                <li class="nav-item active"><a href="admin.php">Análise</a></li>
                <li class="nav-item tab-controle"><a href="controle.php">Controle</a></li>
            </ul>
        </div>

        <div class="nav-right">
            <a href="admin_usuario.php" class="user-btn">
                <span><?php echo htmlspecialchars($primeiro_nome); ?></span>
                <svg class="user-icon" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </a>
        </div>
    </nav>

    <main class="main-content">
        
        <h1 class="page-title">Análise recente</h1>

        <section class="stats-banner">
            <div class="stat-box">
                <svg class="stat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
                <span class="stat-num">65 Km/H</span>
                <span class="stat-desc">Velocidade Média</span>
            </div>

            <div class="stat-box">
                <svg class="stat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="6" width="20" height="10" rx="2"/>
                    <circle cx="7" cy="18" r="2"/>
                    <circle cx="17" cy="18" r="2"/>
                </svg>
                <span class="stat-num">1.567</span>
                <span class="stat-desc">Trens ativos</span>
            </div>

            <div class="stat-box">
                <svg class="stat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
                <span class="stat-num">142</span>
                <span class="stat-desc">Trens em manutenção</span>
            </div>

            <div class="stat-box">
                <svg class="stat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 11V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v0a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v0a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v5a7 7 0 0 0 7 7h1a6 6 0 0 0 6-6V11z"/>
                </svg>
                <span class="stat-num">1.391</span>
                <span class="stat-desc">Trens parados</span>
            </div>

            <div class="stat-box">
                <svg class="stat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span class="stat-num">100</span>
                <span class="stat-desc">Quant. passageiros</span>
            </div>
        </section>

        <section class="dashboard-grid">
            
            <div class="card-panel">
                <h2 class="panel-header">Alertas Ativos</h2>
                
                <div class="alert-summary">
                    <span class="alert-big-number">5</span>
                    <svg class="warning-icon" viewBox="0 0 24 24" fill="none" stroke="red" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <div class="alert-labels">
                        <span>3 Críticos</span>
                        <span>2 Avisos</span>
                    </div>
                </div>

                <ul class="alert-list">
                    <li>#T56: Falha na tração</li>
                    <li>#T237: Superaquecimento</li>
                    <li>#T12: Falha na tração</li>
                    <li>#T502: Bloqueio de via</li>
                </ul>
            </div>

            <div class="card-panel">
                <h2 class="panel-header">Sensores críticos</h2>
                
                <div class="alert-summary">
                    <span class="alert-big-number">4</span>
                    <span class="critical-text">Leituras críticas</span>
                </div>

                <ul class="alert-list">
                    <li>#S42: Temperatura 112°C</li>
                    <li>#S34: Vibração pesada</li>
                    <li>#S142: Temperatura -23°C</li>
                    <li>#S84: Vibração pesada</li>
                </ul>
            </div>

        </section>

    </main>

    <script src="admin.js"></script>
</body>
</html>