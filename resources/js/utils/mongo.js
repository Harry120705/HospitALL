/**
 * Utilitários de manipulação do MongoDB
 */

/**
 * Normaliza e extrai o ID de um documento MongoDB.
 * Suporta formatos de string direta, propriedade _id, id, ou objetos { $oid: '...' }.
 */
export function mongoId(obj) {
  if (!obj) return null;
  if (typeof obj === 'string') return obj;
  if (typeof obj === 'object' && obj.$oid) return obj.$oid;
  
  const id = obj._id || obj.id;
  if (!id) return null;
  if (typeof id === 'string') return id;
  if (typeof id === 'object' && id.$oid) return id.$oid;
  return String(id);
}
