<?php
include "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'] ?? '';
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];
    
    if ($senha !== $confirma_senha) {
        echo "<script>alert('As senhas não coincidem. Tente novamente.'); window.history.back();</script>";
        exit();
    }
    
    // NOTA: Como você pediu anteriormente para tirar a criptografia (password_hash) e salvar a senha pura
    // no FcadastroUser.php por ser um TCC, manteremos o padrão aqui.
    // Em um sistema real, aqui você usaria: $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Atualiza a senha e 'apaga' o token para que o link não possa ser usado de novo.
    // O token é conferido de novo aqui: só troca a senha de quem recebeu o link válido.
    $sql = "UPDATE Cadastro_Users SET Senha_User = ?, Token_Senha = NULL, Expiracao_Token = NULL
            WHERE Token_Senha = ? AND Expiracao_Token >= ?";
    $atualizado = ($token !== '') ? $con->query($sql, [$senha, $token, date("Y-m-d H:i:s")]) : false;

    if ($atualizado && $atualizado->num_rows > 0) {
        echo "<script>alert('Senha atualizada com sucesso! Você já pode fazer login.'); window.location.href='loginUser.html';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar a senha. Tente novamente mais tarde.'); window.location.href='loginUser.html';</script>";
    }
} else {
    header("location: loginUser.html");
}
?>
