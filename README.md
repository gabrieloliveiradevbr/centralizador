# 🏛️ Centralizador de Dados

O **Centralizador de Dados** é uma aplicação desenvolvida em Laravel para visualização e auditoria dinâmica de bases de dados PostgreSQL. O sistema foi projetado especificamente para facilitar a gestão de dados de RH, Ponto e eSocial de Câmaras Municipais, permitindo que administradores consultem e corrijam registros sem a necessidade de escrever queries SQL complexas.

## 🚀 Funcionalidades Principais

- **Exploração Dinâmica**: Descoberta automática de schemas e tabelas via `information_schema`.
- **Visualizador Universal**: Interface de tabela com suporte a paginação, ordenação e filtros globais por coluna.
- **Edição Rápida (CRUD)**: Permite a atualização e exclusão de registros em tabelas que possuam Chaves Primárias (PK) identificadas.
- **Módulo eSocial**:
    - Navegação categorizada por fases (Tabelas, Não Periódicos, SST, etc.).
    - Banner informativo com contexto técnico para cada evento do eSocial.
- **Linha do Tempo do Trabalhador**: Consolida todos os eventos de um trabalhador (vinculados por CPF/Matrícula) em uma timeline cronológica, integrando dados de múltiplas tabelas e a tabela de histórico de envios.
- **Multi-Banco**: Suporte a múltiplas conexões simultâneas (ex: Câmaras de Anapu, Carnaubal e Jucás).
- **Interface Moderna**: Suporte a Dark Mode e design responsivo utilizando Tailwind CSS e Alpine.js.

## 🛠️ Stack Tecnológica

- **Backend**: [Laravel 11+](https://laravel.com) (PHP 8.2+)
- **Banco de Dados**: [PostgreSQL](https://www.postgresql.org/)
- **Frontend**: [Tailwind CSS](https://tailwindcss.com) & [Alpine.js](https://alpinejs.dev)
- **Ícones**: [Remix Icon](https://remixicon.com)

## 📦 Instalação

### Pré-requisitos
- PHP 8.2 ou superior
- Composer
- Node.js & NPM
- PostgreSQL

### Passo a Passo

1. **Clonar o repositório**
   ```bash
   git clone https://github.com/gabrieloliveiradevbr/centralizador
   cd centralizador
   ```

2. **Instalar dependências do PHP**
   ```bash
   composer install
   ```

3. **Configurar o ambiente**
   Copie o arquivo de exemplo e configure suas credenciais de banco de dados:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar Conexões de Banco de Dados**
   No arquivo `config/database.php`, adicione as conexões para as Câmaras desejadas seguindo o padrão:
   ```php
   'pgsql_jucas' => [
       'driver' => 'pgsql',
       'host' => env('DB_HOST_JUCAS', '127.0.0.1'),
       'database' => env('DB_DATABASE_JUCAS', 'forge'),
       'username' => env('DB_USERNAME_JUCAS', 'forge'),
       'password' => env('DB_PASSWORD_JUCAS', ''),
       'charset' => 'utf8',
       'prefix' => '',
       'schema' => 'public',
       'sslmode' => 'prefer',
   ],
   ```

5. **Instalar e compilar dependências do Frontend**
   ```bash
   npm install
   npm run build
   ```

6. **Executar a aplicação**
   ```bash
   php artisan serve
   ```

## 📖 Como Utilizar

1. Acesse `http://localhost:8000`.
2. No menu superior, selecione a **Câmara (Banco de Dados)** desejada.
3. Na barra lateral, escolha o **Schema** (ex: `esocial`) e a **Tabela** (ex: `s2200`).
4. Utilize a busca global ou os filtros por coluna para localizar registros específicos.
5. Para editar um dado, clique no ícone de lápis (apenas em tabelas com PK).
6. Para ver a linha do tempo de um trabalhador, abra os detalhes do registro e clique em **"Ver Linha do Tempo"**.

## 🛡️ Segurança

**Importante:** Este sistema possui capacidades de alteração de dados (`UPDATE` e `DELETE`). Certifique-se de que ele esteja hospedado em ambiente seguro e com acesso restrito a administradores autorizados.
2
