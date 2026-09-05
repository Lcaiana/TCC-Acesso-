<?php
include "conexao.php";  

$nome =$_POST['nome'];
$email =$_POST['email'];
$telefone =$_POST['telefone'];
$CPF =$_POST['CPF'];
$idade =$_POST['idade'];
$CEP =$_POST['CEP'];
$data_nasc =$_POST['data_nasc'];
$genero =$_POST['genero'];
$est_civil =$_POST['est_civil'];
$senha =$_POST['senha'];
//Após testes passar a senha para hash para criptografar

$comando = "INSERT INTO Cadastro_Users(Nome_User, Dta_Nasc_User, Genero_User, CPF_User, CEP_User, Email_User, Senha_User, Num_Tel_User, Est_Civil_User) 
VALUES ('$nome','$data_nasc','$genero','$CPF','$CEP','$email','$senha','$telefone','$est_civil')";

$resulta = mysqli_query($con, $comando);

if ($resulta) {
    // Exibe um alerta e redireciona o usuário para a tela de login
    echo "<script>
            alert('Cadastro realizado com sucesso! Agora você já pode fazer o login.');
            window.location.href = 'login.html'; // Altere para a sua página de login
          </script>";
} else {
    // Se houver algum erro na gravação do banco
    echo "<script>
            alert('Erro ao realizar o cadastro. Tente novamente.');
            window.history.back();
          </script>";
}

$close = mysqli_close($con);
?>