<?php
    session_start();
    if(!isset($_SESSION['CPF']) || !isset($_SESSION['nome']) || !isset($_SESSION['tipo_conta']) || $_SESSION['tipo_conta'] != 'paciente') {
        header("location: loginUser.html");
        exit();
    }
    include "conexao.php"; // NOVO: Conexão com o banco

    $nomeCompleto = $_SESSION['nome'];
    $primeiroNome = explode(' ', trim($nomeCompleto))[0];
    $IdUsuario = $_SESSION['id']; // NOVO: Pega o ID do usuário da sessão
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso+ | Agendamentos</title>
    <link rel="stylesheet" href="css/areaUsuario.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script> <!-- Deixa em português -->
</head>
<body>
    <?php include 'menuUsuario.php'; ?>

    <header class="area-header">
        <div class="header-content">
            <span class="eyebrow">Agendamentos</span>
            <h1>Seus atendimentos</h1>
            <p>Acompanhe as consultas agendadas e solicite novos horários.</p>
        </div>
    </header>

<?php
// Buscando os médicos para o formulário
$comandoProfissionais = "SELECT Id_Profissional, Nome_Profissional, Especialidade_Profissional FROM Cadastro_Profissionais";
$resultadoProfissionais = $con->query($comandoProfissionais);

// Buscando os agendamentos REAIS do paciente
$comandoAgendamentos = "SELECT c.*, p.Nome_Profissional, p.Especialidade_Profissional 
                        FROM Consultas c 
                        INNER JOIN Cadastro_Profissionais p ON c.Id_Profissional = p.Id_Profissional 
                        WHERE c.Id_User = ?
                        ORDER BY c.Data_Consulta DESC";
$resultadoAgendamentos = $con->query($comandoAgendamentos, [(int)$IdUsuario]);
?>

    <main>
        <div class="painel">
            <section class="agendamentos">
                <h2>Seus agendamentos</h2>

                <?php 
                if($resultadoAgendamentos && $resultadoAgendamentos->num_rows > 0) {
                    while($consulta = $resultadoAgendamentos->fetch_assoc()) {
                        // Formatar data e hora
                        $dataFormatada = date("d/m/Y", strtotime($consulta['Data_Consulta']));
                        $horaFormatada = date("H:i", strtotime($consulta['Hora_Consulta']));
                        
                        $statusReal = $consulta['Status_Consulta'];
                        $statusClass = strtolower($statusReal);
                        
                        // Lógica de atraso (Se está pendente mas a data já passou)
                        $hoje = date("Y-m-d");
                        $agora = date("H:i:s");
                        
                        if ($statusClass == 'pendente') {
                            if ($consulta['Data_Consulta'] < $hoje || ($consulta['Data_Consulta'] == $hoje && $consulta['Hora_Consulta'] < $agora)) {
                                $statusReal = "Não Realizada";
                                $statusClass = "atrasada";
                            }
                        }
                ?>
                <div class="agendamento-item">
                    <img src="img/medico.png" alt="Ícone de consulta" class="agendamento-icone">
                    <div class="agendamento-info">
                        <h3><?php echo htmlspecialchars($consulta['Especialidade_Profissional']); ?> — Dr(a). <?php echo htmlspecialchars($consulta['Nome_Profissional']); ?></h3>
                        <p><?php echo $dataFormatada; ?> às <?php echo $horaFormatada; ?></p>
                    </div>
                    <span class="status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusReal); ?></span>
                </div>
                <?php 
                    }
                } else {
                    echo "<p>Você ainda não possui consultas agendadas.</p>";
                }
                ?>
            </section>

            <section class="novo-agendamento">
                <h2>Agendar consulta</h2>

                <form action="FfazerAgendamento.php" method="POST">
                    <label for="id_profissional">Profissional e Especialidade</label>
                    <select id="id_profissional" name="id_profissional" required>
                        <option value="" disabled selected hidden>Selecione um profissional</option>
                        <?php 
                        if($resultadoProfissionais && $resultadoProfissionais->num_rows > 0) {
                            while($prof = $resultadoProfissionais->fetch_assoc()) {
                                echo "<option value='".(int)$prof['Id_Profissional']."'>Dr(a). ".htmlspecialchars($prof['Nome_Profissional'])." - ".htmlspecialchars($prof['Especialidade_Profissional'])."</option>";
                            }
                        }
                        ?>
                    </select>

                    <label for="data">Data preferida</label>
                    <input type="text" id="data" name="data" placeholder="Selecione uma data" required>

                    <label for="hora">Horário da consulta</label>
                    <select id="hora" name="hora" required disabled>
                        <option value="" disabled selected hidden>Selecione o médico e a data primeiro</option>
                    </select>

                    <button type="submit">Confirmar Agendamento</button>
                </form>
            </section>
        </div>
    </main>

    <footer style="text-align: center; padding: 20px; margin-top: 40px; color: #64748b; font-size: 0.85rem; border-top: 1px solid #e2e8f0;">
        <p>© 2026 Acesso+ | Painel do Beneficiário</p>
    </footer>
    <script src= "js/agendamento.js" ></script>
</body>
</html>
