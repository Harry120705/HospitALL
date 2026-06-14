<template>
  <div class="card dados-gerais">
    <div class="dg-header">
      <div class="dg-avatar">{{ initials }}</div>
      <div>
        <h2 class="dg-nome">{{ paciente.nome }}</h2>
        <p class="dg-sub">{{ idade }} anos · {{ paciente.data_nascimento ? formatDate(paciente.data_nascimento) : '—' }}</p>
      </div>
    </div>

    <div class="dg-divider"></div>

    <dl class="dg-list">
      <div class="dg-item">
        <dt><User :size="13" /> Contato de Emergência</dt>
        <dd>{{ contatoNome }}</dd>
      </div>
      <div class="dg-item">
        <dt><Phone :size="13" /> Telefone</dt>
        <dd>{{ contatoTelefone }}</dd>
      </div>
    </dl>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { User, Phone } from 'lucide-vue-next';
import { formatDate, calcularIdade } from '@/utils/date.js';

const props = defineProps({
  paciente: { type: Object, required: true },
});

const initials = computed(() =>
  (props.paciente.nome ?? '?').split(' ').slice(0, 2).map((n) => n[0]).join('').toUpperCase()
);

const contatoNome = computed(() =>
  props.paciente.contato_emergencia_nome
  ?? props.paciente.contato?.nome
  ?? '—'
);

const contatoTelefone = computed(() =>
  props.paciente.contato_emergencia_telefone
  ?? props.paciente.contato?.telefone
  ?? props.paciente.telefone
  ?? '—'
);

const contatoParentesco = computed(() =>
  props.paciente.contato?.parentesco ?? '—'
);

const idade = computed(() => calcularIdade(props.paciente.data_nascimento));
</script>

<style scoped>
.dados-gerais { padding: 20px; }

.dg-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 16px;
}

.dg-avatar {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: linear-gradient(135deg, var(--color-primary) 0%, #0284C7 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 17px;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
}

.dg-nome {
  font-size: 17px;
  font-weight: 700;
  color: var(--color-text-primary);
  line-height: 1.2;
}

.dg-sub {
  font-size: 12px;
  color: var(--color-text-secondary);
  margin-top: 2px;
}

.dg-divider {
  height: 1px;
  background: var(--color-border);
  margin: 0 0 14px;
}

.dg-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.dg-item { display: flex; flex-direction: column; gap: 2px; }

dt {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--color-text-muted);
}

dd {
  font-size: 14px;
  font-weight: 500;
  color: var(--color-text-primary);
  margin-left: 0;
}
</style>
