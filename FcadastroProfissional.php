<?php
include "conexao.php";

$nome = $_POST['nome'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$registro = $_POST['registro'];
$uf_registro = $_POST['uf_registro'];
$especialidade = $_POST['especialidade'];
$cep = $_POST['CEP'];
$cpf = $_POST['CPF'];
$data_nasc = $_POST['data_nasc'];
$genero = $_POST['genero'];
$senha = $_POST['senha'];

$comandoVerifica = "SELECT * FROM Cadastro_Profissionais WHERE CPF_Profissional='$CPF' OR Email_Profissional='$email' OR Registro_Profissional ='$registro'";
$resultadoVerifica = $con->query($comandoVerifica);

if(mysqli_num_rows($resultadoVerifica) > 0)
{
    echo "<script>
            alert('Erro: Este CPF, E-mail ou Registro já está cadastrado em nosso sistema!');
            window.history.back();
          </script>";
    exit();
}
// API Gratuita OpenStreetMap (Nominatim) para pegar Lat e Lng
$urlDaApi = "https://nominatim.openstreetmap.org/search?postalcode={$cep}&country=brazil&format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $urlDaApi);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, "SistemaMedicoTCC/1.0");
$resposta = curl_exec($ch);
curl_close($ch);

$dadosGeolocalizacao = json_decode($resposta, true);

$latitude = "";
$longitude = "";

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

$urlViaCEP = "https://viacep.com.br/ws/{$cep}/json/";
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

// 3. Salvando tudo no Banco de Dados
$comando = "INSERT INTO Cadastro_Profissionais 
            (Nome_Profissional, Email_Profissional, CPF_Profissional, Registro_Profissional, UF_Registro_Profissional, CEP_Profissional, Numero_Profissional, Rua_Profissional, Bairro_Profissional, Cidade_Profissional, Latitude_Profissional, Longitude_Profissional, Especialidade_Profissional, Dta_Nasc_Profissional, Genero_Profissional, Num_Tel_Profissional, Senha_Profissional) 
            VALUES 
            ('$nome', '$email', '$cpf', '$registro', '$uf_registro', '$cep', '$numero', '$rua', '$bairro', '$cidade', '$latitude', '$longitude', '$especialidade', '$data_nasc', '$genero', '$telefone', '$senha')";

if ($con->query($comando)) {
    echo "<script>
            alert('Profissional cadastrado com sucesso! Agora você já pode fazer login');
            window.location.href = 'loginProfissional.html';
          </script>";
} else {
    echo "<script>
            alert('Erro ao cadastrar. Verifique se o CPF, E-mail ou CRM já existem.');
            window.history.back();
          </script>";
}

$close = mysqli_close($con);
?>
