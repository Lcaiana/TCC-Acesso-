
    flatpickr("#data", {
        locale: "pt", // Calendário em português
        dateFormat: "Y-m-d", // Formato que o banco de dados entende
        minDate: "today", // BLOQUEIA DATAS NO PASSADO (O paciente não pode agendar para ontem)
        
        // Exemplo: Bloqueando Finais de Semana (Sábado e Domingo)
        disable: [
            function(date) {
                // Retorna verdadeiro para desativar o dia (0 é Domingo, 6 é Sábado)
                return (date.getDay() === 0 || date.getDay() === 6);
            }
        ]
    });