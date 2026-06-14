# Implementação da Readmissão de Pacientes

Este documento detalha o plano para permitir que pacientes previamente cadastrados (que já tiveram alta) possam ser readmitidos no hospital sem precisarem ser cadastrados novamente, evitando dados duplicados.

## Fluxo da Nova Feature (UX/UI)

1. No `SetorView`, o botão superior direito deixará de abrir diretamente o cadastro e passará a se chamar **"Admitir Paciente"**.
2. Ao clicar, o sistema abrirá o novo **`ModalBuscaPaciente.vue`**.
   - Neste modal, haverá um campo de busca: *"Buscar por CPF ou Nome..."*.
   - Abaixo, a lista de resultados encontrados exibirá os pacientes, com um botão **"Admitir"**.
   - Caso o paciente seja novo e não apareça na busca, haverá um botão em destaque: **"Não encontrou? Cadastrar Novo Paciente"**.
3. Se o usuário escolher "Cadastrar Novo", o `ModalNovoPaciente` (que já existe) será aberto.
4. Se o usuário clicar em "Admitir" em um paciente da busca, o sistema fechará a busca e abrirá o `ModalAdmitirPaciente` (que já existe), reaproveitando todo o fluxo de criação de `Atendimento` do backend!

## Proposed Changes

### Componentes / UI

#### [NEW] [ModalBuscaPaciente.vue](file:///c:/Users/mfs90/Documents/UFOPA/SEMESTRES/hospitall/HospitALL/resources/js/components/setor/ModalBuscaPaciente.vue)
- Criar este componente baseado no `BaseModal`.
- Fazer um request para `GET /api/pacientes` e implementar um filtro local (computed) baseado no texto digitado (CPF ou Nome).
- Emitir dois eventos: `@selecionado` (quando acha um paciente) e `@cadastrar` (quando clica em novo).

#### [MODIFY] [SetorView.vue](file:///c:/Users/mfs90/Documents/UFOPA/SEMESTRES/hospitall/HospitALL/resources/js/views/SetorView.vue)
- Importar o `ModalBuscaPaciente`.
- Alterar o evento do botão de "Novo Paciente" para abrir primeiramente a busca.
- Criar funções para gerenciar as aberturas sequenciais de modais (ex: `onSelecionarPacienteDaBusca(paciente)` -> abre `modalAdmitir`).

### Backend

- **Nenhuma alteração é necessária no backend!** 
O nosso `AtendimentoController` e o `POST /api/atendimentos` já esperam receber um `paciente_id` existente para criar uma nova internação. Tudo vai funcionar nativamente.

## User Review Required

> [!IMPORTANT]
> O que acha desse fluxo com a "etapa intermediária" de busca antes do cadastro, Gabriel? Se concordar com a navegação (Busca -> Seleciona Existente -> Admite **OU** Busca -> Não Acha -> Cadastra -> Admite), dê um "Aprovado" e eu implemento os modais! construiu os objetos `contato` e `dados_clinicos_fixos` corretamente.
- Acessar o Prontuário de um paciente que possua `resumo_ultimas_visitas` para visualizar a lista e clicar em "Ver detalhadamente" para garantir que o Router navega corretamente.
- Alternar o perfil para 'RECEPCIONISTA' no header e garantir que os botões clínicos desaparecem.
