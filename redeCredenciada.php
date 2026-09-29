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
    <title>Acesso+ | Rede Credenciada</title>
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
            <a href="agendamentos.php">Agendamentos</a>
            <a href="redeCredenciada.php" class="link-ativo">Rede credenciada</a>
        </div>

        <div class="nav-usuario">
            <span class="nav-nome"><?php echo htmlspecialchars($primeiroNome); ?></span>
            <a href="encerrarSessaoUser.php" class="btn-nav">Sair</a>
        </div>
    </nav>

    <header class="area-header">
        <div class="header-content">
            <span class="eyebrow">Rede credenciada</span>
            <h1>Locais e profissionais</h1>
            <p>Todos os locais credenciados possuem estrutura adaptada e atendimento acessível.</p>
        </div>
    </header>

    <main>
        <section class="acesso-rapido" style="margin-top: 0;">
            <h2>Mapa da Rede Credenciada</h2>

            <!-- Div onde o Google Maps será renderizado -->
            <div id="map" style="width: 100%; height: 500px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>

            <?php
            // Busca todos os profissionais cadastrados que possuem Latitude e Longitude
            include "conexao.php";
            $queryMedicos = "SELECT Nome_Profissional, Especialidade_Profissional, Rua_Profissional, Numero_Profissional, Latitude_Profissional, Longitude_Profissional FROM Cadastro_Profissionais WHERE Latitude_Profissional IS NOT NULL AND Longitude_Profissional IS NOT NULL";
            $resultadoMedicos = $con->query($queryMedicos);
            
            $medicosArray = [];
            if($resultadoMedicos && mysqli_num_rows($resultadoMedicos) > 0) {
                while($row = $resultadoMedicos->fetch_assoc()) {
                    $medicosArray[] = $row;
                }
            }
            ?>

            <script>
                // Passa os dados do PHP (Banco de Dados) para o JavaScript
                const locaisMedicos = <?php echo json_encode($medicosArray); ?>;

                function initMap() {
                    // Posição central padrão (ex: Centro do Brasil ou São Paulo)
                    // Se houver médicos, centraliza no primeiro. Se não, usa um padrão.
                    let centroMap = { lat: -23.5505, lng: -46.6333 }; // SP padrão
                    
                    if(locaisMedicos.length > 0) {
                        centroMap = { 
                            lat: parseFloat(locaisMedicos[0].Latitude_Profissional), 
                            lng: parseFloat(locaisMedicos[0].Longitude_Profissional) 
                        };
                    }

                    // Cria o mapa no HTML
                    const map = new google.maps.Map(document.getElementById("map"), {
                        zoom: 12,
                        center: centroMap,
                        mapTypeId: "roadmap"
                    });

                    // Cria o InfoWindow (o balãozinho que abre ao clicar no pino)
                    const infoWindow = new google.maps.InfoWindow();

                    // Faz um laço de repetição para colocar um pino para cada médico
                    locaisMedicos.forEach((medico) => {
                        const posicao = {
                            lat: parseFloat(medico.Latitude_Profissional),
                            lng: parseFloat(medico.Longitude_Profissional)
                        };

                        const marker = new google.maps.Marker({
                            position: posicao,
                            map: map,
                            title: medico.Nome_Profissional,
                            animation: google.maps.Animation.DROP
                        });

                        // Quando o usuário clicar no pino, mostra as informações
                        marker.addListener("click", () => {
                            const conteudoBalao = `
                                <div>
                                    <h3 style="color:#333; margin-bottom:5px;">${medico.Nome_Profissional}</h3>
                                    <p style="margin:0;"><strong>Especialidade:</strong> ${medico.Especialidade_Profissional}</p>
                                    <p style="margin:0;"><strong>Endereço:</strong> ${medico.Rua_Profissional}, ${medico.Numero_Profissional}</p>
                                </div>
                            `;
                            infoWindow.setContent(conteudoBalao);
                            infoWindow.open(map, marker);
                        });
                    });
                }
            </script>
            <!-- Carrega a API do Google Maps usando a chave que está no conexao.php -->
            <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_maps_api_key; ?>&callback=initMap"></script>
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
