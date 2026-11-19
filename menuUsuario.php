<?php 
// Descobre o nome do arquivo atual
$paginaAtual = basename($_SERVER['PHP_SELF']); 
?>

<!-- Importa o CSS específico do menu e acessibilidade -->
<link rel="stylesheet" href="css/menuUsuario.css">

<nav>
    <div class="img-logo">
        <img src="img/logo.png" alt="Logo Acesso+" class="logo-img">
    </div>

    <div class="links-nav">
        <a href="areaUsuario.php" class="<?php echo ($paginaAtual == 'areaUsuario.php') ? 'link-ativo' : ''; ?>">Início</a>
        <a href="meuPlano.php" class="<?php echo ($paginaAtual == 'meuPlano.php') ? 'link-ativo' : ''; ?>">Meu plano</a>
        <a href="financeiro.php" class="<?php echo ($paginaAtual == 'financeiro.php') ? 'link-ativo' : ''; ?>">Financeiro</a>
        <a href="agendamentos.php" class="<?php echo ($paginaAtual == 'agendamentos.php') ? 'link-ativo' : ''; ?>">Agendamentos</a>
        <a href="redeCredenciada.php" class="<?php echo ($paginaAtual == 'redeCredenciada.php') ? 'link-ativo' : ''; ?>">Rede credenciada</a>
    </div>

    <!-- Botões de Acessibilidade -->
    <div class="acessibilidade-botoes" aria-label="Controles de acessibilidade">
        <button type="button" id="btn-leitor-voz" onclick="ligarLeitorVoz()" title="Leitor de Tela" class="btn-acessibilidade">🔊 Ouvir</button>
        <button type="button" onclick="mudarFonte(1)" title="Aumentar Texto" class="btn-acessibilidade">A+</button>
        <button type="button" onclick="mudarFonte(-1)" title="Diminuir Texto" class="btn-acessibilidade">A-</button>
        <button type="button" onclick="alternarContraste()" title="Modo Escuro/Alto Contraste" class="btn-acessibilidade icone">◐</button>
    </div>

    <div class="nav-usuario">
        <span class="nav-nome"><?php echo htmlspecialchars($primeiroNome ?? 'Usuário'); ?></span>
        <a href="encerrarSessaoUser.php" class="btn-logout">Sair</a>
    </div>
</nav>

<!-- Widget oficial VLibras (Tradução em Libras - Gov.br) -->
<div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
</div>
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script>
    new window.VLibras.Widget('https://vlibras.gov.br/app');
</script>

<!-- Script de Acessibilidade Unificado -->
<script src="js/acessibilidade.js"></script>
