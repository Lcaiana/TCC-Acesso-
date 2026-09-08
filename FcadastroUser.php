<?php
include "conexao.php";  

$nome =$_POST['nome'];
$email =$_POST['email'];
$telefone =$_POST['telefone'];
$CPF =$_POST['CPF'];
$idade =$_POST['idade'];
$CEP =$_POST['CEP'];
$data_nasc =$_POST['data_nasc'];
$genero =$_POST['genero'];
$est_civil =$_POST['est_civil'];
$senha =$_POST['senha'];
//Após testes passar a senha para hash para criptografar

// Verifica se o CPF ou E-mail já estão cadastrados
$comandoVerifica = "SELECT * FROM Cadastro_Users WHERE CPF_User='$CPF' OR Email_User='$email'";
$resultadoVerifica = $con->query($comandoVerifica);

if(mysqli_num_rows($resultadoVerifica) > 0)
{
    echo "<script>
            alert('Erro: Este CPF ou E-mail já está cadastrado em nosso sistema!');
            window.history.back();
          </script>";
    exit();
}

// API Gratuita OpenStreetMap (Nominatim) para pegar Lat e Lng pelo CEP sem precisar de cartão
$urlDaApi = "https://nominatim.openstreetmap.org/search?postalcode={$CEP}&country=brazil&format=json";

$latitude = "";
$longitude = "";

// Usando cURL com User-Agent (obrigatório para essa API gratuita)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $urlDaApi);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, "SistemaMedicoTCC/1.0"); // A API exige identificação
$resposta = curl_exec($ch);
curl_close($ch);

$dadosGeolocalizacao = json_decode($resposta, true);

// A API retorna um array. Se achar o CEP, o índice [0] tem lat e lon
if (!empty($dadosGeolocalizacao)) {
    $latitude = $dadosGeolocalizacao[0]['lat'];
    $longitude = $dadosGeolocalizacao[0]['lon'];
}

// -----------------------------------------------------------
// NOVO: Consumindo a API do ViaCEP para descobrir a Rua e Bairro
// -----------------------------------------------------------
$rua = "";
$bairro = "";
$cidade = "";
$numero = $_POST['numero'] ?? 'S/N';

$urlViaCEP = "https://viacep.com.br/ws/{$CEP}/json/";
$chCep = curl_init();
curl_setopt($chCep, CURLOPT_URL, $urlViaCEP);
curl_setopt($chCep, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chCep, CURLOPT_SSL_VERIFYPEER, false);
$respostaCep = curl_exec($chCep);
curl_close($chCep);

$dadosEndereco = json_decode($respostaCep, true);

if (!isset($dadosEndereco['erro']) && !empty($dadosEndereco)) {
    $rua = $dadosEndereco['logradouro'];
    $bairro = $dadosEndereco['bairro'];
    $cidade = $dadosEndereco['localidade'];
}

$comando = "INSERT INTO Cadastro_Users (Nome_User, Dta_Nasc_User, Genero_User, CPF_User, CEP_User, Numero_User, Rua_User, Bairro_User, Cidade_User, Latitude_User, Longitude_User, Email_User, Senha_User, Num_Tel_User, Est_Civil_User) VALUES ('$nome', '$data_nasc', '$genero', '$CPF', '$CEP', '$numero', '$rua', '$bairro', '$cidade', '$latitude', '$longitude', '$email', '$senha', '$telefone', '$est_civil')";

$resulta = mysqli_query($con, $comando);

if ($resulta) {
    // Exibe um alerta e redireciona o usuário para a tela de login
    echo "<script>
            alert('Cadastro realizado com sucesso! Agora você já pode fazer o login.');
            window.location.href = 'loginUser.html'; // Altere para a sua página de login
          </script>";
} else {
    // Se houver algum erro na gravação do banco
    echo "<script>
            alert('Erro ao realizar o cadastro. Tente novamente.');
            window.history.back();
          </script>";
}

$close = mysqli_close($con);
?>