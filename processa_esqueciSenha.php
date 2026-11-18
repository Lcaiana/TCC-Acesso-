<?php
include "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    
    // Verifica se o email existe no banco de dados
    $verifica = $con->query("SELECT Id_User, Nome_User FROM Cadastro_Users WHERE Email_User = ?", [$email]);
    
    if ($verifica && $verifica->num_rows > 0) {
        $usuario = $verifica->fetch_assoc();
        $id = $usuario['Id_User'];
        $nome = $usuario['Nome_User'];
        
        // Gera um token aleatório seguro de 32 caracteres (hex)
        $token = bin2hex(random_bytes(16));
        // Define expiração para daqui a 1 hora
        $expiracao = date("Y-m-d H:i:s", strtotime('+1 hour'));
        
        // Atualiza o banco com o token
        $atualiza = "UPDATE Cadastro_Users SET Token_Senha = ?, Expiracao_Token = ? WHERE Id_User = ?";
        $con->query($atualiza, [$token, $expiracao, (int)$id]);
        
        // --- ENVIO DO EMAIL (Usando a função nativa mail do PHP) ---
        // ATENÇÃO: Na InfinityFree, a função mail() às vezes é bloqueada ou cai no SPAM.
        
        $assunto = "Recuperacao de Senha - Acesso+";
        // Pega a URL atual base (ex: http://seusite.epizy.com)
        $protocolo = (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || !empty($_SERVER['HTTPS'])) ? 'https' : 'http';
        $linkRecuperacao = $protocolo . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/redefinirSenha.php?token=" . $token;
        
        $mensagem = "Olá, " . $nome . "!\n\n";
        $mensagem .= "Você solicitou a redefinição da sua senha no Acesso+.\n";
        $mensagem .= "Clique no link abaixo para criar uma nova senha:\n";
        $mensagem .= $linkRecuperacao . "\n\n";
        $mensagem .= "Se você não solicitou isso, pode ignorar este email.\n";
        $mensagem .= "Este link expira em 1 hora.";
        
        $headers = "From: nao-responda@" . $_SERVER['HTTP_HOST'] . "\r\n";
        $headers .= "Reply-To: nao-responda@" . $_SERVER['HTTP_HOST'] . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Tenta enviar o email
        $emailEnviado = mail($email, $assunto, $mensagem, $headers);
        
        if ($emailEnviado) {
            echo "<script>alert('Um link de recuperação foi enviado para o seu e-mail! (Verifique a caixa de SPAM)'); window.location.href='loginUser.html';</script>";
        } else {
            // Falha silenciosa ou aviso para fins de TCC
            echo "<script>alert('O servidor da hospedagem bloqueou o envio do email. Como este é um TCC, use este link temporário para testar: " . $linkRecuperacao . "'); window.location.href='loginUser.html';</script>";
        }
    } else {
        // Por segurança, não confirmamos se o email existe ou não, apenas damos a mensagem genérica
        echo "<script>alert('Se o e-mail existir na nossa base, um link de recuperação foi enviado.'); window.location.href='loginUser.html';</script>";
    }
} else {
    header("location: esqueciSenha.html");
}
?>
