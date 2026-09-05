<?php
    session_start();
    if((!isset($_SESSION['CPF']) == true) AND (!isset($_SESSION['senha']) == true))
        {
            unset($_SESSION['CPF']);
            unset($_SESSION['senha']);
            header("location: loginUser.html");
            exit();
        }
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
    <nav>
        <div class="img-logo">
            <img src="img/logo.png" alt="Logo Acesso+" class="logo-img">
        </div>

        <div class="links-nav">
            <a href="areaUsuario.html" class="link-ativo">Início</a>
            <a href="meuPlano.html">Meu plano</a>
            <a href="agendamentos.html">Agendamentos</a>
            <a href="redeCredenciada.html">Rede credenciada</a>
        </div>

        <div class="nav-usuario">
            <span class="nav-nome">Ana</span>
            <a href="loginUser.html" class="btn-nav">Sair</a>
        </div>
    </nav>

    <header class="area-header">
        <div class="header-content">
            <span class="eyebrow">Área do beneficiário</span>
            <h1>Olá, Ana Souza</h1>
            <p>Aqui está um resumo do seu plano e dos seus próximos atendimentos.</p>
        </div>
    </header>

    <main>
        <div class="painel">
            <!-- Dados fictícios para demonstração acadêmica do TCC. -->
            <article class="carteirinha">
                <div class="carteirinha-topo">
                    <span class="carteirinha-marca">Acesso+</span>
                    <span class="carteirinha-plano">Plano Plus</span>
                </div>
                <div class="carteirinha-info">
                    <p class="carteirinha-rotulo">Titular</p>
                    <h2 class="carteirinha-nome">Ana Souza</h2>
                    <div class="carteirinha-dados">
                        <div>
                            <p class="carteirinha-rotulo">CPF</p>
                            <p class="carteirinha-valor">***.456.789-**</p>
                        </div>
                        <div>
                            <p class="carteirinha-rotulo">Matrícula</p>
                            <p class="carteirinha-valor">000123456</p>
                        </div>
                        <div>
                            <p class="carteirinha-rotulo">Válida até</p>
                            <p class="carteirinha-valor">12/2027</p>
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
