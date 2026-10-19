/**
 * Formatea una fecha al formato día/mes/año (ej. 19/10/2026).
 *
 * Acepta valores tipo `date` (`2026-10-19`), `datetime`
 * (`2026-10-19 08:30:00`) e ISO 8601 (`2026-10-19T08:30:00.000000Z`).
 * Se parsea como texto para evitar el corrimiento de un día por zona horaria.
 *
 * @param {string|Date|null|undefined} value
 * @returns {string} Fecha en formato `dd/mm/yyyy`; `-` si viene vacía; o el
 *                   valor original si no se puede interpretar.
 */
export function formatFecha(value) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    if (value instanceof Date) {
        if (Number.isNaN(value.getTime())) return '-';
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(value.getDate())}/${pad(value.getMonth() + 1)}/${value.getFullYear()}`;
    }

    const match = String(value).trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (match) {
        return `${match[3]}/${match[2]}/${match[1]}`;
    }

    return String(value);
}
