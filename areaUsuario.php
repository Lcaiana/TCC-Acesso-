<?php
    session_start();
    if(!isset($_SESSION['CPF']) || !isset($_SESSION['nome']))
        {
            unset($_SESSION['CPF']);
            unset($_SESSION['senha']);
            unset($_SESSION['nome']);
            header("location: loginUser.html");
            exit();
        }

    $nomeCompleto = $_SESSION['nome']; //Resgata o nome salvo na session no momento do login
    $primeiroNome = explode(' ', trim($nomeCompleto))[0]; //pega apenas o primeiro nome da pessoa
    include "conexao.php";

	$IdUsuario = $_SESSION['id'] ?? 0;
	
    $comando = "SELECT * FROM Cadastro_Users WHERE Id_User = '$IdUsuario'";
    $resultado = $con->query($comando);
    
    if($resultado && mysqli_num_rows($resultado) > 0) {
        $dadosUsuario = $resultado->fetch_assoc();
        $CPF = $dadosUsuario['CPF_User'];
        $dataNasc = $dadosUsuario['Dta_Nasc_User'];
        $dataNascFormatada = date("d/m/Y", strtotime($dataNasc));
        $plano = "Plano Plus"; 
    } else {
        $CPF = $_SESSION['CPF'] ?? "00000000000";
        $dataNascFormatada = "--/--/----";
        $plano = "Plano Plus";
    }

	$cpfFormatado = "***." . substr($CPF, 3, 3) . "." . substr($CPF, 6, 3) . "-**";
	$matricula = sprintf('%09d', $IdUsuario);
    $validade = date("m/Y", strtotime("+1 year"));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso+ | Área do Beneficiário</title>
    <link rel="stylesheet" href="css/areaUsuario.css">
</head>
<body>
    <?php include 'menuUsuario.php'; ?>

    <header class="area-header">
        <div class="header-content">
            <span class="eyebrow">Área do beneficiário</span>
            <h1>Olá, <?php echo htmlspecialchars($nomeCompleto); ?> </h1>
            <p>Aqui está um resumo do seu plano e dos seus próximos atendimentos.</p>
        </div>
    </header>

    <main>
        <div class="painel">
            <!-- Dados fictícios para demonstração acadêmica do TCC. -->
            <article class="carteirinha">
                <div class="carteirinha-topo">
                    <span class="carteirinha-marca">Acesso+</span>
                    <span class="carteirinha-plano"><?php echo $plano; ?></span>
                </div>
                <div class="carteirinha-info">
                    <p class="carteirinha-rotulo">Titular</p>
                    <h2 class="carteirinha-nome"><?php echo htmlspecialchars($nomeCompleto); ?></h2>
                    <div class="carteirinha-dados carteirinha-dados-wrap">
                        <div>
                            <p class="carteirinha-rotulo">CPF</p>
                            <p class="carteirinha-valor"><?php echo $cpfFormatado; ?></p>
                        </div>
                        <div>
                            <p class="carteirinha-rotulo">Matrícula</p>
                            <p class="carteirinha-valor"><?php echo $matricula; ?></p>
                        </div>
                        <div>
                            <p class="carteirinha-rotulo">Nascimento</p>
                            <p class="carteirinha-valor"><?php echo $dataNascFormatada; ?></p>
                        </div>
                        <div>
                            <p class="carteirinha-rotulo">Validade</p>
                            <p class="carteirinha-valor"><?php echo $validade; ?></p>
                        </div>
                    </div>
                </div>
            </article>

            <section class="agendamentos">
                <h2>Próximos agendamentos</h2>

                <div class="agendamento-item">
                    <img src="img/medico.png" alt="Ícone de consulta" class="agendamento-icone">
                    <div class="agendamento-info">
                        <h3>Cardiologia — Dr. Carlos Lima</h3>
                        <p>15/09/2026 às 09:00</p>
                    </div>
                    <span class="status confirmado">Confirmado</span>
                </div>

                <div class="agendamento-item">
                    <img src="img/pcd.png" alt="Ícone de fisioterapia" class="agendamento-icone">
                    <div class="agendamento-info">
                        <h3>Fisioterapia — Dra. Marina Reis</h3>
                        <p>22/09/2026 às 14:30</p>
                    </div>
                    <span class="status confirmado">Confirmado</span>
                </div>

                <div class="agendamento-item">
                    <img src="img/cuidados-de-saude.png" alt="Ícone de consulta clínica" class="agendamento-icone">
                    <div class="agendamento-info">
                        <h3>Consulta clínica — Dr. Pedro Alves</h3>
                        <p>30/09/2026 às 11:00</p>
                    </div>
                    <span class="status pendente">Pendente</span>
                </div>
            </section>
        </div>

        <section class="acesso-rapido">
            <h2>Acesso rápido</h2>

            <div class="cards">
                <div class="card-content">
                    <img src="img/hospital.png" alt="Ícone de hospital" class="card-img">
                    <div class="card-body">
                        <h3 class="card-title">Rede credenciada</h3>
                        <p class="card-description">Encontre locais acessíveis e qualificados perto de você.</p>
                    </div>
                </div>

                <div class="card-content">
                    <img src="img/medico.png" alt="Ícone de profissional de saúde" class="card-img">
                    <div class="card-body">
                        <h3 class="card-title">Equipe especializada</h3>
                        <p class="card-description">Profissionais preparados para o seu cuidado.</p>
                    </div>
                </div>

                <div class="card-content">
                    <img src="img/cuidados-de-saude.png" alt="Ícone de suporte" class="card-img">
                    <div class="card-body">
                        <h3 class="card-title">Suporte prioritário</h3>
                        <p class="card-description">Atendimento por WhatsApp sempre que precisar.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer style="text-align: center; padding: 20px; margin-top: 40px; color: #64748b; font-size: 0.85rem; border-top: 1px solid #e2e8f0;">
        <p>© 2026 Acesso+ | Painel do Beneficiário</p>
    </footer>
</body>
</html>

