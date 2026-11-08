// Gunakan string agar nilai uang tidak dibulatkan oleh floating point JavaScript.
export function formatRupiahInput(value) {
    if (value === null || value === undefined || value === '') return '';
    const raw = String(value);
    if (!/^\d+(\.\d*)?$/.test(raw)) return raw;
    const [whole, decimal] = raw.split('.');
    const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return decimal === undefined ? grouped : `${grouped},${decimal}`;
}

export function parseRupiahInput(value) {
    const clean = value.replace(/^\s*Rp\s*/i, '').replace(/\s/g, '');
    if (clean === '') return '';
    // Notasi Indonesia: titik ribuan, koma desimal. Isian tidak valid tetap
    // diteruskan untuk validasi, bukan diubah diam-diam menjadi nominal lain.
    if (!/^[\d.]+(,\d*)?$/.test(clean)) return clean;
    const normalized = clean.replace(/\./g, '').replace(',', '.');
    if (!/^\d+(\.\d*)?$/.test(normalized)) return clean;
    return normalized.replace(/^0+(?=\d)/, '');
}
