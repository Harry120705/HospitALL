# Plano de Implementação

Este plano detalha as etapas para corrigir os bugs de persistência visual, adequar as stores do Pinia ao novo esquema de subdocumentos do MongoDB, criar o histórico de visitas e implementar a restrição de perfil simulada.

## User Review Required

> [!IMPORTANT]
> A recontagem de ocupação será feita de forma reativa no Front-end buscando todos os atendimentos ativos da API. Verifique se essa abordagem está alinhada ou se o back-end futuramente providenciará os números consolidados.
> A restrição de perfil será baseada em uma store global simples (`useAppStore`), controlável a partir do `AppHeader.vue`.

## Proposed Changes

### 1. Persistência dos Dashboards e Ocupação

Para garantir que a ocupação dos leitos reflita fielmente o banco de dados sem depender de um contador estático desatualizado:

#### [MODIFY] `resources/js/stores/hospital.js`
- Adicionar uma chamada para `/api/atendimentos` (filtrando por status 'INTERNADO' / 'AGUARDANDO_TRIAGEM') ao carregar o hospital, e recalcular dinamicamente a `ocupacao_atual` de cada setor no array local.
- Criar ações `editarSetor` e `excluirSetor` integradas ao Axios (`api.put` e `api.delete`).

#### [MODIFY] `resources/js/views/DashboardView.vue`
- Conectar os eventos `@edit` e `@delete` do `AreaCard` às novas ações da store `hospitalStore`.

### 2. Novo Esquema NoSQL (Subset Pattern e Referência Estendida)

#### [MODIFY] `resources/js/stores/paciente.js`
- Na função `_normalizePaciente(p)`, iterar sobre `resumo_ultimas_visitas` (se existir) e converter os IDs do MongoDB (`{ $oid: "..." }`) para string usando o utilitário `mongoId`.

#### [MODIFY] `resources/js/components/setor/ModalNovoPaciente.vue`
- Estruturar o `payload` do Axios para enviar `dados_clinicos_fixos` e `contato` como objetos aninhados, conforme o esquema JSON revisado, em vez de propriedades planas soltas.

### 3. Seção de Resumo das Últimas Visitas

#### [NEW] `resources/js/components/prontuario/ResumoVisitas.vue`
- Componente para iterar sobre `resumo_ultimas_visitas`.
- Para cada visita, exibir os dados básicos (data, motivo) e um `<RouterLink :to="'/prontuario/' + visita.atendimento_id">` como gatilho de "Ver detalhadamente".

#### [MODIFY] `resources/js/views/ProntuarioView.vue`
- Importar e acoplar `<ResumoVisitas :visitas="pacienteView.resumo_ultimas_visitas" />` na coluna da esquerda (abaixo dos dados clínicos).

### 4. Simulação de Restrição de Perfil (Fluxo LOGIN.jpg)

#### [NEW] `resources/js/stores/app.js`
- Criar uma Pinia store contendo o estado `userRole` (padrão: `MEDICO`).

#### [MODIFY] `resources/js/components/layout/AppHeader.vue`
- Adicionar um seletor visual simples no header para alternar entre `MEDICO` e `RECEPCIONISTA`.

#### [MODIFY] `resources/js/views/ProntuarioView.vue` & `resources/js/components/dashboard/AreaCard.vue`
- Adicionar `v-if="appStore.userRole !== 'RECEPCIONISTA'"` ao botão "Nova evolução" e às opções de "Editar" e "Excluir" área.

---

## Verification Plan

### Manual Verification
- Recarregar o dashboard e verificar se a barra de ocupação está sendo calculada com base na contagem real de atendimentos ativos.
- Testar a deleção e edição simulada de um setor no Dashboard.
- Cadastrar um novo paciente e verificar no Network (ou Mongo) se o payload construiu os objetos `contato` e `dados_clinicos_fixos` corretamente.
- Acessar o Prontuário de um paciente que possua `resumo_ultimas_visitas` para visualizar a lista e clicar em "Ver detalhadamente" para garantir que o Router navega corretamente.
- Alternar o perfil para 'RECEPCIONISTA' no header e garantir que os botões clínicos desaparecem.
