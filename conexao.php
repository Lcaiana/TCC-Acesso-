<?php
/**
 * Conexão com o banco PostgreSQL do Supabase (usado pelo site E pelo app Android via api_app.php).
 *
 * As credenciais NÃO ficam no código: são lidas de variáveis de ambiente.
 *  - No Railway: aba "Variables" do serviço.
 *  - Localmente: crie um arquivo .env (veja .env.example) ou exporte as variáveis.
 *
 * Variáveis usadas:
 *   DATABASE_URL         -> string de conexão do Supabase (Connect > Session pooler), ex:
 *                           postgresql://postgres.xxxx:SENHA@aws-0-sa-east-1.pooler.supabase.com:5432/postgres
 *   GOOGLE_MAPS_API_KEY  -> chave do Google Maps (opcional, usada na Rede Credenciada)
 */

date_default_timezone_set('America/Sao_Paulo');

// Carrega um arquivo .env local, se existir (apenas para testes no computador)
if (is_readable(__DIR__ . '/.env')) {
    foreach (file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linhaEnv) {
        $linhaEnv = trim($linhaEnv);
        if ($linhaEnv === '' || $linhaEnv[0] === '#' || strpos($linhaEnv, '=') === false) continue;
        [$chave, $valor] = array_map('trim', explode('=', $linhaEnv, 2));
        if (getenv($chave) === false) putenv("$chave=" . trim($valor, "\"'"));
    }
}

$google_maps_api_key = getenv('GOOGLE_MAPS_API_KEY') ?: '';

/**
 * O PostgreSQL devolve nomes de colunas em minúsculas (id_user), mas o código do site
 * usa os nomes originais do MySQL (Id_User). Este mapa restaura as maiúsculas/minúsculas.
 */
const COLUNAS_DO_BANCO = [
    'Id_Plano', 'Nome_Plano', 'Preco',
    'Id_User', 'Nome_User', 'Dta_Nasc_User', 'Genero_User', 'CPF_User', 'Est_Civil_User', 'Num_Tel_User',
    'CEP_User', 'Numero_User', 'Rua_User', 'Bairro_User', 'Cidade_User', 'Latitude_User', 'Longitude_User',
    'Email_User', 'Senha_User', 'Status_Pagamento', 'Token_Senha', 'Expiracao_Token',
    'Id_Profissional', 'Nome_Profissional', 'Email_Profissional', 'CPF_Profissional', 'Registro_Profissional',
    'UF_Registro_Profissional', 'CEP_Profissional', 'Numero_Profissional', 'Rua_Profissional',
    'Bairro_Profissional', 'Cidade_Profissional', 'Latitude_Profissional', 'Longitude_Profissional',
    'Especialidade_Profissional', 'Dta_Nasc_Profissional', 'Genero_Profissional', 'Num_Tel_Profissional',
    'Senha_Profissional',
    'Id_Consulta', 'Data_Consulta', 'Hora_Consulta', 'Status_Consulta', 'Observacoes_Medico', 'Data_Criacao_Consulta',
];

/** Resultado de uma consulta, com a mesma "cara" do mysqli (num_rows e fetch_assoc). */
class Resultado
{
    public int $num_rows;
    private array $linhas;
    private int $posicao = 0;

    public function __construct(array $linhas)
    {
        static $mapa = null;
        if ($mapa === null) {
            $mapa = [];
            foreach (COLUNAS_DO_BANCO as $coluna) $mapa[strtolower($coluna)] = $coluna;
        }
        $this->linhas = array_map(function ($linha) use ($mapa) {
            $nova = [];
            foreach ($linha as $chave => $valor) $nova[$mapa[$chave] ?? $chave] = $valor;
            return $nova;
        }, $linhas);
        $this->num_rows = count($this->linhas);
    }

    public function fetch_assoc(): ?array
    {
        return $this->linhas[$this->posicao++] ?? null;
    }
}

/** Pequena camada sobre o PDO. Sempre use "?" nos valores para evitar SQL Injection. */
class Banco
{
    public PDO $pdo;
    public string $error = '';

    public function __construct(string $url)
    {
        $p = parse_url($url);
        if (!$p || empty($p['host'])) {
            throw new RuntimeException('DATABASE_URL inválida ou não configurada.');
        }
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;sslmode=require',
            $p['host'],
            $p['port'] ?? 5432,
            ltrim($p['path'] ?? '/postgres', '/') ?: 'postgres'
        );
        $this->pdo = new PDO($dsn, urldecode($p['user'] ?? ''), urldecode($p['pass'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Compatível com o pooler do Supabase (que não aceita prepared statements no servidor)
            PDO::ATTR_EMULATE_PREPARES => true,
        ]);
        $this->pdo->exec("SET TIME ZONE 'America/Sao_Paulo'");
    }

    /**
     * Executa um comando SQL com parâmetros.
     * Retorna Resultado (para SELECT ou ... RETURNING) ou false em caso de erro.
     */
    public function query(string $sql, array $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($params));
            $linhas = $stmt->columnCount() > 0 ? $stmt->fetchAll() : [];
            $resultado = new Resultado($linhas);
            if ($stmt->columnCount() === 0) $resultado->num_rows = $stmt->rowCount();
            return $resultado;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log('[Acesso+] Erro SQL: ' . $e->getMessage());
            return false;
        }
    }
}

try {
    $con = new Banco(getenv('DATABASE_URL') ?: '');
} catch (Throwable $e) {
    error_log('[Acesso+] Falha ao conectar no banco: ' . $e->getMessage());
    $con = null;
    if (basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'api_app.php') {
        http_response_code(500);
        exit('Erro ao conectar ao banco de dados. Verifique a variável DATABASE_URL.');
    }
}
