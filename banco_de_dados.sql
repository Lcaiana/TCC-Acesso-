-- --------------------------------------------------------
-- Script de Criação do Banco de Dados - Acesso+ (TCC)
-- Versão PostgreSQL (Supabase)
--
-- Como usar: no painel do Supabase abra "SQL Editor", cole este arquivo inteiro e clique em "Run".
-- Os nomes das tabelas/colunas ficam sem aspas, então o Postgres os guarda em minúsculas
-- (o conexao.php cuida de devolver os nomes originais para o PHP).
-- --------------------------------------------------------

-- TABELA DE PLANOS DO CONVÊNIO
CREATE TABLE IF NOT EXISTS Planos (
    Id_Plano SERIAL PRIMARY KEY,
    Nome_Plano VARCHAR(100) NOT NULL UNIQUE,
    Preco NUMERIC(10,2) NOT NULL
);

-- Inserindo os planos base (Os mesmos que estão no HTML de Planos)
INSERT INTO Planos (Id_Plano, Nome_Plano, Preco) VALUES
    (1, 'Essencial', 89.90),
    (2, 'Plus', 149.90),
    (3, 'Premium', 219.90)
ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('planos', 'id_plano'), (SELECT MAX(Id_Plano) FROM Planos));

-- 1. Criação da tabela de Usuários (Pacientes)
CREATE TABLE IF NOT EXISTS Cadastro_Users (
    Id_User SERIAL PRIMARY KEY,
    Nome_User VARCHAR(255) NOT NULL,
    Dta_Nasc_User DATE NOT NULL,
    Genero_User VARCHAR(50) NOT NULL,
    CPF_User VARCHAR(15) NOT NULL UNIQUE,
    Est_Civil_User VARCHAR(50) NOT NULL,
    Num_Tel_User VARCHAR(20) NOT NULL,
    CEP_User VARCHAR(10) NOT NULL,
    Numero_User VARCHAR(10) NOT NULL,
    Rua_User VARCHAR(255),
    Bairro_User VARCHAR(100),
    Cidade_User VARCHAR(100),
    Latitude_User VARCHAR(50),
    Longitude_User VARCHAR(50),
    Email_User VARCHAR(255) NOT NULL UNIQUE,
    Senha_User VARCHAR(255) NOT NULL,
    Id_Plano INT REFERENCES Planos(Id_Plano),
    Status_Pagamento VARCHAR(50) DEFAULT 'Pendente',
    Token_Senha VARCHAR(100) NULL,
    Expiracao_Token TIMESTAMP NULL
);

-- 2. Criação da tabela de Profissionais (Médicos)
CREATE TABLE IF NOT EXISTS Cadastro_Profissionais (
    Id_Profissional SERIAL PRIMARY KEY,
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
    Latitude_Profissional VARCHAR(50),  -- Para os mapas do Google
    Longitude_Profissional VARCHAR(50), -- Para os mapas do Google
    Especialidade_Profissional VARCHAR(100) NOT NULL,
    Dta_Nasc_Profissional DATE NOT NULL,
    Genero_Profissional VARCHAR(50) NOT NULL,
    Num_Tel_Profissional VARCHAR(20) NOT NULL,
    Senha_Profissional VARCHAR(255) NOT NULL
);

-- 3. Criação da tabela de Agendamentos
CREATE TABLE IF NOT EXISTS Consultas (
    Id_Consulta SERIAL PRIMARY KEY,
    Id_User INT NULL REFERENCES Cadastro_Users(Id_User) ON DELETE CASCADE,
    Id_Profissional INT NOT NULL REFERENCES Cadastro_Profissionais(Id_Profissional) ON DELETE CASCADE,
    Data_Consulta DATE NOT NULL,
    Hora_Consulta TIME NOT NULL,
    Status_Consulta VARCHAR(50) DEFAULT 'Pendente',
    Observacoes_Medico TEXT,
    Data_Criacao_Consulta TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Registra quando a consulta foi marcada
);

CREATE INDEX IF NOT EXISTS idx_consultas_profissional_data ON Consultas (Id_Profissional, Data_Consulta);
CREATE INDEX IF NOT EXISTS idx_consultas_user ON Consultas (Id_User);

-- Segurança do Supabase: o site e o app acessam o banco SOMENTE pelo PHP (usuário postgres).
-- Ativar o RLS sem políticas bloqueia o acesso direto pela API pública do Supabase (chave anon).
ALTER TABLE Planos ENABLE ROW LEVEL SECURITY;
ALTER TABLE Cadastro_Users ENABLE ROW LEVEL SECURITY;
ALTER TABLE Cadastro_Profissionais ENABLE ROW LEVEL SECURITY;
ALTER TABLE Consultas ENABLE ROW LEVEL SECURITY;

-- --------------------------------------------------------
-- Fim do Script
-- --------------------------------------------------------
