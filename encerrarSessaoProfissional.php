<?php
    session_start();
    unset($_SESSION['CPF']);
    unset($_SESSION['senha']);
    unset($_SESSION['nome']);
    unset($_SESSION['tipo_conta']);
    unset($_SESSION['id']);
    session_destroy();
    header("location: loginProfissional.html");
    exit();
?>
