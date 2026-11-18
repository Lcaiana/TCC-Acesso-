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
        $comandoAtualiza = "UPDATE Consultas SET Status_Consulta = 'Realizada', Observacoes_Medico = ? WHERE Id_Consulta = ? AND Id_Profissional = ?";
        $con->query($comandoAtualiza, [$observacoes, (int)$idConsulta, (int)$IdProfissional]);
        
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
    <?php include 'menuProfissional.php'; ?>

    <main class="painel-profissional painel-prontuario">
        <div class="prontuario-container">
            <?php
            if (isset($_GET['id_consulta'])) {
                $idConsultaSelecionada = $_GET['id_consulta'];
                // Busca a consulta selecionada (independente se está pendente ou realizada)
                $query = "SELECT c.Id_Consulta, c.Data_Consulta, c.Hora_Consulta, c.Status_Consulta, c.Observacoes_Medico, u.Nome_User, u.CPF_User 
                          FROM Consultas c 
                          INNER JOIN Cadastro_Users u ON c.Id_User = u.Id_User 
                          WHERE c.Id_Profissional = ? AND c.Id_Consulta = ?";
                
                $result = $con->query($query, [(int)$IdProfissional, (int)$idConsultaSelecionada]);
                
                if($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $dataFormatada = date("d/m/Y", strtotime($row['Data_Consulta']));
                    $horaFormatada = date("H:i", strtotime($row['Hora_Consulta']));
                    $isRealizada = (strtolower($row['Status_Consulta']) == 'realizada');
                    ?>
                    <h2><?php echo $isRealizada ? 'Prontuário Finalizado' : 'Preencher Prontuário'; ?></h2>
                    <p><a href="minhaAgenda.php" style="color: #007bff; text-decoration: none;">&larr; Voltar para a Agenda</a></p>
                    
                    <div style="border: 1px solid #eee; padding: 15px; margin-top: 15px; border-radius: 5px; background: <?php echo $isRealizada ? '#f8fafc' : '#fff'; ?>;">
                        <h3>Paciente: <?php echo htmlspecialchars($row['Nome_User']); ?></h3>
                        <p><strong>Data:</strong> <?php echo $dataFormatada; ?> às <?php echo $horaFormatada; ?> | <strong>CPF:</strong> <?php echo htmlspecialchars($row['CPF_User']); ?></p>
                        
                        <?php if ($isRealizada): ?>
                            <div class="form-prontuario">
                                <label>Anotações da Consulta (Prontuário):</label>
                                <textarea readonly style="background-color: #e2e8f0; color: #475569; border-color: #cbd5e1;"><?php echo htmlspecialchars($row['Observacoes_Medico']); ?></textarea>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="prontuario.php" class="form-prontuario">
                                <input type="hidden" name="id_consulta" value="<?php echo $row['Id_Consulta']; ?>">
                                <label for="obs_<?php echo $row['Id_Consulta']; ?>">Anotações da Consulta (Prontuário):</label>
                                <textarea id="obs_<?php echo $row['Id_Consulta']; ?>" name="observacoes" placeholder="Digite as observações, sintomas, receituário, etc..." required></textarea>
                                <br>
                                <button type="submit" class="btn-salvar">Salvar e Finalizar Consulta</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <?php
                } else {
                    echo "<p>Consulta não encontrada.</p>";
                    echo '<p><a href="minhaAgenda.php" style="color: #007bff; text-decoration: none;">&larr; Voltar</a></p>';
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
                          WHERE c.Id_Profissional = ? AND c.Status_Consulta = 'Pendente'
                          ORDER BY c.Data_Consulta ASC";
                
                $result = $con->query($query, [(int)$IdProfissional]);
                
                if($result && $result->num_rows > 0) {
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
