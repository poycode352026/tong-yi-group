import { latestResourceChoices } from './resourceChoices.js';

export function levelModules(catalog, placement, selected = []) {
    const lookup = new Map(catalog.map((item) => [item.id, item]));
    const matches = (item) => item.kind === 'materials' && item.usage !== 'session';
    // Kaitan terdahulu tetap terlihat untuk dibaca atau dilepas secara eksplisit.
    return [...selected.map((id) => lookup.get(id) ?? { id, title: 'Modul tersimpan' }), ...latestResourceChoices(catalog, selected).filter(matches)];
}

export function selectedLevelModules(catalog, selected = []) {
    const byId = new Map(catalog.map((item) => [item.id, item]));
    const latest = new Map();
    for (const item of catalog) {
        if (item.kind !== 'materials' || item.usage === 'session') continue;
        const key = item.resource_id ?? item.id;
        if (!latest.has(key) || latest.get(key).revision < item.revision) latest.set(key, item);
    }
    return selected.map((id) => {
        const original = byId.get(id);
        const current = original && original.usage !== 'session' ? latest.get(original.resource_id ?? original.id) : original;
        return { ...(current ?? original ?? { id, title: 'Modul tersimpan' }), selection_id: id };
    });
}
