# Site Acesso+ (PHP + Supabase + Railway)

Este é o site do Acesso+. O arquivo `api_app.php` é a API que o app Android usa. **Site e app usam o mesmo banco PostgreSQL no Supabase.**

```
App Android ──HTTPS──> api_app.php ┐
                                   ├─> Railway (PHP 8.3 + Apache) ──> Supabase (PostgreSQL)
Navegador ───HTTPS──> páginas .php ┘
```

## 1. Criar o banco no Supabase

1. Entre em https://supabase.com, clique em **New project** e escolha a região **South America (São Paulo)**. Guarde a senha do banco.
2. Abra **SQL Editor**, cole todo o conteúdo de `banco_de_dados.sql` e clique em **Run**.
3. Clique em **Connect** (no topo do projeto) e escolha **Session pooler**. Copie a string `postgresql://postgres.xxxx:[YOUR-PASSWORD]@aws-0-sa-east-1.pooler.supabase.com:5432/postgres` e troque `[YOUR-PASSWORD]` pela senha do banco.
   - Use o **Session pooler**, não a "Direct connection": a conexão direta só funciona por IPv6, e o Railway não alcança.
   - Se a senha tiver caracteres especiais (`@ # / ? %`), codifique-os na URL (ex.: `@` vira `%40`).

## 2. Publicar no Railway

1. Envie este repositório para o GitHub (com a pasta `site/`).
2. Em https://railway.com clique em **New Project → Deploy from GitHub repo** e escolha o repositório.
3. No serviço criado, abra **Settings**:
   - **Root Directory** = `/site` (o Railway encontra o `Dockerfile` e o `railway.json` sozinho).
   - Em **Networking**, clique em **Generate Domain** para ganhar um endereço `https://xxxx.up.railway.app`.
4. Em **Variables**, adicione:
   - `DATABASE_URL` = a string do Session pooler do passo 1.3
   - `GOOGLE_MAPS_API_KEY` = sua chave do Google Maps (opcional, só para o mapa da Rede Credenciada)
5. Aguarde o deploy e teste `https://xxxx.up.railway.app/api_app.php?action=ping`. A resposta deve ser `{"ok":true,...}`.

## 3. Apontar o app para o site

Em `app/src/main/java/com/example/acessomais/ApiClient.java`, troque `BASE_URL` por
`https://xxxx.up.railway.app/api_app.php` e gere o app de novo.

## Testar no computador (opcional)

Copie `.env.example` para `.env`, preencha `DATABASE_URL` e rode `php -S localhost:8000` dentro da pasta `site/`. Precisa ter PHP 8 instalado, com a extensão `pdo_pgsql` ativa.
