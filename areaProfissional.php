<?php
    session_start();
    // Verifica se está logado e se é realmente uma conta de profissional
    if(!isset($_SESSION['CPF']) || !isset($_SESSION['nome']) || !isset($_SESSION['tipo_conta']) || $_SESSION['tipo_conta'] != 'profissional')
        {
            unset($_SESSION['CPF']);
            unset($_SESSION['senha']);
            unset($_SESSION['nome']);
            unset($_SESSION['tipo_conta']);
            header("location: loginProfissional.html");
            exit();
        }

    $nomeCompleto = $_SESSION['nome']; //Resgata o nome salvo na session
    $primeiroNome = explode(' ', trim($nomeCompleto))[0];
    include "conexao.php";

	$IdProfissional = $_SESSION['id'] ?? 0;
	
    $comando = "SELECT * FROM Cadastro_Profissionais WHERE Id_Profissional = '$IdProfissional'";
    $resultado = $con->query($comando);
    
    if($resultado && mysqli_num_rows($resultado) > 0) {
        $dadosProfissional = $resultado->fetch_assoc();
        $registro = $dadosProfissional['Registro_Profissional'];
        $uf = $dadosProfissional['UF_Registro_Profissional'];
        $especialidade = $dadosProfissional['Especialidade_Profissional'];
        $cidade = $dadosProfissional['Cidade_Profissional'];
    } else {
        $registro = "000000";
        $uf = "SP";
        $especialidade = "Médico Clínico";
        $cidade = "São Paulo";
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso+ | Portal do Profissional</title>
    <!-- Reaproveitamos o css do usuario para manter o padrão visual da nav -->
    <link rel="stylesheet" href="css/areaUsuario.css">
    <!-- Novo CSS focado apenas no painel do médico -->
    <link rel="stylesheet" href="css/areaProfissional.css">
</head>
<body>
    <nav>
        <div class="img-logo">
            <img src="img/logo.png" alt="Logo Acesso+" class="logo-img">
        </div>

        <div class="links-nav">
            <a href="areaProfissional.php" class="link-ativo">Portal do Médico</a>
            <a href="#">Minha Agenda</a>
            <a href="#">Prontuários</a>
        </div>

        <div class="nav-usuario">
            <span class="nav-nome">Dr(a). <?php echo htmlspecialchars($primeiroNome); ?></span>
            <a href="encerrarSessaoProfissional.php" class="btn-logout">Sair</a>
        </div>
    </nav>

    <header class="header-boas-vindas">
        <h1>Bem-vindo(a) ao portal, <span>Dr(a). <?php echo htmlspecialchars($primeiroNome); ?></span></h1>
        <p>Acompanhe suas consultas e gerencie sua agenda com facilidade.</p>
    </header>

    <main class="painel-profissional">
        <aside class="info-medico">
            <h2>Seu Perfil</h2>
            <p><strong>Especialidade:</strong> <?php echo $especialidade; ?></p>
            <p><strong>Registro:</strong> <?php echo $registro; ?> / <?php echo $uf; ?></p>
            <p><strong>Atuação:</strong> <?php echo $cidade; ?></p>
        </aside>

        <section class="resumo-agenda">
            <h2>Próximas Consultas</h2>
            
            <div class="grid-consultas">
                <?php
                // Busca os agendamentos no banco
                $queryAgenda = "SELECT c.Id_Consulta, c.Data_Consulta, c.Hora_Consulta, c.Status_Consulta, 
                                       u.Nome_User, u.Num_Tel_User 
                                FROM Consultas c 
                                INNER JOIN Cadastro_Users u ON c.Id_User = u.Id_User 
                                WHERE c.Id_Profissional = '$IdProfissional' 
                                ORDER BY c.Data_Consulta ASC, c.Hora_Consulta ASC";
                
                $resultAgenda = $con->query($queryAgenda);

                if($resultAgenda && mysqli_num_rows($resultAgenda) > 0) {
                    while($consulta = $resultAgenda->fetch_assoc()) {
                        $dataFormatada = date("d/m/Y", strtotime($consulta['Data_Consulta']));
                        $horaFormatada = date("H:i", strtotime($consulta['Hora_Consulta']));
                        
                        $statusClass = (strtolower($consulta['Status_Consulta']) == 'pendente') ? 'pendente' : 'confirmado';

                        echo "<div class='card-consulta'>";
                        echo "    <div class='dados-paciente'>";
                        echo "        <h3>" . htmlspecialchars($consulta['Nome_User']) . "</h3>";
                        echo "        <p><strong>Tel:</strong> " . htmlspecialchars($consulta['Num_Tel_User']) . "</p>";
                        echo "        <span class='status-badge {$statusClass}'>" . htmlspecialchars($consulta['Status_Consulta']) . "</span>";
                        echo "    </div>";
                        echo "    <div class='hora-consulta'>";
                        echo "        <span class='data'>" . $dataFormatada . "</span>";
                        echo "        <span class='hora'>" . $horaFormatada . "</span>";
                        echo "    </div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>Você não tem nenhuma consulta agendada no momento.</p>";
                }
                ?>
            </div>
        </section>
    </main>
</body>
</html>
