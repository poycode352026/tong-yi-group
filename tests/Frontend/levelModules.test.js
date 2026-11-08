import test from 'node:test';
import assert from 'node:assert/strict';
import { levelModules } from '../../resources/js/utils/levelModules.js';
const place = { program_id: 'p', subprogram_id: 's', level_id: 'l' };
const catalog = [
    { id: 'a1', resource_id: 'a', revision: 1, kind: 'materials', level_target: place },
    { id: 'a2', resource_id: 'a', revision: 2, kind: 'materials', level_target: place },
    { id: 'b', resource_id: 'b', revision: 1, kind: 'materials', level_target: { ...place, level_id: 'other' } },
    { id: 'old', resource_id: 'old', kind: 'materials', framework_level: 'HSK 1' },
    { id: 'session', resource_id: 'session', kind: 'materials', usage: 'session', level_target: place },
];
test('level modules are independent of HSK selection and allow reuse across levels and exclude session PDFs', () => {
    assert.deepEqual(levelModules(catalog, place).map((m) => m.id), ['a2', 'b', 'old']);
    assert.deepEqual(levelModules(catalog, { ...place, program_id: 'other' }).map((m) => m.id), ['a2', 'b', 'old']);
});
test('historical module selections are preserved without replacing a pinned version', () => {
    assert.deepEqual(levelModules(catalog, place, ['a1', 'old']).map((m) => m.id), ['a1', 'old', 'b']);
});

test('selected reference modules follow newest ready content and keep original link for detach', async () => {
    const { selectedLevelModules } = await import('../../resources/js/utils/levelModules.js');
    const old = { id: 'old', kind: 'materials', usage: 'reference', resource_id: 'module', revision: 1, attachments: [{ title: 'A' }] };
    const current = { ...old, id: 'new', revision: 2, attachments: [{ title: 'A' }, { title: 'B' }] };
    const selected = ['old'];
    const rows = selectedLevelModules([current, old], selected);
    assert.equal(rows[0].id, 'new');
    assert.equal(rows[0].selection_id, 'old');
    assert.equal(rows[0].attachments.length, 2);
    assert.deepEqual(selected, ['old']);
    assert.equal(old.attachments.length, 1);
});
test('session material stays pinned and missing module link is preserved', async () => {
    const { selectedLevelModules } = await import('../../resources/js/utils/levelModules.js');
    const session = { id: 'session-old', kind: 'materials', usage: 'session', resource_id: 'session', revision: 1 };
    const rows = selectedLevelModules([session, { ...session, id: 'session-new', revision: 2 }], ['session-old', 'missing']);
    assert.equal(rows[0].id, 'session-old');
    assert.equal(rows[1].selection_id, 'missing');
});
