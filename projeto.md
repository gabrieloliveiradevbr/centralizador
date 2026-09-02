Estamos adicionando a funcionalidade de "Linha do Tempo / Histórico do Trabalhador" no projeto Centralizador (Laravel 13 + PostgreSQL + Alpine.js).

Objetivo:
Ao abrir o modal de detalhes de um registro no `dashboard.blade.php`, se a linha contiver uma chave identificadora do trabalhador (ex: `cpf`, `num_cpf_trab`, `matricula`), deve haver um botão "Ver Histórico". Esse botão consulta via AJAX o histórico de eventos desse trabalhador no eSocial e exibe em um painel/modal com visual de linha do tempo (Timeline).

Tarefas:

1. Backend - Criar `TrabalhadorHistoryService.php`:
   - Método `getHistorico(string $banco, string $identificador)`
   - Monte consultas seguras com `DB::connection($banco)` unificando principais tabelas de eventos do eSocial (`esocial.s_2200`, `esocial.s_2206`, `esocial.s_2230`, `esocial.s_1200`, etc.) ordenadas por data.

2. Rota e Controller:
   - Adicione a rota GET `/{banco}/trabalhador/{identificador}/historico` nomeada como `trabalhador.historico`.
   - Adicione o método `historicoTrabalhador` no `VisualizadorController` retornando JSON.

3. Frontend - Modificação na `dashboard.blade.php`:
   - Adicione ao estado do Alpine.js (`x-data` principal): `historicoTrabalhador: []`, `carregandoHistorico: false`, `modalHistoricoOpen: false`.
   - Adicione a função `carregarHistoricoTrabalhador(id)` no Alpine.js.
   - Adicione o botão "Ver Linha do Tempo" no rodapé/cabeçalho do Modal de Detalhes (`registroSelecionado`).
   - Crie o submodal/gaveta para exibir a timeline dos eventos formatados.

Por favor, apresente um resumo das alterações e aguarde minha confirmação antes de alterar os arquivos.
