<?php
    session_start();

    include "conexao.php";

    if(isset($_POST['submit']) && !empty($_POST['CPF']) && !empty($_POST['senha']))
    {
        $CPF = $_POST['CPF'];
        $senha = $_POST['senha'];

        $comando = "SELECT * FROM Cadastro_Users WHERE CPF_User='$CPF' AND Senha_User='$senha'";

        $resultado = $con->query($comando);

        if(mysqli_num_rows($resultado) < 1)
            {
                unset($_SESSION['CPF']);
                unset($_SESSION['senha']);
                unset($_SESSION['nome']);
                echo "<script>
                    alert('Usuário não encontrado!');
                    window.location.href = 'loginUser.html';
                    </script>";
                    exit();
            }
        else
        {
            //Extrai os dados do usuário retornados pelo banco
            $usuario = $resultado->fetch_assoc();


            $_SESSION['CPF'] = $CPF;
            $_SESSION['senha'] = $senha;
            $_SESSION['nome'] = $usuario['Nome_User'];
            $_SESSION['id'] = $usuario['Id_User'];
            
            header("location: areaUsuario.php");
            exit();
        }
    }
    else
    {
        //Não deixa acessar a página via barra de pesquisa se não houver login
        header('location: loginUser.html');
        exit();
    }
?>