<?php
    session_start();

    // Bloqueio de segurança
    if(!isset($_SESSION['id']) || !isset($_SESSION['tipo_conta']) || $_SESSION['tipo_conta'] != 'paciente') {
        echo "<script>
                alert('Acesso negado. Faça login como paciente para agendar consultas.');
                window.location.href = 'loginUser.html';
              </script>";
        exit();
    }

    include "conexao.php";

    // Pega os dados do formulário
    $id_user = $_SESSION['id'];
    $id_profissional = $_POST['id_profissional'];
    $data_consulta = $_POST['data'];
    $hora_consulta = $_POST['hora'];
    $status = 'Pendente'; // A clínica/médico confirma depois

    // -------------------------------------------------------------------------
    // NOVO: BLOQUEIO FINANCEIRO - Verifica se o usuário pagou o plano
    // -------------------------------------------------------------------------
    $queryStatus = "SELECT Status_Pagamento FROM Cadastro_Users WHERE Id_User = '$id_user'";
    $resultadoStatus = $con->query($queryStatus);
    
    if ($resultadoStatus && mysqli_num_rows($resultadoStatus) > 0) {
        $usuario = $resultadoStatus->fetch_assoc();
        if ($usuario['Status_Pagamento'] !== 'Ativo') {
            // BLOQUEIA O AGENDAMENTO!
            echo "<script>
                    alert('AGENDAMENTO BLOQUEADO!\\n\\nIdentificamos que a mensalidade do seu plano está Pendente.\\nPor favor, regularize o pagamento para agendar consultas.');
                    window.location.href = 'agendamentos.php';
                  </script>";
            exit(); // Para o script na hora!
        }
    }

    // -------------------------------------------------------------------------
    // NOVO: FORÇA A HORA CHEIA E IMPEDE CONFLITO DE AGENDA
    // -------------------------------------------------------------------------
    // Força os minutos e segundos para '00' (Ex: 14:30 vira 14:00:00)
    $hora_formatada = date('H:00:00', strtotime($hora_consulta));

    // Verifica se já existe uma consulta marcada com esse médico, neste mesmo dia e nesta mesma hora
    $queryConflito = "SELECT Id_Consulta FROM Consultas 
                      WHERE Id_Profissional = '$id_profissional' 
                      AND Data_Consulta = '$data_consulta' 
                      AND Hora_Consulta = '$hora_formatada' 
                      AND Status_Consulta != 'Cancelada'";
                      
    $resultadoConflito = $con->query($queryConflito);
    
    if($resultadoConflito && mysqli_num_rows($resultadoConflito) > 0) {
        // Já tem alguém agendado nesse horário
        echo "<script>
                alert('HORÁRIO INDISPONÍVEL!\\n\\nEste médico já possui um agendamento nesta data e horário.\\nPor favor, escolha um horário diferente (As consultas duram 1 hora).');
                window.location.href = 'agendamentos.php';
              </script>";
        exit();
    }

    // Preparando o comando para salvar no Banco de Dados
    $comando = "INSERT INTO Consultas (Id_User, Id_Profissional, Data_Consulta, Hora_Consulta, Status_Consulta) 
                VALUES ('$id_user', '$id_profissional', '$data_consulta', '$hora_formatada', '$status')";

    if($con->query($comando)){
        echo "<script>
                alert('Consulta agendada com sucesso! O médico confirmará em breve.');
                window.location.href = 'agendamentos.php';
              </script>";
    } else {
        echo "<script>
                alert('Ocorreu um erro ao agendar. Tente novamente.');
                window.location.href = 'agendamentos.php';
              </script>";
    }
?>
