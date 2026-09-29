<?php
    session_start();
    if(!isset($_SESSION['CPF']) || !isset($_SESSION['nome'])) {
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
    <link rel="stylesheet" href="css/areaUsuario.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script> <!-- Deixa em português -->
</head>
<body>
    <nav>
        <div class="img-logo">
            <img src="img/logo.png" alt="Logo Acesso+" class="logo-img">
        </div>

        <div class="links-nav">
            <a href="areaUsuario.php">Início</a>
            <a href="meuPlano.php">Meu plano</a>
            <a href="agendamentos.php" class="link-ativo">Agendamentos</a>
            <a href="redeCredenciada.php">Rede credenciada</a>
        </div>

        <div class="nav-usuario">
            <span class="nav-nome"><?php echo htmlspecialchars($primeiroNome); ?></span>
            <a href="encerrarSessaoUser.php" class="btn-nav">Sair</a>
        </div>
    </nav>

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
                        WHERE c.Id_User = '$IdUsuario' 
                        ORDER BY c.Data_Consulta DESC";
$resultadoAgendamentos = $con->query($comandoAgendamentos);
?>

    <main>
        <div class="painel">
            <section class="agendamentos">
                <h2>Seus agendamentos</h2>

                <?php 
                if($resultadoAgendamentos && mysqli_num_rows($resultadoAgendamentos) > 0) {
                    while($consulta = $resultadoAgendamentos->fetch_assoc()) {
                        // Formatar data e hora
                        $dataFormatada = date("d/m/Y", strtotime($consulta['Data_Consulta']));
                        $horaFormatada = date("H:i", strtotime($consulta['Hora_Consulta']));
                        $status = strtolower($consulta['Status_Consulta']);
                ?>
                <div class="agendamento-item">
                    <img src="img/medico.png" alt="Ícone de consulta" class="agendamento-icone">
                    <div class="agendamento-info">
                        <h3><?php echo htmlspecialchars($consulta['Especialidade_Profissional']); ?> — Dr(a). <?php echo htmlspecialchars($consulta['Nome_Profissional']); ?></h3>
                        <p><?php echo $dataFormatada; ?> às <?php echo $horaFormatada; ?></p>
                    </div>
                    <span class="status <?php echo $status; ?>"><?php echo htmlspecialchars($consulta['Status_Consulta']); ?></span>
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
                        if($resultadoProfissionais && mysqli_num_rows($resultadoProfissionais) > 0) {
                            while($prof = $resultadoProfissionais->fetch_assoc()) {
                                echo "<option value='".$prof['Id_Profissional']."'>Dr(a). ".$prof['Nome_Profissional']." - ".$prof['Especialidade_Profissional']."</option>";
                            }
                        }
                        ?>
                    </select>

                    <label for="data">Data preferida</label>
                    <input type="text" id="data" name="data" placeholder="Selecione uma data" required>

                    <label for="hora">Horário preferido (Somente horas cheias)</label>
                    <input type="time" id="hora" name="hora" required step="3600">

                    <button type="submit">Confirmar Agendamento</button>
                </form>
            </section>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-block">
                <img src="img/logo.png" alt="Logo Acesso+" class="img-logo-footer">
                <p>Convênio médico voltado para pessoas com deficiência</p>
            </div>

            <div class="footer-block">
                <h4>Institucional</h4>
                <a href="">Sobre Nós</a>
                <br>
                <a href="">Sociedade</a>
                <br>
                <a href="">Fale Conosco</a>
            </div>

            <div class="footer-block">
                <h4>Ajuda</h4>
                <a href="">Dúvidas Frequentes</a>
                <br>
                <a href="">Política de Privacidade</a>
                <br>
                <a href="">Termos de Uso</a>
            </div>

            <div class="footer-siga">
                <h4>Siga-nos</h4>
                <div class="social-icons">
                    <a href="youtube.com"><img src="img/instagram.png" alt="Instagram" class="footer-img-siganos"></a>
                    <a href=""><img src="img/facebook.png" alt="Facebook" class="footer-img-siganos"></a>
                    <a href=""><img src="img/linkedin.png" alt="LinkedIn" class="footer-img-siganos"></a>
                </div>
            </div>
        </div>
    </footer>
    <script src= "js/agendamento.js" ></script>
</body>
</html>
