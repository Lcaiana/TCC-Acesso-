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
    <?php include 'menuUsuario.php'; ?>

    <header class="area-header">
        <div class="header-content">
            <span class="eyebrow">Rede credenciada</span>
            <h1>Locais e profissionais</h1>
            <p>Todos os locais credenciados possuem estrutura adaptada e atendimento acessível.</p>
        </div>
    </header>

    <main>
        <section class="acesso-rapido mt-0">
            <h2>Mapa da Rede Credenciada</h2>

            <!-- Div onde o Google Maps será renderizado -->
            <div id="map" class="map-container"></div>

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
                                    <h3 class="info-window-title">${medico.Nome_Profissional}</h3>
                                    <p class="info-window-text"><strong>Especialidade:</strong> ${medico.Especialidade_Profissional}</p>
                                    <p class="info-window-text"><strong>Endereço:</strong> ${medico.Rua_Profissional}, ${medico.Numero_Profissional}</p>
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

    <footer style="text-align: center; padding: 20px; margin-top: 40px; color: #64748b; font-size: 0.85rem; border-top: 1px solid #e2e8f0;">
        <p>© 2026 Acesso+ | Painel do Beneficiário</p>
    </footer>
</body>
</html>
