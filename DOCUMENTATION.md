# Documentação Técnica: Projeto Centralizador de Dados

Este documento foi criado para servir de base de conhecimento (Knowledge Base) para estudo e análise do projeto "Centralizador de Dados". Ele detalha a arquitetura, o fluxo de dados e as decisões técnicas implementadas.

## 1. Visão Geral do Projeto
O **Centralizador de Dados** é uma aplicação desenvolvida em Laravel 11 para permitir que administradores visualizem e gerenciem dados de múltiplas instâncias de bancos de dados PostgreSQL (multi-tenant) de forma dinâmica, com foco especial nos dados do eSocial e na gestão de Certificados Digitais A1.

### Objetivos Principais:
- **Visualização Dinâmica**: Acessar qualquer tabela de qualquer banco configurado sem precisar escrever código para cada nova tabela.
- **Gestão de Certificados**: Centralizar o upload, download e controle de certificados digitais (.pfx/.p12) vinculados a entidades ou procuradores.
- **Interface Unificada**: Dashboard moderno com suporte a Dark Mode e responsividade.

---

## 2. Arquitetura Técnica

### Stack Tecnológica
- **Backend**: Laravel 11 (PHP 8.5).
- **Banco de Dados**: PostgreSQL (múltiplas conexões).
- **Frontend**: Tailwind CSS (estilização), Alpine.js (interatividade no cliente), Remix Icon (ícones).
- **Armazenamento**: Disco local do servidor (`storage/app`).

### Estratégia de Multi-tenancy (Múltiplos Bancos)
O projeto não usa um único banco com `tenant_id`. Em vez disso, ele usa **conexões separadas**.
- As conexões são definidas em `config/database.php`.
- O parâmetro `{banco}` é passado via URL em quase todas as rotas (ex: `/cm_jucas/certificados`).
- O sistema alterna a conexão do banco de dados em tempo de execução usando `DB::connection($banco)`.

---

## 3. Detalhamento dos Módulos

### 3.1 Visualizador de Tabelas (O "Coração" do Projeto)
Este módulo permite navegar por Schemas $\to$ Tabelas $\to$ Dados.

#### Fluxo de Funcionamento:
1. **Seleção**: O usuário escolhe o banco, o schema (ex: `esocial`) e a tabela.
2. **Metadados**: O `PostgresMetadataRepository` consulta a `information_schema.tables` e `information_schema.columns` do Postgres para descobrir quais colunas a tabela possui.
3. **Construção da Query**: O `TableQueryService` monta a query SQL dinamicamente:
   - **Busca Global**: Percorre todas as colunas da tabela usando `ILIKE` para encontrar um termo.
   - **Filtros por Coluna**: Aplica filtros específicos para colunas selecionadas.
   - **Ordenação**: Altera o `ORDER BY` com base no clique do usuário no cabeçalho da tabela.
   - **Duplicados**: Identifica registros repetidos em uma coluna específica usando `GROUP BY` e `HAVING COUNT(*) > 1`.
4. **Paginação**: Os dados são retornados paginados para evitar travamentos com tabelas gigantes.

#### Componentes Chave:
- `VisualizadorController`: Orquestra a requisição e prepara os dados para a View.
- `TableQueryService`: Contém a lógica complexa de construção de queries SQL.
- `DashboardData` (DTO): Um objeto de transferência de dados que garante que a View receba exatamente o que precisa.

### 3.2 Gestão de Certificados Digitais
Módulo para controle de arquivos `.pfx` e `.p12`.

#### Fluxo de Funcionamento:
- **Upload**: O usuário envia o arquivo e a senha. O sistema sanitiza o documento (CPF/CNPJ), salva o arquivo fisicamente no storage com um timestamp e salva a senha no banco `esocial.certificados`.
- **Download**: O sistema localiza o arquivo mais recente associado ao documento no storage e o entrega ao usuário com o MIME type correto (`application/x-pkcs12`).
- **Exclusão**: Remove o registro do banco e apaga todos os arquivos físicos vinculados àquele documento.
- **Procuração**: Suporta a associação do certificado ao CNPJ da entidade ou ao CPF/CNPJ de um procurador externo.

---

## 4. Estrutura de Arquivos Principal

### Backend (Logic)
- `app/Http/Controllers/VisualizadorController.php`: Controla a navegação e visualização de dados.
- `app/Http/Controllers/CertificadoController.php`: Controla o ciclo de vida dos certificados.
- `app/Services/TableQueryService.php`: Lógica de filtragem e query do Postgres.
- `app/Repositories/PostgresMetadataRepository.php`: Consultas aos metadados do banco.
- `app/DTOs/DashboardData.php`: Estrutura de dados para a View.

### Frontend (Views)
- `resources/views/layout/app.blade.php`: Layout base com Sidebar e Header.
- `resources/views/index.blade.php`: Página principal do visualizador.
- `resources/views/components/tabela.blade.php`: Componente complexo da tabela com filtros e busca.
- `resources/views/certificados/index.blade.php`: Tela de gestão de certificados.

---

## 5. Pontos de Atenção e Aprendizado (Estudo)

### Desafios Superados
1. **Bypass de `finfo`**: O servidor não possuía a extensão `php_fileinfo`. A solução foi trocar `Storage::exists()` e `Storage::put()` por funções nativas do PHP (`file_exists`, `move()`), que não dependem de análise de MIME type interna.
2. **SQL Dinâmico**: O uso de `CAST(coluna AS TEXT)` permite que a busca global funcione em colunas de tipos diferentes (Inteiros, Datas, Booleanos) transformando tudo em texto para a comparação.

### Conceitos de Engenharia Aplicados
- **Single Responsibility Principle (SRP)**: A lógica de banco foi movida para *Services* e *Repositories*, deixando os *Controllers* magros.
- **DTO Pattern**: O uso de `DashboardData` evita passar arrays gigantes e desestruturados para as views, facilitando a manutenção.
- **Multi-tenancy via Conexões**: Abordagem eficiente para lidar com múltiplos bancos de dados independentes.

---

## 6. Guia de Estudo para o Aluno
Para aprender como o projeto funciona, siga esta ordem:
1. **Siga a Rota**: Abra o `routes/web.php` e veja para qual Controller cada URL aponta.
2. **Estude a Query**: Olhe o `TableQueryService` e tente entender como ele monta o SQL. Tente adicionar um novo filtro.
3. **Análise o Frontend**: Veja como o `Alpine.js` no `layout/app.blade.php` controla a abertura de painéis e o Dark Mode.
4. **Pratique a Refatoração**: Tente criar um novo componente de View para exibir estatísticas simples de uma tabela.
