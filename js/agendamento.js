document.addEventListener('DOMContentLoaded', function() {
    const selectMedico = document.getElementById('id_profissional');
    const selectHora = document.getElementById('hora');
    
    // Configura o calendário
    flatpickr("#data", {
        locale: "pt", 
        dateFormat: "Y-m-d", 
        minDate: "today", 
        disable: [
            function(date) {
                // Retorna verdadeiro para desativar o dia (0 é Domingo, 6 é Sábado)
                return (date.getDay() === 0 || date.getDay() === 6);
            }
        ],
        // Essa função roda toda vez que o paciente escolhe uma data!
        onChange: function(selectedDates, dateStr, instance) {
            buscarHorariosLivres(dateStr);
        }
    });

    // Se o paciente trocar de médico depois de já ter escolhido a data, atualiza as horas
    selectMedico.addEventListener('change', function() {
        const dataEscolhida = document.getElementById('data').value;
        if (dataEscolhida) {
            buscarHorariosLivres(dataEscolhida);
        }
    });

    function buscarHorariosLivres(dataEscolhida) {
        const medicoId = selectMedico.value;
        
        // Se ainda não escolheu o médico, avisa e bloqueia
        if (!medicoId) {
            selectHora.innerHTML = '<option value="" disabled selected hidden>Selecione o médico primeiro</option>';
            selectHora.disabled = true;
            // Para forçar o usuário a ver o aviso, podemos até limpar a data
            return;
        }

        // Mostra estado de carregando
        selectHora.innerHTML = '<option value="" disabled selected hidden>Buscando horários livres...</option>';
        selectHora.disabled = true;

        // Faz a requisição para o PHP (AJAX)
        fetch(`api_buscar_horarios.php?medico_id=${medicoId}&data=${dataEscolhida}`)
            .then(resposta => resposta.json())
            .then(horarios => {
                selectHora.innerHTML = '<option value="" disabled selected hidden>Selecione um horário</option>';
                
                if (horarios.length === 0) {
                    selectHora.innerHTML = '<option value="" disabled selected hidden>Nenhum horário livre nesse dia</option>';
                } else {
                    // Preenche o <select> apenas com os horários que sobraram
                    horarios.forEach(hora => {
                        const opcao = document.createElement('option');
                        opcao.value = hora + ':00'; // O banco espera HH:MM:SS (ex: 14:00:00)
                        opcao.textContent = hora;
                        selectHora.appendChild(opcao);
                    });
                    // Libera o campo
                    selectHora.disabled = false;
                }
            })
            .catch(erro => {
                console.error("Erro na API:", erro);
                selectHora.innerHTML = '<option value="" disabled selected hidden>Erro de conexão</option>';
            });
    }
});