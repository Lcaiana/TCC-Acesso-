<?php
    session_start();

    include "conexao.php";

    if(isset($_POST['submit']) && !empty($_POST['CPF']) && !empty($_POST['senha']))
    {
        $CPF = $_POST['CPF'];
        $senha = $_POST['senha'];

        $comando = "SELECT * FROM Cadastro_Profissionais WHERE CPF_Profissional='$CPF' AND Senha_Profissional='$senha'";

        $resultado = $con->query($comando);

        if(mysqli_num_rows($resultado) < 1)
            {
                unset($_SESSION['CPF']);
                unset($_SESSION['senha']);
                unset($_SESSION['nome']);
                unset($_SESSION['tipo_conta']);
                echo "<script>
                    alert('Usuário não encontrado!');
                    window.location.href = 'loginProfissional.html';
                    </script>";
                    exit();
            }
        else
        {
            //Extrai os dados do usuário retornados pelo banco
            $usuario = $resultado->fetch_assoc();


            $_SESSION['CPF'] = $CPF;
            $_SESSION['senha'] = $senha;
            $_SESSION['nome'] = $usuario['Nome_Profissional'];
            $_SESSION['id'] = $usuario['Id_Profissional'];
            $_SESSION['tipo_conta'] = 'profissional'; // Para evitar conflitos com login de paciente
            
            header("location: areaProfissional.php");
            exit();
        }
    }
    else
    {
        //Não deixa acessar a página via barra de pesquisa se não houver login
        header('location: loginProfissional.html');
        exit();
    }
?>