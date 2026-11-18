<?php
    session_start();
    // Verifica se está logado e se é realmente uma conta de profissional
    if(!isset($_SESSION['CPF']) || !isset($_SESSION['nome']) || !isset($_SESSION['tipo_conta']) || $_SESSION['tipo_conta'] != 'profissional')
    {
        unset($_SESSION['CPF']);
        unset($_SESSION['senha']);
        unset($_SESSION['nome']);
        unset($_SESSION['tipo_conta']);
        header("location: loginProfissional.html");
        exit();
    }

    $nomeCompleto = $_SESSION['nome']; 
    $primeiroNome = explode(' ', trim($nomeCompleto))[0];
    include "conexao.php";

    $IdProfissional = $_SESSION['id'] ?? 0;

    // Busca TODAS as consultas do profissional
    $query = "SELECT c.Id_Consulta, c.Data_Consulta, c.Hora_Consulta, c.Status_Consulta, u.Nome_User, u.Num_Tel_User 
              FROM Consultas c 
              INNER JOIN Cadastro_Users u ON c.Id_User = u.Id_User 
              WHERE c.Id_Profissional = ? 
              ORDER BY c.Data_Consulta ASC, c.Hora_Consulta ASC";
    
    $result = $con->query($query, [(int)$IdProfissional]);

    // Arrays para separar as consultas
    $realizadas = [];
    $atrasadas = [];
    $futuras = [];

    // Pega a data e hora atual do servidor para comparar
    date_default_timezone_set('America/Sao_Paulo');
    $hoje = date('Y-m-d');
    $agora = date('H:i:s');

    if($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $status = strtolower($row['Status_Consulta']);
            
            if ($status == 'realizada') {
                $realizadas[] = $row;
            } else if ($status == 'cancelado' || $status == 'cancelada') {
                // Se quiser exibir canceladas, crie um array. Por enquanto vamos ignorar ou colocar nas realizadas.
            } else {
                // Se está pendente, verifica se atrasou
                if ($row['Data_Consulta'] < $hoje || ($row['Data_Consulta'] == $hoje && $row['Hora_Consulta'] < $agora)) {
                    $atrasadas[] = $row;
                } else {
                    $futuras[] = $row;
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso+ | Minha Agenda</title>
    <link rel="stylesheet" href="css/areaUsuario.css">
    <link rel="stylesheet" href="css/areaProfissional.css">
    <link rel="stylesheet" href="css/minhaAgenda.css">
</head>
<body>
    <?php include 'menuProfissional.php'; ?>

    <header class="area-header">
        <div class="header-content">
            <span class="eyebrow">Gestão de Consultas</span>
            <h1>Minha Agenda</h1>
            <p>Acompanhe seu histórico, próximos compromissos e pendências.</p>
        </div>
    </header>

    <main class="agenda-container">
        <!-- Navegação por abas -->
        <div class="tabs">
            <button class="tab-btn active" onclick="abrirAba(event, 'aba-futuras')">Por vir (<?php echo count($futuras); ?>)</button>
            <button class="tab-btn" onclick="abrirAba(event, 'aba-atrasadas')">Em Atraso (<?php echo count($atrasadas); ?>)</button>
            <button class="tab-btn" onclick="abrirAba(event, 'aba-realizadas')">Realizadas (<?php echo count($realizadas); ?>)</button>
        </div>

        <!-- Conteúdo das abas -->
        <div class="tab-content active" id="aba-futuras">
            <?php if(count($futuras) > 0): ?>
                <div class="grid-agenda">
                    <?php foreach($futuras as $c): ?>
                        <div class="card-agenda futuro">
                            <h3><?php echo htmlspecialchars($c['Nome_User']); ?></h3>
                            <p><strong>Data:</strong> <?php echo date("d/m/Y", strtotime($c['Data_Consulta'])); ?></p>
                            <p><strong>Hora:</strong> <?php echo date("H:i", strtotime($c['Hora_Consulta'])); ?></p>
                            <span class="badge futura">Agendada</span>
                            <a href="prontuario.php?id_consulta=<?php echo $c['Id_Consulta']; ?>" class="btn-acao">Ir para Prontuário</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Nenhuma consulta agendada para o futuro.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="aba-atrasadas">
            <?php if(count($atrasadas) > 0): ?>
                <div class="grid-agenda">
                    <?php foreach($atrasadas as $c): ?>
                        <div class="card-agenda atraso">
                            <h3><?php echo htmlspecialchars($c['Nome_User']); ?></h3>
                            <p><strong>Data:</strong> <?php echo date("d/m/Y", strtotime($c['Data_Consulta'])); ?></p>
                            <p><strong>Hora:</strong> <?php echo date("H:i", strtotime($c['Hora_Consulta'])); ?></p>
                            <span class="badge atrasada">Atrasada</span>
                            <a href="prontuario.php?id_consulta=<?php echo $c['Id_Consulta']; ?>" class="btn-acao">Resolver Pendência</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Ótimo! Você não possui consultas em atraso.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="aba-realizadas">
            <?php if(count($realizadas) > 0): ?>
                <div class="grid-agenda">
                    <?php foreach($realizadas as $c): ?>
                        <div class="card-agenda realizada">
                            <h3><?php echo htmlspecialchars($c['Nome_User']); ?></h3>
                            <p><strong>Data:</strong> <?php echo date("d/m/Y", strtotime($c['Data_Consulta'])); ?></p>
                            <p><strong>Hora:</strong> <?php echo date("H:i", strtotime($c['Hora_Consulta'])); ?></p>
                            <span class="badge realizada">Finalizada</span>
                            <a href="prontuario.php?id_consulta=<?php echo $c['Id_Consulta']; ?>" class="btn-acao sec">Ver Prontuário</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Nenhuma consulta finalizada ainda.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Função simples para alternar as abas
        function abrirAba(evento, nomeAba) {
            // Esconde todo mundo
            const conteudos = document.getElementsByClassName("tab-content");
            for (let i = 0; i < conteudos.length; i++) {
                conteudos[i].classList.remove("active");
            }
            
            // Tira o azul (ativo) de todos os botões
            const botoes = document.getElementsByClassName("tab-btn");
            for (let i = 0; i < botoes.length; i++) {
                botoes[i].classList.remove("active");
            }
            
            // Mostra a aba clicada e pinta o botão de azul
            document.getElementById(nomeAba).classList.add("active");
            evento.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>
