// Katalog berisi versi siap dipakai. Riwayat tetap tersedia untuk membaca kaitan lama.
export function latestResourceChoices(catalog, selected = []) {
    const latest = new Map();
    const selectedResources = new Set(catalog.filter((item) => selected.includes(item.id)).map((item) => item.resource_id ?? item.id));
    for (const item of catalog) {
        const key = item.resource_id ?? item.id;
        if (selectedResources.has(key)) continue;
        if (!latest.has(key) || Number(item.revision) > Number(latest.get(key).revision)) latest.set(key, item);
    }
    return [...latest.values()];
}
