<?php
session_start();

// Verifica se é um paciente logado
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_conta']) || $_SESSION['tipo_conta'] != 'paciente') {
    header("location: loginUser.html");
    exit();
}

include "conexao.php";

$idUsuario = $_SESSION['id'];
// O Mercado Pago devolve payment_id (ou collection_id) na URL de retorno
$paymentId = $_GET['payment_id'] ?? $_GET['collection_id'] ?? '';

if (empty($paymentId) || $paymentId === 'null') {
    header("location: financeiro.php?msg=erro");
    exit();
}

// SEGURANÇA: Nunca confiamos apenas no ?status=approved da URL!
// Consultamos diretamente a API oficial do Mercado Pago pelo payment_id para confirmar a verdade.
$paymentIdLimpo = urlencode($paymentId);
$ch = curl_init("https://api.mercadopago.com/v1/payments/{$paymentIdLimpo}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $mp_access_token,
    "Content-Type: application/json"
]);

$resposta = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode < 200 || $httpCode >= 300) {
    header("location: financeiro.php?msg=erro");
    exit();
}

$pagamento = json_decode($resposta, true);
$statusMP = $pagamento['status'] ?? '';
$idFatura = isset($pagamento['external_reference']) ? (int)$pagamento['external_reference'] : 0;
$paymentIdSeguro = $con->real_escape_string($paymentId);

if ($statusMP === 'approved' && $idFatura > 0) {
    // Atualiza a fatura para Pago e salva o comprovante oficial do Mercado Pago
    $con->query("UPDATE Faturas 
                 SET Status_Fatura = 'Pago', 
                     Id_Pagamento_MP = '$paymentIdSeguro', 
                     Data_Pagamento = NOW() 
                 WHERE Id_Fatura = '$idFatura' 
                 AND Id_User = '$idUsuario'");

    // Ativa o plano do paciente para liberar os agendamentos imediatamente
    $con->query("UPDATE Cadastro_Users 
                 SET Status_Pagamento = 'Ativo' 
                 WHERE Id_User = '$idUsuario'");

    header("location: financeiro.php?msg=aprovado");
    exit();
} elseif ($statusMP === 'pending' || $statusMP === 'in_process') {
    if ($idFatura > 0) {
        $con->query("UPDATE Faturas 
                     SET Id_Pagamento_MP = '$paymentIdSeguro' 
                     WHERE Id_Fatura = '$idFatura' 
                     AND Id_User = '$idUsuario'");
    }
    header("location: financeiro.php?msg=pendente");
    exit();
} else {
    header("location: financeiro.php?msg=falha");
    exit();
}
?>
