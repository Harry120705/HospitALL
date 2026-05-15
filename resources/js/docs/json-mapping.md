# HospitALL — Guia de Mapeamento JSON → Componentes Vue

## Por que NoSQL + Vue é uma combinação poderosa?

O MongoDB armazena documentos JSON com estrutura flexível. O Vue.js renderiza interfaces reativas a partir de objetos JavaScript. A combinação é natural: **os documentos do MongoDB se tornam diretamente o estado reativo dos componentes Vue**, sem necessidade de transformações complexas.

---

## Coleção HOSPITAIS → Dashboard

```json
// MongoDB: HOSPITAIS
{
  "_id": "ObjectId(...)",
  "nome": "Hospital São Lucas",
  "unidade": "Unidade Centro",
  "setores": [
    {
      "_id": "setor_ped",
      "nome": "Pediatria",
      "descricao": "Atendimento clínico infantil de 0 a 12 anos",
      "icone": "baby",
      "ocupacao_atual": 18,
      "capacidade_maxima": 24
    }
    // ... outros setores como subdocumentos do array
  ]
}
```

**Mapeamento Vue:**
- `hospital.setores[]` → Grid de `<AreaCard>` no `DashboardView`
- `setor.ocupacao_atual / setor.capacidade_maxima` → Barra de ocupação dinâmica
- Cor da barra: azul (< 60%), laranja (60–90%), vermelho (> 90%)
- `setor.icone` → Componente Lucide via mapa de ícones no `AreaCard`

> **Vantagem NoSQL:** Adicionar um novo campo ao setor (ex: `responsavel_medico`) não quebra nenhum componente — o Vue ignora campos desconhecidos.

---

## Coleção PACIENTES → Prontuário (Painel Esquerdo)

```json
// MongoDB: PACIENTES
{
  "_id": "ObjectId(...)",
  "nome": "Lucas Almeida Santos",
  "data_nascimento": "2018-03-15",
  "contato": {
    "nome": "Maria Santos",
    "telefone": "(91) 99999-0001",
    "parentesco": "Mãe"
  },
  "dados_clinicos_fixos": {
    "tipo_sanguineo": "A+",
    "alergias": ["Penicilina"],
    "peso_kg": 22,
    "altura_cm": 115
    // ← Adicione novos campos aqui sem quebrar o layout!
  }
}
```

**Mapeamento Vue:**
- `paciente.nome`, `paciente.data_nascimento` → `<DadosGerais>`
- `paciente.contato{}` → Contato de emergência em `<DadosGerais>`
- `paciente.dados_clinicos_fixos{}` → `<DadosClinicos>`
  - `.tipo_sanguineo` → Chip vermelho
  - `.alergias[]` → Tags de alerta
  - `.peso_kg`, `.altura_cm` → Chips informativos

---

## Coleção ATENDIMENTOS → Prontuário (Painel Direito)

```json
// MongoDB: ATENDIMENTOS
{
  "_id": "ObjectId(...)",
  "paciente_id": "ObjectId(...)",
  "hospital_id": "ObjectId(...)",
  "status": "internado",
  "protocolo_manchester": "muito_urgente",
  "alocacao_leito": {
    "numero": "P-01",
    "setor": "Pediatria",
    "status": "ocupado"
  },
  "dados_iniciais": {
    "queixa_principal": "Febre alta persistente"
  },

  // ─── A JOIA DO PROJETO: Arrays de subdocumentos NoSQL ───
  "evolucoes_medicas": [
    {
      "id": "ev_001",
      "data_hora": "2026-05-13T09:30:00",
      "medico": "Dr. Rafael Costa",
      "crm": "145.220",
      "tipo": "evolucao",
      "descricao": "Paciente apresenta febre de 39.5°C..."
      // ← Adicione "imagens", "exames_anexados", "assinatura_digital"
      //    sem alterar o componente de timeline!
    }
  ],

  "administracao_enfermagem": [
    {
      "id": "enf_001",
      "data_hora": "2026-05-13T10:00:00",
      "enfermeiro": "Enf. Carla Menezes",
      "tipo": "medicacao",
      "descricao": "Administrado Dipirona 500mg IV..."
    }
  ]
}
```

**Mapeamento Vue:**
| Campo MongoDB | Componente Vue | Comportamento |
|---|---|---|
| `evolucoes_medicas[]` | `<TimelineEvolution>` | Renderiza cada item como card na timeline |
| `administracao_enfermagem[]` | `<TimelineEnfermagem>` | Timeline separada com dots teal |
| `protocolo_manchester` | Badge colorido no header | Muda cor automaticamente |
| `dados_iniciais.queixa_principal` | Card destacado em amarelo | Sempre visível no topo |
| `alocacao_leito.numero` | Badge "Leito P-01" | No aside do prontuário |

---

## Como Adicionar Novos Módulos ao Prontuário (Sem Quebrar Nada)

A arquitetura NoSQL + Vue foi pensada para **extensão sem ruptura**. Exemplos:

### Exemplo 1: Adicionar módulo de Exames de Imagem

**No MongoDB** (backend Harry), adicione ao documento de ATENDIMENTO:
```json
"exames_imagem": [
  {
    "id": "img_001",
    "data_hora": "2026-05-13T11:00:00",
    "tipo": "Raio-X de Tórax",
    "url_imagem": "https://storage.../rx001.jpg",
    "laudo": "Sem alterações significativas."
  }
]
```

**No Frontend** (Arquivo novo `TimelineImagem.vue`):
```vue
<TimelineImagem :exames="atendimento.exames_imagem ?? []" />
```

**Na `ProntuarioView.vue`**, adicione uma linha:
```vue
<TimelineImagem :exames="atendimento.exames_imagem ?? []" />
```

> ✅ Os outros componentes (`TimelineEvolution`, `TimelineEnfermagem`) continuam funcionando sem modificação.

---

### Exemplo 2: Adicionar campo ao dados_clinicos_fixos

**No MongoDB**, o novo documento do paciente agora inclui:
```json
"dados_clinicos_fixos": {
  "tipo_sanguineo": "A+",
  "alergias": ["Penicilina"],
  "comorbidades": ["Diabetes Tipo 1", "Hipertensão"]  // ← NOVO
}
```

**No `DadosClinicos.vue`**, adicione o bloco:
```vue
<div class="dc-section" v-if="clinicos.comorbidades?.length">
  <p class="dc-section__label">Comorbidades</p>
  <div class="dc-tags">
    <span v-for="c in clinicos.comorbidades" :key="c" class="dc-tag">{{ c }}</span>
  </div>
</div>
```

> ✅ Pacientes antigos (sem `comorbidades`) continuam sendo exibidos corretamente — `?.length` retorna `0` e o bloco não renderiza.

---

## Stores Pinia como Camada de Abstração

```
MongoDB JSON → API REST (Laravel) → Axios → Pinia Store → Componente Vue
```

As **stores Pinia** funcionam como a única fonte da verdade. Quando o Harry terminar os endpoints:

1. Mude `useMock.value = false` em `stores/hospital.js` e `stores/paciente.js`
2. Configure `VITE_API_BASE_URL` no `.env`
3. Pronto — todos os componentes recebem dados reais automaticamente

---

## Configuração de Variáveis de Ambiente

```bash
# .env (copie do .env.example)
VITE_API_BASE_URL=http://IP_DO_HARRY:8000/api
```

O arquivo `services/api.js` lê essa variável via `import.meta.env.VITE_API_BASE_URL`.
