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
    <!-- Reaproveitamos o css do usuario para manter o padrão visual -->
    <link rel="stylesheet" href="css/areaUsuario.css">
    <style>
        .painel-profissional {
            padding: 40px 8%;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        .resumo-agenda {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        .resumo-agenda h2 {
            color: #0f172a;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .info-medico {
            background: #2668D7;
            color: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(38,104,215,0.2);
        }
        .info-medico h2 { margin-bottom: 10px; font-size: 1.8rem; }
        .info-medico p { margin-bottom: 5px; opacity: 0.9; }
        .btn-logout {
            color: #ef4444;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
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
            <a href="sair.php" class="btn-logout">Sair</a>
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
            <p>Em breve, aqui aparecerá a lista de pacientes agendados com você de forma dinâmica puxando da tabela Consultas!</p>
        </section>
    </main>
</body>
</html>
