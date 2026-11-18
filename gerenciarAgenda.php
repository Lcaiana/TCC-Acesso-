<?php
    session_start();
    if(!isset($_SESSION['CPF']) || !isset($_SESSION['nome']) || !isset($_SESSION['tipo_conta']) || $_SESSION['tipo_conta'] != 'profissional') {
        header("location: loginProfissional.html");
        exit();
    }

    $nomeCompleto = $_SESSION['nome']; 
    $primeiroNome = explode(' ', trim($nomeCompleto))[0];
    include "conexao.php";

    $IdProfissional = $_SESSION['id'] ?? 0;

    // ----- LÓGICA DE PROCESSAMENTO DO FORMULÁRIO -----
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['acao'] ?? '';

        if ($acao == 'bloquear_horario') {
            $data = $_POST['data_bloqueio'];
            $hora = $_POST['hora_bloqueio'];
            
            // Insere uma consulta falsa (Id_User = NULL) com status Bloqueado
            // NOTA: Para isso funcionar, o banco de dados deve permitir Id_User = NULL.
            $sql = "INSERT INTO Consultas (Id_User, Id_Profissional, Data_Consulta, Hora_Consulta, Status_Consulta, Observacoes_Medico) 
                    VALUES (NULL, ?, ?, ?, 'Bloqueado', 'Bloqueio manual de agenda')";
            $con->query($sql, [(int)$IdProfissional, $data, $hora]);
            
            echo "<script>alert('Horário bloqueado com sucesso!'); window.location.href='gerenciarAgenda.php';</script>";
            exit();

        } elseif ($acao == 'bloquear_dia') {
            $dataDia = $_POST['data_dia'];
            $horariosExpediente = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
            
            // Cria um bloqueio para cada hora do dia
            foreach($horariosExpediente as $h) {
                // Checa se já existe consulta nesse horário
                $check = "SELECT Id_Consulta FROM Consultas WHERE Id_Profissional = ? AND Data_Consulta = ? AND Hora_Consulta = ?";
                $resCheck = $con->query($check, [(int)$IdProfissional, $dataDia, "$h:00"]);
                if($resCheck && $resCheck->num_rows == 0) {
                    // Só bloqueia se estiver vazio (não cancela consultas de pacientes já marcados)
                    $sql = "INSERT INTO Consultas (Id_User, Id_Profissional, Data_Consulta, Hora_Consulta, Status_Consulta, Observacoes_Medico) 
                            VALUES (NULL, ?, ?, ?, 'Bloqueado', 'Férias / Dia Inteiro')";
                    $con->query($sql, [(int)$IdProfissional, $dataDia, "$h:00"]);
                }
            }
            echo "<script>alert('Dia bloqueado com sucesso! (Horários já agendados por pacientes foram mantidos)'); window.location.href='gerenciarAgenda.php';</script>";
            exit();

        } elseif ($acao == 'desbloquear') {
            $idConsulta = $_POST['id_bloqueio'];
            // Deleta a "consulta falsa"
            $sql = "DELETE FROM Consultas WHERE Id_Consulta = ? AND Id_Profissional = ? AND Status_Consulta = 'Bloqueado'";
            $con->query($sql, [(int)$idConsulta, (int)$IdProfissional]);
            echo "<script>alert('Horário liberado!'); window.location.href='gerenciarAgenda.php';</script>";
            exit();
        }
    }

    // ----- BUSCA OS BLOQUEIOS ATUAIS PARA MOSTRAR NA TELA -----
    $queryBloqueios = "SELECT Id_Consulta, Data_Consulta, Hora_Consulta, Observacoes_Medico 
                       FROM Consultas 
                       WHERE Id_Profissional = ? AND Status_Consulta = 'Bloqueado'
                       ORDER BY Data_Consulta ASC, Hora_Consulta ASC";
    $resultBloqueios = $con->query($queryBloqueios, [(int)$IdProfissional]);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso+ | Gerenciar Agenda</title>
    <link rel="stylesheet" href="css/areaUsuario.css">
    <link rel="stylesheet" href="css/areaProfissional.css">
    <link rel="stylesheet" href="css/gerenciarAgenda.css">
    <!-- Usando o Flatpickr para os calendários -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <?php include 'menuProfissional.php'; ?>

    <header class="area-header">
        <div class="header-content">
            <span class="eyebrow">Controle de Horários</span>
            <h1>Gerenciar Agenda</h1>
            <p>Bloqueie horários específicos ou dias inteiros (férias/recessos).</p>
        </div>
    </header>

    <main class="gerenciar-container">
        <!-- Lado Esquerdo: Formulários -->
        <div class="coluna-forms">
            
            <div class="box-bloqueio">
                <h3>Bloquear Horário Específico</h3>
                <form method="POST">
                    <input type="hidden" name="acao" value="bloquear_horario">
                    <div class="form-group">
                        <label>Data</label>
                        <input type="text" class="calendario" name="data_bloqueio" required placeholder="Selecione o dia">
                    </div>
                    <div class="form-group">
                        <label>Horário</label>
                        <select name="hora_bloqueio" required>
                            <option value="" disabled selected hidden>Selecione a hora</option>
                            <option value="08:00">08:00</option><option value="09:00">09:00</option>
                            <option value="10:00">10:00</option><option value="11:00">11:00</option>
                            <option value="13:00">13:00</option><option value="14:00">14:00</option>
                            <option value="15:00">15:00</option><option value="16:00">16:00</option><option value="17:00">17:00</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-bloquear">Confirmar Bloqueio</button>
                </form>
            </div>

            <div class="box-bloqueio">
                <h3>Bloquear Dia Inteiro (Férias)</h3>
                <form method="POST">
                    <input type="hidden" name="acao" value="bloquear_dia">
                    <div class="form-group">
                        <label>Data para bloquear</label>
                        <input type="text" class="calendario" name="data_dia" required placeholder="Selecione o dia">
                    </div>
                    <p class="aviso-bloqueio">Ao bloquear um dia inteiro, todos os horários livres desse dia sumirão para os pacientes. Pacientes que já agendaram antes do bloqueio serão mantidos.</p>
                    <button type="submit" class="btn-bloquear btn-bloquear-dia">Bloquear Dia Completo</button>
                </form>
            </div>

        </div>

        <!-- Lado Direito: Lista de Bloqueios -->
        <div class="coluna-lista">
            <h3 class="titulo-lista">Horários Bloqueados</h3>
            
            <?php if($resultBloqueios && $resultBloqueios->num_rows > 0): ?>
                <div class="lista-bloqueios">
                    <?php while($b = $resultBloqueios->fetch_assoc()): ?>
                        <div class="item-bloqueio">
                            <div class="info-bloqueio">
                                <span class="data"><?php echo date("d/m/Y", strtotime($b['Data_Consulta'])); ?> às <?php echo date("H:i", strtotime($b['Hora_Consulta'])); ?></span>
                                <span class="motivo"><?php echo htmlspecialchars($b['Observacoes_Medico']); ?></span>
                            </div>
                            <form method="POST" class="form-remover">
                                <input type="hidden" name="acao" value="desbloquear">
                                <input type="hidden" name="id_bloqueio" value="<?php echo $b['Id_Consulta']; ?>">
                                <button type="submit" class="btn-remover">Liberar</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="empty-bloqueios">Sua agenda está totalmente livre. Nenhum bloqueio ativo.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
    <script>
        // Ativa o calendário nos campos
        flatpickr(".calendario", {
            locale: "pt",
            dateFormat: "Y-m-d",
            minDate: "today",
            disable: [
                function(date) {
                    return (date.getDay() === 0 || date.getDay() === 6);
                }
            ]
        });
    </script>
</body>
</html>
