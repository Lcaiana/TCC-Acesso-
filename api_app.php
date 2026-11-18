<?php
/**
 * API REST JSON para integração entre o aplicativo Android Acesso+ e o site (mesmo banco no Supabase).
 *
 * Este arquivo é publicado junto com o site (pasta site/, hospedada no Railway).
 * A constante BASE_URL em app/src/main/java/com/example/acessomais/ApiClient.java deve apontar para
 * https://SEU-DOMINIO.up.railway.app/api_app.php
 */

error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/conexao.php';

if (!isset($con) || !$con) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Falha ao conectar ao banco de dados.']);
    exit;
}

function responder($dados, $statusHttp = 200) {
    http_response_code($statusHttp);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

function lerEntradaJson() {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            return $json;
        }
    }
    return $_POST;
}

function apenasDigitos($texto) {
    return preg_replace('/\D/', '', (string)$texto);
}

function montarUsuarioJson($row) {
    $idUser = (int)$row['Id_User'];
    $idPlano = isset($row['Id_Plano']) && $row['Id_Plano'] !== null ? (int)$row['Id_Plano'] : 2;
    $nomePlano = !empty($row['Nome_Plano']) ? $row['Nome_Plano'] : 'Plus';
    $precoPlano = isset($row['Preco']) && $row['Preco'] !== null ? (float)$row['Preco'] : 149.90;
    $statusPagamento = !empty($row['Status_Pagamento']) ? $row['Status_Pagamento'] : 'Pendente';

    $matricula = sprintf('%09d', $idUser);
    $carteirinha = '4582 ' . substr($matricula, 0, 4) . ' ' . substr($matricula, 4, 4) . ' ' . sprintf('%04d', $idUser);

    return [
        'id' => $idUser,
        'nome' => $row['Nome_User'] ?? '',
        'email' => $row['Email_User'] ?? '',
        'cpf' => $row['CPF_User'] ?? '',
        'telefone' => $row['Num_Tel_User'] ?? '',
        'data_nascimento' => $row['Dta_Nasc_User'] ?? '',
        'cidade' => $row['Cidade_User'] ?? '',
        'numero_carteirinha' => $carteirinha,
        'matricula' => $matricula,
        'id_plano' => $idPlano,
        'plano' => $nomePlano,
        'preco_plano' => $precoPlano,
        'status_pagamento' => $statusPagamento,
        'validade' => date('m/Y', strtotime('+1 year')),
        'categoria' => 'PCD'
    ];
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$body = lerEntradaJson();
if (empty($action) && isset($body['action'])) {
    $action = $body['action'];
}

switch ($action) {

    // =========================================================================
    // 1. STATUS / HEALTHCHECK
    // =========================================================================
    case 'ping':
        responder(['ok' => true, 'mensagem' => 'API Acesso+ online', 'servidor' => date('Y-m-d H:i:s')]);
        break;

    // =========================================================================
    // 2. LISTAR PLANOS DO CONVÊNIO (TABELA Planos)
    // =========================================================================
    case 'planos':
        $sql = "SELECT Id_Plano, Nome_Plano, Preco FROM Planos ORDER BY Id_Plano ASC";
        $res = $con->query($sql);
        $planos = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $planos[] = [
                    'id' => (int)$row['Id_Plano'],
                    'nome' => $row['Nome_Plano'],
                    'preco' => (float)$row['Preco']
                ];
            }
        }
        responder(['ok' => true, 'planos' => $planos]);
        break;

    // =========================================================================
    // 3. LISTAR MÉDICOS / PROFISSIONAIS CADASTRADOS NO SITE (Cadastro_Profissionais)
    // =========================================================================
    case 'medicos':
        $busca = trim($_GET['busca'] ?? '');
        $especialidade = trim($_GET['especialidade'] ?? '');

        $sql = "SELECT Id_Profissional, Nome_Profissional, Email_Profissional, Registro_Profissional,
                       UF_Registro_Profissional, Rua_Profissional, Numero_Profissional,
                       Bairro_Profissional, Cidade_Profissional, Especialidade_Profissional,
                       Num_Tel_Profissional, Latitude_Profissional, Longitude_Profissional
                FROM Cadastro_Profissionais
                WHERE 1=1";

        $params = [];

        if ($busca !== '') {
            $sql .= " AND (Nome_Profissional ILIKE ? OR Especialidade_Profissional ILIKE ? OR Cidade_Profissional ILIKE ? OR Rua_Profissional ILIKE ?)";
            $like = '%' . $busca . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($especialidade !== '' && strcasecmp($especialidade, 'Todas') !== 0) {
            $sql .= " AND Especialidade_Profissional ILIKE ?";
            $params[] = '%' . $especialidade . '%';
        }

        $sql .= " ORDER BY Nome_Profissional ASC";

        $res = $con->query($sql, $params);
        if (!$res) {
            responder(['ok' => false, 'erro' => 'Erro ao consultar médicos.'], 500);
        }

        $medicos = [];
        while ($row = $res->fetch_assoc()) {
            $medicos[] = [
                'id' => (int)$row['Id_Profissional'],
                'nome' => $row['Nome_Profissional'] ?? '',
                'email' => $row['Email_Profissional'] ?? '',
                'registro' => trim(($row['Registro_Profissional'] ?? '') . (!empty($row['UF_Registro_Profissional']) ? '/' . $row['UF_Registro_Profissional'] : '')),
                'especialidade' => $row['Especialidade_Profissional'] ?? '',
                'rua' => $row['Rua_Profissional'] ?? '',
                'numero' => $row['Numero_Profissional'] ?? '',
                'bairro' => $row['Bairro_Profissional'] ?? '',
                'cidade' => $row['Cidade_Profissional'] ?? '',
                'telefone' => $row['Num_Tel_Profissional'] ?? '',
                'latitude' => $row['Latitude_Profissional'] ?? '',
                'longitude' => $row['Longitude_Profissional'] ?? ''
            ];
        }

        responder(['ok' => true, 'total' => count($medicos), 'medicos' => $medicos]);
        break;

    // =========================================================================
    // 4. HORÁRIOS DISPONÍVEIS DE UM MÉDICO EM UMA DATA (MESMA REGRA DO SITE)
    // =========================================================================
    case 'horarios':
        $idProfissional = (int)($_GET['medico_id'] ?? 0);
        $dataConsulta = trim($_GET['data'] ?? '');

        if ($idProfissional <= 0 || $dataConsulta === '') {
            responder(['ok' => false, 'erro' => 'Médico e data são obrigatórios.'], 400);
        }

        $horariosExpediente = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

        $stmt_sql = "SELECT Hora_Consulta FROM Consultas
             WHERE Id_Profissional = ? AND Data_Consulta = ?
               AND Status_Consulta NOT IN ('Cancelado', 'Cancelada')";
        $stmt_params = [$idProfissional, $dataConsulta];
        $res = $con->query($stmt_sql, $stmt_params);

        $ocupados = [];
        while ($row = $res->fetch_assoc()) {
            $ocupados[] = substr($row['Hora_Consulta'], 0, 5);
        }

        $disponiveis = array_values(array_diff($horariosExpediente, $ocupados));
        responder(['ok' => true, 'horarios' => $disponiveis]);
        break;

    // =========================================================================
    // 5. LOGIN DE PACIENTE (Cadastro_Users + Planos)
    // =========================================================================
    case 'login':
        $login = trim($body['login'] ?? '');
        $senha = (string)($body['senha'] ?? '');

        if ($login === '' || $senha === '') {
            responder(['ok' => false, 'erro' => 'Informe e-mail ou CPF e a senha.'], 400);
        }

        $cpfLimpo = apenasDigitos($login);

        $stmt_sql = "SELECT u.*, p.Nome_Plano, p.Preco
             FROM Cadastro_Users u
             LEFT JOIN Planos p ON u.Id_Plano = p.Id_Plano
             WHERE (u.Email_User = ? OR u.CPF_User = ? OR REPLACE(REPLACE(u.CPF_User, '.', ''), '-', '') = ?)
               AND u.Senha_User = ?
             LIMIT 1";
        $stmt_params = [$login, $login, $cpfLimpo, $senha];
        $res = $con->query($stmt_sql, $stmt_params);

        if ($row = $res->fetch_assoc()) {
            responder(['ok' => true, 'usuario' => montarUsuarioJson($row)]);
        }
        responder(['ok' => false, 'erro' => 'E-mail/CPF ou senha inválidos.'], 401);
        break;

    // =========================================================================
    // 6. CADASTRO DE PACIENTE (Cadastro_Users)
    // =========================================================================
    case 'cadastro':
        $nome = trim($body['nome'] ?? '');
        $email = trim($body['email'] ?? '');
        $cpf = trim($body['cpf'] ?? '');
        $senha = (string)($body['senha'] ?? '');
        $telefone = trim($body['telefone'] ?? '(11) 90000-0000');
        $idPlano = isset($body['id_plano']) ? (int)$body['id_plano'] : 2;
        if ($idPlano <= 0) $idPlano = 2;

        if ($nome === '' || $email === '' || $cpf === '' || $senha === '') {
            responder(['ok' => false, 'erro' => 'Preencha nome, e-mail, CPF e senha.'], 400);
        }

        $cpfLimpo = apenasDigitos($cpf);
        if (strlen($cpfLimpo) === 11) {
            $cpfFormatado = substr($cpfLimpo, 0, 3) . '.' . substr($cpfLimpo, 3, 3) . '.' . substr($cpfLimpo, 6, 3) . '-' . substr($cpfLimpo, 9, 2);
        } else {
            $cpfFormatado = $cpf;
        }

        // Verifica duplicidade
        $stmtCheck_sql = "SELECT Id_User FROM Cadastro_Users
             WHERE Email_User = ? OR CPF_User = ? OR REPLACE(REPLACE(CPF_User, '.', ''), '-', '') = ?
             LIMIT 1";
        $stmtCheck_params = [$email, $cpfFormatado, $cpfLimpo];
        $resCheck = $con->query($stmtCheck_sql, $stmtCheck_params);
        if ($resCheck && $resCheck->num_rows > 0) {
            responder(['ok' => false, 'erro' => 'Este CPF ou e-mail já está cadastrado no sistema.'], 409);
        }

        $dtaNasc = trim($body['data_nasc'] ?? '2000-01-01');
        $genero = trim($body['genero'] ?? 'Não informado');
        $estCivil = trim($body['est_civil'] ?? 'Não informado');
        $cep = trim($body['cep'] ?? '00000-000');
        $numero = trim($body['numero'] ?? 'S/N');
        $statusPag = 'Pendente';

        $stmtIns_sql = "INSERT INTO Cadastro_Users
                (Nome_User, Dta_Nasc_User, Genero_User, CPF_User, Est_Civil_User, Num_Tel_User,
                 CEP_User, Numero_User, Email_User, Senha_User, Id_Plano, Status_Pagamento)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             RETURNING Id_User";
        $stmtIns_params = [$nome, $dtaNasc, $genero, $cpfFormatado, $estCivil, $telefone, $cep, $numero, $email, $senha, $idPlano, $statusPag];
        $stmtIns_res = $con->query($stmtIns_sql, $stmtIns_params);
        if (!$stmtIns_res) {
            $erroSql = $con->error;
            responder(['ok' => false, 'erro' => 'Erro ao cadastrar paciente: ' . $erroSql], 500);
        }

        $novoId = (int)$stmtIns_res->fetch_assoc()['Id_User'];

        $stmtBusca_sql = "SELECT u.*, p.Nome_Plano, p.Preco
             FROM Cadastro_Users u
             LEFT JOIN Planos p ON u.Id_Plano = p.Id_Plano
             WHERE u.Id_User = ? LIMIT 1";
        $stmtBusca_params = [$novoId];
        $stmtBusca_res = $con->query($stmtBusca_sql, $stmtBusca_params);
        $row = $stmtBusca_res ? $stmtBusca_res->fetch_assoc() : null;

        responder(['ok' => true, 'usuario' => montarUsuarioJson($row)]);
        break;

    // =========================================================================
    // 7. CONSULTAR PERFIL ATUALIZADO DO PACIENTE (PLANO E SITUAÇÃO PAGAMENTO)
    // =========================================================================
    case 'perfil':
        $idUser = (int)($_GET['id_user'] ?? $body['id_user'] ?? 0);
        if ($idUser <= 0) {
            responder(['ok' => false, 'erro' => 'ID do paciente obrigatório.'], 400);
        }

        $stmt_sql = "SELECT u.*, p.Nome_Plano, p.Preco
             FROM Cadastro_Users u
             LEFT JOIN Planos p ON u.Id_Plano = p.Id_Plano
             WHERE u.Id_User = ? LIMIT 1";
        $stmt_params = [$idUser];
        $res = $con->query($stmt_sql, $stmt_params);

        if ($row = $res->fetch_assoc()) {
            responder(['ok' => true, 'usuario' => montarUsuarioJson($row)]);
        }
        responder(['ok' => false, 'erro' => 'Paciente não encontrado.'], 404);
        break;

    // =========================================================================
    // 8. ATUALIZAR PERFIL DO PACIENTE
    // =========================================================================
    case 'atualizar_perfil':
        $idUser = (int)($body['id_user'] ?? 0);
        $nome = trim($body['nome'] ?? '');
        $email = trim($body['email'] ?? '');
        $cpf = trim($body['cpf'] ?? '');
        $senha = (string)($body['senha'] ?? '');

        if ($idUser <= 0 || $nome === '' || $email === '') {
            responder(['ok' => false, 'erro' => 'Dados inválidos para atualização.'], 400);
        }

        if ($senha !== '') {
            $stmt_sql = "UPDATE Cadastro_Users SET Nome_User = ?, Email_User = ?, CPF_User = ?, Senha_User = ? WHERE Id_User = ?";
            $stmt_params = [$nome, $email, $cpf, $senha, $idUser];
        } else {
            $stmt_sql = "UPDATE Cadastro_Users SET Nome_User = ?, Email_User = ?, CPF_User = ? WHERE Id_User = ?";
            $stmt_params = [$nome, $email, $cpf, $idUser];
        }

        $ok = $con->query($stmt_sql, $stmt_params) !== false;
        responder(['ok' => (bool)$ok]);
        break;

    // =========================================================================
    // 9. EXCLUIR CONTA DO PACIENTE
    // =========================================================================
    case 'excluir_conta':
        $idUser = (int)($body['id_user'] ?? 0);
        if ($idUser <= 0) {
            responder(['ok' => false, 'erro' => 'ID do paciente inválido.'], 400);
        }
        $stmt_sql = "DELETE FROM Cadastro_Users WHERE Id_User = ?";
        $stmt_params = [$idUser];
        $ok = $con->query($stmt_sql, $stmt_params) !== false;
        responder(['ok' => (bool)$ok]);
        break;

    // =========================================================================
    // 10. LISTAR CONSULTAS DO PACIENTE (Consultas JOIN Cadastro_Profissionais)
    // =========================================================================
    case 'consultas':
        $idUser = (int)($_GET['id_user'] ?? 0);
        if ($idUser <= 0) {
            responder(['ok' => false, 'erro' => 'ID do paciente obrigatório.'], 400);
        }

        $stmt_sql = "SELECT c.Id_Consulta, c.Id_User, c.Id_Profissional, c.Data_Consulta, c.Hora_Consulta,
                    c.Status_Consulta, c.Observacoes_Medico,
                    p.Nome_Profissional, p.Especialidade_Profissional,
                    p.Rua_Profissional, p.Numero_Profissional, p.Bairro_Profissional, p.Cidade_Profissional
             FROM Consultas c
             INNER JOIN Cadastro_Profissionais p ON c.Id_Profissional = p.Id_Profissional
             WHERE c.Id_User = ?
             ORDER BY c.Data_Consulta DESC, c.Hora_Consulta DESC";
        $stmt_params = [$idUser];
        $res = $con->query($stmt_sql, $stmt_params);

        $consultas = [];
        while ($row = $res->fetch_assoc()) {
            $dataBr = !empty($row['Data_Consulta']) ? date('d/m/Y', strtotime($row['Data_Consulta'])) : '';
            $horaBr = !empty($row['Hora_Consulta']) ? substr($row['Hora_Consulta'], 0, 5) : '';
            $endereco = trim(
                ($row['Rua_Profissional'] ?? '') .
                (!empty($row['Numero_Profissional']) ? ', ' . $row['Numero_Profissional'] : '') .
                (!empty($row['Cidade_Profissional']) ? ' - ' . $row['Cidade_Profissional'] : '')
            );

            $consultas[] = [
                'id' => (int)$row['Id_Consulta'],
                'usuario_id' => (int)$row['Id_User'],
                'profissional_id' => (int)$row['Id_Profissional'],
                'medico' => 'Dr(a). ' . ($row['Nome_Profissional'] ?? ''),
                'especialidade' => $row['Especialidade_Profissional'] ?? '',
                'clinica' => !empty($row['Cidade_Profissional']) ? 'Consultório - ' . $row['Cidade_Profissional'] : 'Rede Credenciada Acesso+',
                'endereco' => $endereco,
                'data' => $dataBr,
                'data_iso' => $row['Data_Consulta'] ?? '',
                'horario' => $horaBr,
                'modalidade' => 'Presencial',
                'status' => $row['Status_Consulta'] ?? 'Pendente',
                'observacoes' => $row['Observacoes_Medico'] ?? ''
            ];
        }

        responder(['ok' => true, 'consultas' => $consultas]);
        break;

    // =========================================================================
    // 11. AGENDAR CONSULTA (TABELA Consultas)
    // =========================================================================
    case 'agendar':
        $idUser = (int)($body['id_user'] ?? 0);
        $idProfissional = (int)($body['id_profissional'] ?? 0);
        $dataIso = trim($body['data'] ?? '');
        $hora = trim($body['hora'] ?? '');

        if ($idUser <= 0 || $idProfissional <= 0 || $dataIso === '' || $hora === '') {
            responder(['ok' => false, 'erro' => 'Informe paciente, profissional, data e horário.'], 400);
        }

        // Força hora cheia no mesmo padrão do FfazerAgendamento.php
        $horaFormatada = date('H:00:00', strtotime($hora));

        // Verifica conflito de horário para o mesmo profissional
        $stmtConf_sql = "SELECT Id_Consulta FROM Consultas
             WHERE Id_Profissional = ? AND Data_Consulta = ? AND Hora_Consulta = ?
               AND Status_Consulta NOT IN ('Cancelado', 'Cancelada')
             LIMIT 1";
        $stmtConf_params = [$idProfissional, $dataIso, $horaFormatada];
        $resConf = $con->query($stmtConf_sql, $stmtConf_params);
        if ($resConf && $resConf->num_rows > 0) {
            responder(['ok' => false, 'erro' => 'Este médico já possui um agendamento nesta data e horário.'], 409);
        }

        $status = 'Pendente';
        $stmtIns_sql = "INSERT INTO Consultas (Id_User, Id_Profissional, Data_Consulta, Hora_Consulta, Status_Consulta)
             VALUES (?, ?, ?, ?, ?)
             RETURNING Id_Consulta";
        $stmtIns_params = [$idUser, $idProfissional, $dataIso, $horaFormatada, $status];
        $stmtIns_res = $con->query($stmtIns_sql, $stmtIns_params);
        if ($stmtIns_res) {
            $idConsulta = (int)$stmtIns_res->fetch_assoc()['Id_Consulta'];
            responder(['ok' => true, 'id_consulta' => $idConsulta, 'mensagem' => 'Consulta agendada com sucesso!']);
        }

        $erroSql = $con->error;
        responder(['ok' => false, 'erro' => 'Erro ao salvar consulta: ' . $erroSql], 500);
        break;

    // =========================================================================
    // 12. CANCELAR CONSULTA
    // =========================================================================
    case 'cancelar_consulta':
        $idConsulta = (int)($body['id_consulta'] ?? 0);
        $idUser = (int)($body['id_user'] ?? 0);

        if ($idConsulta <= 0 || $idUser <= 0) {
            responder(['ok' => false, 'erro' => 'Dados inválidos para cancelamento.'], 400);
        }

        $stmt_sql = "UPDATE Consultas SET Status_Consulta = 'Cancelada' WHERE Id_Consulta = ? AND Id_User = ?";
        $stmt_params = [$idConsulta, $idUser];
        $ok = $con->query($stmt_sql, $stmt_params) !== false;
        responder(['ok' => (bool)$ok]);
        break;

    // =========================================================================
    // 13. ALTERAR PLANO DO PACIENTE (Cadastro_Users.Id_Plano)
    // =========================================================================
    case 'alterar_plano':
        $idUser = (int)($body['id_user'] ?? 0);
        $idPlano = (int)($body['id_plano'] ?? 0);
        $nomePlano = trim($body['nome_plano'] ?? '');

        if ($idUser <= 0) {
            responder(['ok' => false, 'erro' => 'ID do paciente obrigatório.'], 400);
        }

        if ($idPlano <= 0 && $nomePlano !== '') {
            $stmtPlano_sql = "SELECT Id_Plano FROM Planos WHERE LOWER(Nome_Plano) = LOWER(?) LIMIT 1";
            $stmtPlano_params = [$nomePlano];
            $stmtPlano_res = $con->query($stmtPlano_sql, $stmtPlano_params);
            $rowP = $stmtPlano_res ? $stmtPlano_res->fetch_assoc() : null;
            if ($rowP) {
                $idPlano = (int)$rowP['Id_Plano'];
            }
        }

        if ($idPlano <= 0) {
            responder(['ok' => false, 'erro' => 'Plano inválido.'], 400);
        }

        $stmt_sql = "UPDATE Cadastro_Users SET Id_Plano = ? WHERE Id_User = ?";
        $stmt_params = [$idPlano, $idUser];
        $ok = $con->query($stmt_sql, $stmt_params) !== false;
        responder(['ok' => (bool)$ok, 'id_plano' => $idPlano]);
        break;

    // =========================================================================
    // 14. CONFIRMAR PAGAMENTO DO PLANO (Cadastro_Users.Status_Pagamento = 'Ativo')
    // =========================================================================
    case 'pagar_plano':
        $idUser = (int)($body['id_user'] ?? 0);
        if ($idUser <= 0) {
            responder(['ok' => false, 'erro' => 'ID do paciente obrigatório.'], 400);
        }

        $stmt_sql = "UPDATE Cadastro_Users SET Status_Pagamento = 'Ativo' WHERE Id_User = ?";
        $stmt_params = [$idUser];
        $ok = $con->query($stmt_sql, $stmt_params) !== false;
        responder(['ok' => (bool)$ok, 'status_pagamento' => 'Ativo']);
        break;

    default:
        responder(['ok' => false, 'erro' => 'Ação não reconhecida.'], 400);
        break;
}
?>
