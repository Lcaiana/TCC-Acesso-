<?php
// Desativa a exibição de erros no HTML para não quebrar o JSON de resposta
error_reporting(0);
header('Content-Type: application/json');

include 'conexao.php';

// Recebe os dados da requisição
$idProfissional = $_GET['medico_id'] ?? null;
$dataConsulta = $_GET['data'] ?? null;

if (!$idProfissional || !$dataConsulta) {
    echo json_encode(['erro' => 'Médico e Data são obrigatórios']);
    exit;
}

// 1. Definimos o horário de expediente padrão da clínica (de hora em hora, pulando o almoço das 12h)
$horariosExpediente = [
    '08:00', '09:00', '10:00', '11:00', 
    '13:00', '14:00', '15:00', '16:00', '17:00'
];

// 2. Buscamos no banco de dados quais horários já estão ocupados para esse médico nesse dia
// O status diferente de "Cancelado" garante que consultas canceladas liberam o horário
$comando = "SELECT Hora_Consulta FROM Consultas 
            WHERE Id_Profissional = ?
            AND Data_Consulta = ? 
            AND Status_Consulta NOT IN ('Cancelado', 'Cancelada')";

$resultado = $con->query($comando, [(int)$idProfissional, $dataConsulta]);

$horariosOcupados = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($linha = $resultado->fetch_assoc()) {
        // Pega apenas as horas e minutos (ex: '09:00') para comparar
        $horaBanco = substr($linha['Hora_Consulta'], 0, 5); 
        $horariosOcupados[] = $horaBanco;
    }
}

// 3. Remove os horários ocupados da lista de expediente
$horariosDisponiveis = [];
foreach ($horariosExpediente as $hora) {
    if (!in_array($hora, $horariosOcupados)) {
        $horariosDisponiveis[] = $hora;
    }
}

// 4. Retorna a lista de horários livres no formato JSON para o JavaScript ler
echo json_encode($horariosDisponiveis);
?>
