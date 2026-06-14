/**
 * Utilitários de Data e Hora com suporte a fuso horário local
 */

/**
 * Analisa uma string de data no formato YYYY-MM-DD no fuso horário local do navegador,
 * evitando problemas de fuso horário (deslocamento para o dia anterior).
 */
export function parseLocalDate(dateStr) {
  if (!dateStr) return null;
  if (dateStr instanceof Date) return dateStr;
  
  const match = String(dateStr).match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (match) {
    const year = parseInt(match[1], 10);
    const month = parseInt(match[2], 10) - 1;
    const day = parseInt(match[3], 10);
    return new Date(year, month, day);
  }
  return new Date(dateStr);
}

/**
 * Formata uma string de data para o padrão brasileiro DD/MM/AAAA.
 */
export function formatDate(dateStr) {
  const parsed = parseLocalDate(dateStr);
  if (!parsed || isNaN(parsed.getTime())) return '—';
  return parsed.toLocaleDateString('pt-BR');
}

/**
 * Formata uma data com hora para o padrão brasileiro DD/MM/AAAA HH:MM.
 */
export function formatDateTime(dateStr) {
  if (!dateStr) return '—';
  const parsed = new Date(dateStr);
  if (isNaN(parsed.getTime())) return '—';
  return parsed.toLocaleString('pt-BR', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}

/**
 * Calcula a idade de uma pessoa a partir de uma data de nascimento com precisão em anos.
 */
export function calcularIdade(dateStr) {
  if (!dateStr) return '—';
  const birth = parseLocalDate(dateStr);
  if (!birth || isNaN(birth.getTime())) return '—';
  
  const today = new Date();
  let age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--;
  }
  return age;
}
