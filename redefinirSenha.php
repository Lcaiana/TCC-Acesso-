<?php
include "conexao.php";

$tokenValido = false;
$idUsuario = 0;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    $agora = date("Y-m-d H:i:s");
    
    // Verifica se o token existe e não está expirado
    $query = "SELECT Id_User FROM Cadastro_Users WHERE Token_Senha = ? AND Expiracao_Token >= ?";
    $result = $con->query($query, [$token, $agora]);
    
    if ($result && $result->num_rows > 0) {
        $tokenValido = true;
        $row = $result->fetch_assoc();
        $idUsuario = $row['Id_User'];
    }
}
?>
<!DOCTYPE html>
<html lang="PT-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha | Acesso+</title>
    <link rel="stylesheet" href="css/loginUser.css">
    <link rel="stylesheet" href="css/acessibilidade.css">
</head>
<body>
    <div class="acessibilidade-botoes acessibilidade-flutuante" aria-label="Controles de acessibilidade">
        <button type="button" id="btn-leitor-voz" onclick="ligarLeitorVoz()" title="Leitor de Tela" class="btn-acessibilidade">🔊 Ouvir</button>
        <button type="button" onclick="mudarFonte(1)" title="Aumentar Texto" class="btn-acessibilidade">A+</button>
        <button type="button" onclick="mudarFonte(-1)" title="Diminuir Texto" class="btn-acessibilidade">A-</button>
        <button type="button" onclick="alternarContraste()" title="Modo Escuro/Alto Contraste" class="btn-acessibilidade icone">◐</button>
    </div>
    <section class="containerPai">
        <a href="loginUser.html" class="logo-card"><img src="img/logo.png" alt="Logo Acesso+"></a>
        <div class="card loginActive">
            <div class="esquerda">
                <div class="formLogin" style="width: 100%;">
                    <?php if ($tokenValido): ?>
                        <h2>Criar Nova Senha</h2>
                        <form action="processa_redefinirSenha.php" method="post">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="password" name="senha" placeholder="Digite a nova senha" required minlength="6">
                            <input type="password" name="confirma_senha" placeholder="Confirme a nova senha" required minlength="6">
                            <button type="submit" name="submit">Alterar Senha</button>
                        </form>
                    <?php else: ?>
                        <h2>Link Inválido</h2>
                        <p style="text-align: center; color: #ef4444;">Este link de recuperação é inválido ou já expirou.</p>
                        <a href="esqueciSenha.html" class="esqueci-senha" style="display: block; text-align: center; margin-top: 20px;">Solicitar novo link</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="direita" style="background: url('img/background2.png') no-repeat center center / cover;">
            </div>
        </div>
    </section>

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

    <script src="js/acessibilidade.js"></script>
</body>
</html>
