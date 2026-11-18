<?php
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<nav>
    <div class="img-logo">
        <img src="img/logo.png" alt="Logo Acesso+" class="logo-img">
    </div>

    <div class="links-nav">
        <a href="areaProfissional.php" class="<?php echo ($paginaAtual == 'areaProfissional.php') ? 'link-ativo' : ''; ?>">Portal do Médico</a>
        <a href="minhaAgenda.php" class="<?php echo ($paginaAtual == 'minhaAgenda.php') ? 'link-ativo' : ''; ?>">Minha Agenda</a>
        <a href="gerenciarAgenda.php" class="<?php echo ($paginaAtual == 'gerenciarAgenda.php') ? 'link-ativo' : ''; ?>">Gerenciar Agenda</a>
        <a href="prontuario.php" class="<?php echo ($paginaAtual == 'prontuario.php') ? 'link-ativo' : ''; ?>">Prontuários</a>
    </div>

    <div class="nav-usuario">
        <span class="nav-nome">Dr(a). <?php echo htmlspecialchars($primeiroNome ?? 'Profissional'); ?></span>
        <a href="encerrarSessaoProfissional.php" class="btn-logout">Sair</a>
    </div>
</nav>
