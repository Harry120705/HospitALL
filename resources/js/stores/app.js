import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAppStore = defineStore('app', () => {
  // Inicialmente MEDICO. Poderá ser alterado para 'RECEPCIONISTA'
  const userRole = ref('MEDICO');

  return { userRole };
});
