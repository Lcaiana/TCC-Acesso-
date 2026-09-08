<?php
    session_start();
    if(!isset($_SESSION['CPF']) || !isset($_SESSION['nome'])) {
        header("location: loginUser.html");
        exit();
    }
    include "conexao.php";

    $nomeCompleto = $_SESSION['nome'];
    $primeiroNome = explode(' ', trim($nomeCompleto))[0];

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

	$cpfFormatado = substr($CPF, 0, 3) . "." . substr($CPF, 3, 3) . "." . substr($CPF, 6, 3) . "-" . substr($CPF, 9, 2);
	$matricula = sprintf('%09d', $IdUsuario);
    $validade = date("m/Y", strtotime("+1 year"));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso+ | Meu Plano</title>
    <link rel="stylesheet" href="css/areaUsuario.css">
</head>
<body>
    <nav>
        <div class="img-logo">
            <img src="img/logo.png" alt="Logo Acesso+" class="logo-img">
        </div>

        <div class="links-nav">
            <a href="areaUsuario.php">Início</a>
            <a href="meuPlano.php" class="link-ativo">Meu plano</a>
            <a href="agendamentos.php">Agendamentos</a>
            <a href="redeCredenciada.php">Rede credenciada</a>
        </div>

        <div class="nav-usuario">
            <span class="nav-nome"><?php echo htmlspecialchars($primeiroNome); ?></span>
            <a href="encerrarSessaoUser.php" class="btn-nav">Sair</a>
        </div>
    </nav>

    <header class="area-header">
        <div class="header-content">
            <span class="eyebrow">Meu plano</span>
            <h1>Plano Plus</h1>
            <p>Confira os detalhes da sua cobertura e os dados da sua carteirinha.</p>
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
                    <div class="carteirinha-dados" style="flex-wrap: wrap; row-gap: 16px;">
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

            <section class="detalhes-plano">
                <h2>Detalhes da assinatura</h2>

                <div class="detalhe-linha">
                    <span>Mensalidade</span>
                    <strong>R$ 149,90/mês</strong>
                </div>
                <div class="detalhe-linha">
                    <span>Forma de pagamento</span>
                    <strong>Boleto — dia 10</strong>
                </div>
                <div class="detalhe-linha">
                    <span>Início da vigência</span>
                    <strong>10/03/2024</strong>
                </div>
                <div class="detalhe-linha">
                    <span>Situação</span>
                    <span class="status confirmado">Ativo</span>
                </div>
            </section>
        </div>

        <section class="acesso-rapido">
            <h2>Cobertura do plano</h2>

            <div class="cards">
                <div class="card-content">
                    <img src="img/cuidados-de-saude.png" alt="Ícone de cuidados de saúde" class="card-img">
                    <div class="card-body">
                        <h3 class="card-title">Consultas com especialistas</h3>
                        <p class="card-description">Cardiologia, neurologia, ortopedia e mais, sem fila de espera longa.</p>
                    </div>
                </div>

                <div class="card-content">
                    <img src="img/pcd.png" alt="Ícone de terapias" class="card-img">
                    <div class="card-body">
                        <h3 class="card-title">Terapias e reabilitação</h3>
                        <p class="card-description">Fisioterapia, fonoaudiologia e terapia ocupacional com cobertura integral.</p>
                    </div>
                </div>

                <div class="card-content">
                    <img src="img/hospital.png" alt="Ícone de hospital" class="card-img">
                    <div class="card-body">
                        <h3 class="card-title">Rede acessível</h3>
                        <p class="card-description">Locais credenciados com estrutura adaptada e atendimento humanizado.</p>
                    </div>
                </div>
            </div>
        </section>
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
</body>
</html>
