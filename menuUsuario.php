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
        <a href="agendamentos.php" class="<?php echo ($paginaAtual == 'agendamentos.php') ? 'link-ativo' : ''; ?>">Agendamentos</a>
        <a href="redeCredenciada.php" class="<?php echo ($paginaAtual == 'redeCredenciada.php') ? 'link-ativo' : ''; ?>">Rede credenciada</a>
    </div>

    <!-- Botões de Acessibilidade -->
    <div class="acessibilidade-botoes">
        <button onclick="ligarLeitorVoz()" title="Leitor de Tela" class="btn-acessibilidade">🔊 Ouvir</button>
        <button onclick="mudarFonte(1)" title="Aumentar Texto" class="btn-acessibilidade">A+</button>
        <button onclick="mudarFonte(-1)" title="Diminuir Texto" class="btn-acessibilidade">A-</button>
        <button onclick="alternarContraste()" title="Modo Escuro/Alto Contraste" class="btn-acessibilidade icone">◐</button>
    </div>

    <div class="nav-usuario">
        <span class="nav-nome"><?php echo htmlspecialchars($primeiroNome ?? 'Usuário'); ?></span>
        <a href="encerrarSessaoUser.php" class="btn-nav">Sair</a>
    </div>
</nav>

<!-- Script de Acessibilidade -->
<script>
    // --- 1. TAMANHO DA FONTE ---
    // Tenta recuperar o tamanho salvo, se não houver, usa 100
    let tamanhoAtual = parseInt(localStorage.getItem('acesso_tamanhoFonte')) || 100;
    
    // Aplica o tamanho assim que a página carrega
    document.body.style.zoom = (tamanhoAtual / 100);

    function mudarFonte(acao) {
        tamanhoAtual += (acao * 10);
        if (tamanhoAtual > 150) tamanhoAtual = 150;
        if (tamanhoAtual < 70) tamanhoAtual = 90;
        
        document.body.style.zoom = (tamanhoAtual / 100);
        localStorage.setItem('acesso_tamanhoFonte', tamanhoAtual); // Salva no navegador
    }

    // --- 2. ALTO CONTRASTE ---
    // Verifica se já estava ativado na outra página
    if (localStorage.getItem('acesso_altoContraste') === 'true') {
        document.body.classList.add('alto-contraste');
    }

    function alternarContraste() {
        document.body.classList.toggle('alto-contraste');
        
        // Salva a preferência
        if (document.body.classList.contains('alto-contraste')) {
            localStorage.setItem('acesso_altoContraste', 'true');
        } else {
            localStorage.setItem('acesso_altoContraste', 'false');
        }
    }

    // --- 3. LEITOR INTELIGENTE ---
    // Verifica se já estava ligado
    let leitorLigado = localStorage.getItem('acesso_leitorLigado') === 'true';

    function ligarLeitorVoz() {
        leitorLigado = !leitorLigado; // Inverte o status
        localStorage.setItem('acesso_leitorLigado', leitorLigado); // Salva

        if (leitorLigado) {
            alert("Leitor Inteligente ativado! Ele continuará ativo nas outras páginas.");
        } else {
            window.speechSynthesis.cancel();
            alert("Leitor Inteligente desativado.");
        }
    }

    document.addEventListener('mouseover', function(evento) {
        if (!leitorLigado) return;
        
        let elemento = evento.target;
        const tagsParaLer = ['H1', 'H2', 'H3', 'P', 'A', 'BUTTON', 'SPAN', 'IMG', 'LABEL', 'INPUT', 'SELECT', 'TEXTAREA'];
        
        if (tagsParaLer.includes(elemento.tagName)) {
            let textoParaLer = "";
            
            if (elemento.tagName === 'INPUT' || elemento.tagName === 'TEXTAREA') {
                textoParaLer = elemento.value || elemento.placeholder || elemento.name;
            } 
            else if (elemento.tagName === 'SELECT') {
                textoParaLer = elemento.options[elemento.selectedIndex].text;
            } 
            else {
                textoParaLer = elemento.innerText || elemento.alt; 
            }
            
            if (textoParaLer && textoParaLer.trim() !== '') {
                window.speechSynthesis.cancel(); 
                let robo = new SpeechSynthesisUtterance(textoParaLer.trim());
                robo.lang = 'pt-BR';
                window.speechSynthesis.speak(robo);
            }
        }
    });
</script>
