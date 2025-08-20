# 💳 Sistema de Pagamento Simplificado

Aplicação backend (Laravel 8.83 + PHP 8.2) que permite **cadastro de usuários (comuns e lojistas)**, **depósitos, saques, transferências** e **estorno** com **validação de saldo**, **mock de autorização externa** e **mock de notificação**. Projeto containerizado com **Docker (php-fpm + Nginx + MySQL)**.

---

## 📦 Arquitetura (macro)

-   **Laravel 8.83** (REST, validação, Eloquent, migrations, seeders, testes)
-   **MySQL 8** (dados transacionais)
-   **Nginx** (reverse proxy/servidor web)
-   **php-fpm 8.2** (runtime)
-   **Mocks**:
    -   Autorização externa (ENV `AUTHORIZER_MOCK=allow|deny|random`)
    -   Notificação (log em `storage/logs/laravel.log`)
-   **Regras de negócio** em `app/Services/PaymentService.php`
-   **Transações** SQL para consistência (commit/rollback)

---

## 🚀 Como rodar

### 1) Clonar

```bash
git clone https://github.com/seu-usuario/sistema-pagamento.git
cd sistema-pagamento
```

### 2) Subir containers

```bash
docker compose up -d --build
```

## Serviços

-   **web** → Nginx em [http://localhost:8000](http://localhost:8000)
-   **app** → PHP 8.2 + Laravel 8.83
-   **db** → MySQL 8 (banco `pagamento`)

---

## 3) Configurar ambiente

```bash
cp .env.example .env
docker compose exec app php artisan key:generate
```

Garanta que no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=pagamento
DB_USERNAME=user
DB_PASSWORD=password

MAIL_MAILER=log
AUTHORIZER_MOCK=allow  # allow|deny|random
```

---

## 4) Migrar e seedar

```bash
docker compose exec app php artisan migrate --seed
```

---

## 5) Acessar

-   Browser: [http://localhost:8000](http://localhost:8000)
-   Health-check simples:

```bash
curl http://localhost:8000
```

---

## 🔑 Endpoints (API)

Base: `http://localhost:8000`

Sempre enviar os headers:

-   `Accept: application/json`
-   `Content-Type: application/json`

### Criar usuário

```http
POST /api/users
```

```json
{
    "name": "Cliente B",
    "email": "clienteB@example.com",
    "cpf_cnpj": "33333333333",
    "type": "comum",
    "password": "senha123",
    "balance": 100
}
```

### Depósito

```http
POST /api/deposit
```

```json
{
    "user_id": 1,
    "amount": 50.0
}
```

### Saque

```http
POST /api/withdraw
```

```json
{
    "user_id": 1,
    "amount": 25.0
}
```

### Transferência

```http
POST /api/transfer
```

```json
{
    "sender_id": 1,
    "receiver_id": 2,
    "amount": 25.5
}
```

### Estorno (somente quem recebeu a transferência)

```http
POST /api/transactions/{id}/reverse
```

```json
{
    "by_user_id": 2
}
```

---

## 🧪 Testes

Rodar todos:

```bash
docker compose exec app php artisan test
```

Rodar teste específico:

```bash
docker compose exec app php artisan test --filter=PaymentTest
```

Ambiente de testes usa `.env.testing` (MySQL do container).  
O trait `RefreshDatabase` dropa/recria tabelas durante os testes.

Gerar `.env.testing` a partir do `.env` se necessário:

```bash
cp .env .env.testing
```

---

## 🧰 Exemplos com curl

### Criar usuário

```bash
curl -i -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"name":"Cliente B","email":"clienteB@example.com","cpf_cnpj":"33333333333","type":"comum","password":"senha123","balance":100}'
```

### Depósito

```bash
curl -i -X POST http://localhost:8000/api/deposit \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"user_id":1,"amount":50.00}'
```

### Transferência e estorno

```bash
TX_ID=$(curl -s -X POST http://localhost:8000/api/transfer \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"sender_id":1,"receiver_id":2,"amount":25.50}' | jq -r '.id'); echo $TX_ID

curl -i -X POST "http://localhost:8000/api/transactions/$TX_ID/reverse" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"by_user_id":2}'
```

### Logs de notificação

```bash
docker compose exec app bash -lc "tail -n 100 storage/logs/laravel.log"
```

---

# 🗂️ Estrutura de Arquivos do Projeto

## Estrutura Relevante

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── PaymentController.php
│   │   └── UsersController.php
│   ├── Requests/
│   │   ├── UserStoreRequest.php
│   │   ├── DepositRequest.php
│   │   ├── WithdrawRequest.php
│   │   └── TransferRequest.php
│
├── Models/
│   ├── User.php
│   └── Transaction.php
│
├── Services/
│   └── PaymentService.php
│
database/
├── migrations/
│   ├── 2014_10_12_000000_create_users_table.php
│   └── 2025_08_18_005127_create_transactions_table.php
├── seeders/
│   ├── DatabaseSeeder.php
│   └── UserSeeder.php
│
routes/
└── api.php

Dockerfile
docker-compose.yml
nginx.conf
```

## Descrição dos Diretórios

### `app/`

Diretório principal da aplicação Laravel contendo toda a lógica de negócio.

#### `Http/Controllers/Api/`

-   **PaymentController.php** - Controlador responsável pelas operações de pagamento
-   **UsersController.php** - Controlador para gerenciamento de usuários

#### `Http/Requests/`

-   **UserStoreRequest.php** - Validação para criação de usuários
-   **DepositRequest.php** - Validação para operações de depósito
-   **WithdrawRequest.php** - Validação para operações de saque
-   **TransferRequest.php** - Validação para operações de transferência

#### `Models/`

-   **User.php** - Model do usuário
-   **Transaction.php** - Model das transações

#### `Services/`

-   **PaymentService.php** - Serviço contendo a lógica de negócio para pagamentos

### `database/`

Contém as migrações e seeders do banco de dados.

#### `migrations/`

-   **create_users_table.php** - Migração para criação da tabela de usuários
-   **create_transactions_table.php** - Migração para criação da tabela de transações

#### `seeders/`

-   **DatabaseSeeder.php** - Seeder principal
-   **UserSeeder.php** - Seeder para população da tabela de usuários

### `routes/`

-   **api.php** - Definição das rotas da API

### Arquivos de Configuração

-   **Dockerfile** - Configuração do container Docker
-   **docker-compose.yml** - Orquestração dos containers
-   **nginx.conf** - Configuração do servidor web Nginx

---

## 🧱 Modelagem (resumo)

### users

-   id, name, email (único), cpf_cnpj (único)
-   type (comum|lojista), balance (decimal)
-   password, timestamps

### transactions

-   id, uuid, type (transfer|deposit|withdraw|reversal)
-   sender_id (nullable), receiver_id (nullable)
-   amount (decimal)
-   status (completed|reversed|failed)
-   original_id (referência para estorno), timestamps

---

## ⚙️ Variáveis de ambiente (essenciais)

`.env` (produção/dev local):

```env
APP_NAME=Pagamento
APP_ENV=local
APP_KEY= # gerado pelo artisan
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=pagamento
DB_USERNAME=user
DB_PASSWORD=password

MAIL_MAILER=log
MAIL_FROM_ADDRESS=teste@example.com
MAIL_FROM_NAME="Sistema Pagamento"

AUTHORIZER_MOCK=allow  # allow | deny | random
```

---

## 🧯 Troubleshooting

-   **403 / This action is unauthorized**  
    → Envie `Accept: application/json` nos requests e confira `authorize(): true` nos FormRequest.

-   **SQLSTATE[HY000] [2002] (conexão)**  
    → Garanta `DB_HOST=db` no `.env`.

-   **“Could not open input file: artisan”**  
    → Rode comandos dentro do container:

    ```bash
    docker compose exec app bash -lc "cd /var/www/html && php artisan ..."
    ```

-   **Not Found no navegador**  
    → Confirme `root /var/www/html/public;` no Nginx.

---

## 📖 Contexto do desafio (resumo)

-   Cadastro de usuários (comuns/lojistas) com CPF/CNPJ e e-mail únicos.
-   Depósito/saque/transferência com validação de saldo.
-   Lojistas apenas recebem transferências.
-   Estorno permitido somente ao recebedor.
-   Consulta a serviço autorizador externo (mock) antes de concluir.
-   Transações reversíveis.
-   Notificação (e-mail/SMS) mockada.

**Avaliação:** REST, Git, PSRs/SOLID, design patterns, testes, banco relacional, Docker.
