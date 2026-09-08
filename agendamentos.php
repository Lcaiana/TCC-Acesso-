<?php
    session_start();
    if(!isset($_SESSION['CPF']) || !isset($_SESSION['nome'])) {
        header("location: loginUser.html");
        exit();
    }
    $nomeCompleto = $_SESSION['nome'];
    $primeiroNome = explode(' ', trim($nomeCompleto))[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso+ | Agendamentos</title>
    <link rel="stylesheet" href="css/areaUsuario.css">
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

    <main>
        <div class="painel">
            <section class="agendamentos">
                <h2>Próximos agendamentos</h2>

                <!-- Dados fictícios para demonstração acadêmica do TCC. -->
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

            <section class="novo-agendamento">
                <h2>Agendar consulta</h2>

                <form action="agendamentos.html">
                    <label for="especialidade">Especialidade</label>
                    <select id="especialidade" name="especialidade" required>
                        <option value="" disabled selected hidden>Selecione a especialidade</option>
                        <option value="cardiologia">Cardiologia</option>
                        <option value="fonoaudiologia">Fonoaudiologia</option>
                        <option value="fisioterapia">Fisioterapia</option>
                        <option value="clinica">Clínica geral</option>
                    </select>

                    <label for="data">Data preferida</label>
                    <input type="date" id="data" name="data" required>

                    <label for="periodo">Período</label>
                    <select id="periodo" name="periodo" required>
                        <option value="" disabled selected hidden>Selecione o período</option>
                        <option value="manha">Manhã</option>
                        <option value="tarde">Tarde</option>
                    </select>

                    <button type="submit">Solicitar agendamento</button>
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
</body>
</html>
