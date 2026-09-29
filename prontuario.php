<?php
    session_start();
    if(!isset($_SESSION['CPF']) || !isset($_SESSION['nome']) || !isset($_SESSION['tipo_conta']) || $_SESSION['tipo_conta'] != 'profissional')
    {
        header("location: loginProfissional.html");
        exit();
    }

    $nomeCompleto = $_SESSION['nome'];
    $primeiroNome = explode(' ', trim($nomeCompleto))[0];
    include "conexao.php";

	$IdProfissional = $_SESSION['id'] ?? 0;
    
    // Verifica se enviou o formulário de prontuário
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_consulta'])) {
        $idConsulta = $_POST['id_consulta'];
        $observacoes = $_POST['observacoes'];
        
        // Atualiza a consulta para Realizada e salva as observações (Certifique-se de criar a coluna Observacoes_Medico na tabela Consultas)
        $comandoAtualiza = "UPDATE Consultas SET Status_Consulta = 'Realizada', Observacoes_Medico = '$observacoes' WHERE Id_Consulta = '$idConsulta' AND Id_Profissional = '$IdProfissional'";
        $con->query($comandoAtualiza);
        
        echo "<script>alert('Prontuário salvo e consulta finalizada com sucesso!'); window.location.href='prontuario.php';</script>";
        exit();
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso+ | Prontuários</title>
    <link rel="stylesheet" href="css/areaUsuario.css">
    <link rel="stylesheet" href="css/areaProfissional.css">
    <link rel="stylesheet" href="css/prontuario.css">
</head>
<body>
    <nav>
        <div class="img-logo">
            <img src="img/logo.png" alt="Logo Acesso+" class="logo-img">
        </div>
        <div class="links-nav">
            <a href="areaProfissional.php">Portal do Médico</a>
            <a href="areaProfissional.php">Minha Agenda</a>
            <a href="prontuario.php" class="link-ativo">Prontuários</a>
        </div>
        <div class="nav-usuario">
            <span class="nav-nome">Dr(a). <?php echo htmlspecialchars($primeiroNome); ?></span>
            <a href="encerrarSessaoProfissional.php" class="btn-logout">Sair</a>
        </div>
    </nav>

    <main class="painel-profissional painel-prontuario">
        <div class="prontuario-container">
            <?php
            if (isset($_GET['id_consulta'])) {
                $idConsultaSelecionada = $_GET['id_consulta'];
                // Busca apenas a consulta selecionada
                $query = "SELECT c.Id_Consulta, c.Data_Consulta, c.Hora_Consulta, u.Nome_User, u.CPF_User 
                          FROM Consultas c 
                          INNER JOIN Cadastro_Users u ON c.Id_User = u.Id_User 
                          WHERE c.Id_Profissional = '$IdProfissional' AND c.Id_Consulta = '$idConsultaSelecionada' AND c.Status_Consulta = 'Pendente'";
                
                $result = $con->query($query);
                
                if($result && mysqli_num_rows($result) > 0) {
                    $row = $result->fetch_assoc();
                    $dataFormatada = date("d/m/Y", strtotime($row['Data_Consulta']));
                    $horaFormatada = date("H:i", strtotime($row['Hora_Consulta']));
                    ?>
                    <h2>Preencher Prontuário</h2>
                    <p><a href="prontuario.php" style="color: #007bff; text-decoration: none;">&larr; Voltar para a lista de consultas</a></p>
                    
                    <div style="border: 1px solid #eee; padding: 15px; margin-top: 15px; border-radius: 5px;">
                        <h3>Paciente: <?php echo htmlspecialchars($row['Nome_User']); ?></h3>
                        <p><strong>Data:</strong> <?php echo $dataFormatada; ?> às <?php echo $horaFormatada; ?> | <strong>CPF:</strong> <?php echo $row['CPF_User']; ?></p>
                        
                        <form method="POST" action="prontuario.php" class="form-prontuario">
                            <input type="hidden" name="id_consulta" value="<?php echo $row['Id_Consulta']; ?>">
                            <label for="obs_<?php echo $row['Id_Consulta']; ?>">Anotações da Consulta (Prontuário):</label>
                            <textarea id="obs_<?php echo $row['Id_Consulta']; ?>" name="observacoes" placeholder="Digite as observações, sintomas, receituário, etc..." required></textarea>
                            <br>
                            <button type="submit" class="btn-salvar">Salvar e Finalizar Consulta</button>
                        </form>
                    </div>
                    <?php
                } else {
                    echo "<p>Consulta não encontrada ou já finalizada.</p>";
                    echo '<p><a href="prontuario.php" style="color: #007bff; text-decoration: none;">&larr; Voltar</a></p>';
                }
            } else {
                // Se não houver id_consulta na URL, lista todas as consultas pendentes
            ?>
                <h2>Consultas Pendentes</h2>
                <p>Selecione uma consulta para preencher as anotações e marcá-la como realizada.</p>
                
                <?php
                $query = "SELECT c.Id_Consulta, c.Data_Consulta, c.Hora_Consulta, u.Nome_User, u.CPF_User 
                          FROM Consultas c 
                          INNER JOIN Cadastro_Users u ON c.Id_User = u.Id_User 
                          WHERE c.Id_Profissional = '$IdProfissional' AND c.Status_Consulta = 'Pendente'
                          ORDER BY c.Data_Consulta ASC";
                
                $result = $con->query($query);
                
                if($result && mysqli_num_rows($result) > 0) {
                    echo '<div class="cards-container">';
                    while($row = $result->fetch_assoc()) {
                        $dataFormatada = date("d/m/Y", strtotime($row['Data_Consulta']));
                        $horaFormatada = date("H:i", strtotime($row['Hora_Consulta']));
                        
                        echo '<div class="card-paciente">';
                        echo '<div>';
                        echo '<h3>' . htmlspecialchars($row['Nome_User']) . '</h3>';
                        echo '<p><strong>Data:</strong> ' . $dataFormatada . '</p>';
                        echo '<p><strong>Hora:</strong> ' . $horaFormatada . '</p>';
                        echo '</div>';
                        echo '<a href="prontuario.php?id_consulta=' . $row['Id_Consulta'] . '" class="btn-atender">Atender</a>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo "<p>Nenhuma consulta pendente de prontuário.</p>";
                }
            }
            ?>
        </div>
    </main>
</body>
</html>
