-- --------------------------------------------------------
-- Script de Criação do Banco de Dados - Acesso+ (TCC)
-- --------------------------------------------------------

-- 1. Criação da tabela de Usuários (Pacientes)
CREATE TABLE IF NOT EXISTS Cadastro_Users (
    Id_User INT AUTO_INCREMENT PRIMARY KEY,
    Nome_User VARCHAR(255) NOT NULL,
    Email_User VARCHAR(255) NOT NULL UNIQUE,
    CPF_User VARCHAR(14) NOT NULL UNIQUE,
    Dta_Nasc_User DATE NOT NULL,
    Genero_User VARCHAR(50) NOT NULL,
    Est_Civil_User VARCHAR(50) NOT NULL,
    Num_Tel_User VARCHAR(20) NOT NULL,
    CEP_User VARCHAR(10) NOT NULL,
    Numero_User VARCHAR(10) NOT NULL,
    Rua_User VARCHAR(255),
    Bairro_User VARCHAR(100),
    Cidade_User VARCHAR(100),
    Latitude_User VARCHAR(50),
    Longitude_User VARCHAR(50),
    Senha_User VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Criação da tabela de Profissionais (Médicos)
CREATE TABLE IF NOT EXISTS Cadastro_Profissionais (
    Id_Profissional INT AUTO_INCREMENT PRIMARY KEY,
    Nome_Profissional VARCHAR(255) NOT NULL,
    Email_Profissional VARCHAR(255) NOT NULL UNIQUE,
    CPF_Profissional VARCHAR(14) NOT NULL UNIQUE,
    Registro_Profissional VARCHAR(50) NOT NULL UNIQUE, -- CRM, etc
    UF_Registro_Profissional VARCHAR(2) NOT NULL,
    CEP_Profissional VARCHAR(10) NOT NULL,
    Numero_Profissional VARCHAR(10) NOT NULL,
    Rua_Profissional VARCHAR(255),
    Bairro_Profissional VARCHAR(100),
    Cidade_Profissional VARCHAR(100),
    Latitude_Profissional VARCHAR(50),  -- Adicionado para os mapas do Google
    Longitude_Profissional VARCHAR(50), -- Adicionado para os mapas do Google
    Especialidade_Profissional VARCHAR(100) NOT NULL,
    Dta_Nasc_Profissional DATE NOT NULL,
    Genero_Profissional VARCHAR(50) NOT NULL,
    Num_Tel_Profissional VARCHAR(20) NOT NULL,
    Senha_Profissional VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Criação da tabela de Agendamentos
CREATE TABLE IF NOT EXISTS Consultas (
    Id_Consulta INT AUTO_INCREMENT PRIMARY KEY,
    Id_User INT NOT NULL,
    Id_Profissional INT NOT NULL,
    Data_Consulta DATE NOT NULL,
    Hora_Consulta TIME NOT NULL, 
    Status_Consulta VARCHAR(50) DEFAULT 'Pendente',
    Data_Criacao_Consulta TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Registra exatamente quando a consulta foi marcada
    
    -- Relações: Agendamento pertence a um usuário e a um profissional
    FOREIGN KEY (Id_User) REFERENCES Cadastro_Users(Id_User) ON DELETE CASCADE,
    FOREIGN KEY (Id_Profissional) REFERENCES Cadastro_Profissionais(Id_Profissional) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Fim do Script
-- --------------------------------------------------------
