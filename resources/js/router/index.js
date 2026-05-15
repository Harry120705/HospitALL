import { createRouter, createWebHistory } from 'vue-router';
import DashboardView from '@/views/DashboardView.vue';

/**
 * Regex que aceita qualquer string de ID:
 * - IDs MongoDB: 24 caracteres hexadecimais (ex: 6823a1f9e3b0c44d5f8e12ab)
 * - IDs de desenvolvimento (slugs como 'setor_ped')
 * - UUIDs padrão
 */
const MONGO_ID_REGEX = '[a-fA-F0-9]{24}|[\\w-]+';

const routes = [
  {
    path: '/',
    name: 'dashboard',
    component: DashboardView,
    meta: { title: 'Áreas Hospitalares — HospitALL' },
  },
  {
    path: `/setor/:id(${MONGO_ID_REGEX})`,
    name: 'setor',
    component: () => import('@/views/SetorView.vue'),
    meta: { title: 'Setor — HospitALL' },
  },
  {
    path: `/prontuario/:atendimentoId(${MONGO_ID_REGEX})`,
    name: 'prontuario',
    component: () => import('@/views/ProntuarioView.vue'),
    meta: { title: 'Prontuário — HospitALL' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
});

router.afterEach((to) => {
  document.title = to.meta.title || 'HospitALL';
});

export default router;
