// ============================================================
// SUÍTE DE ACESSIBILIDADE ACESSO+ (Sincronizada com localStorage)
// ============================================================

// --- 1. TAMANHO DA FONTE ---
let tamanhoAtual = parseInt(localStorage.getItem('acesso_tamanhoFonte')) || 100;
document.body.style.zoom = (tamanhoAtual / 100);

function mudarFonte(acao) {
    tamanhoAtual += (acao * 10);
    if (tamanhoAtual > 150) tamanhoAtual = 150;
    if (tamanhoAtual < 70) tamanhoAtual = 70;

    document.body.style.zoom = (tamanhoAtual / 100);
    localStorage.setItem('acesso_tamanhoFonte', tamanhoAtual);
}

// --- 2. ALTO CONTRASTE ---
if (localStorage.getItem('acesso_altoContraste') === 'true') {
    document.body.classList.add('alto-contraste');
}

function alternarContraste() {
    document.body.classList.toggle('alto-contraste');
    const ativo = document.body.classList.contains('alto-contraste');
    localStorage.setItem('acesso_altoContraste', ativo ? 'true' : 'false');
}

// --- 3. LEITOR INTELIGENTE (Web Speech API) ---
let leitorLigado = localStorage.getItem('acesso_leitorLigado') === 'true';

function atualizarEstadoBotaoLeitor() {
    const btnLeitor = document.getElementById('btn-leitor-voz');
    if (btnLeitor) {
        btnLeitor.classList.toggle('ativo', leitorLigado);
        btnLeitor.setAttribute('aria-pressed', leitorLigado ? 'true' : 'false');
    }
}

document.addEventListener('DOMContentLoaded', atualizarEstadoBotaoLeitor);

function ligarLeitorVoz() {
    leitorLigado = !leitorLigado;
    localStorage.setItem('acesso_leitorLigado', leitorLigado);
    atualizarEstadoBotaoLeitor();

    if (leitorLigado) {
        alert("Leitor Inteligente ativado! Passe o mouse sobre os textos e botões para ouvir. Ele continuará ativo nas outras páginas.");
    } else {
        window.speechSynthesis.cancel();
        alert("Leitor Inteligente desativado.");
    }
}

document.addEventListener('mouseover', function(evento) {
    if (!leitorLigado) return;

    const elemento = evento.target;
    const tagsParaLer = ['H1', 'H2', 'H3', 'H4', 'P', 'A', 'BUTTON', 'SPAN', 'IMG', 'LABEL', 'LI', 'INPUT', 'SELECT', 'TEXTAREA'];

    if (tagsParaLer.includes(elemento.tagName)) {
        let textoParaLer = "";

        if (elemento.tagName === 'INPUT' || elemento.tagName === 'TEXTAREA') {
            textoParaLer = elemento.value || elemento.placeholder || elemento.name;
        } else if (elemento.tagName === 'SELECT' && elemento.options.length > 0) {
            textoParaLer = elemento.options[elemento.selectedIndex].text;
        } else {
            textoParaLer = elemento.innerText || elemento.alt;
        }

        if (textoParaLer && textoParaLer.trim() !== '') {
            window.speechSynthesis.cancel();
            const robo = new SpeechSynthesisUtterance(textoParaLer.trim());
            robo.lang = 'pt-BR';
            window.speechSynthesis.speak(robo);
        }
    }
});
