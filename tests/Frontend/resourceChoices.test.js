import test from 'node:test';
import assert from 'node:assert/strict';
import { latestResourceChoices } from '../../resources/js/utils/resourceChoices.js';

test('choices show latest snapshot per source regardless of catalog order', () => {
    const catalog = [{ id: 'h2', resource_id: 'h', revision: 2 }, { id: 'b1', resource_id: 'b', revision: 1 }, { id: 'h1', resource_id: 'h', revision: 1 }];
    assert.deepEqual(latestResourceChoices(catalog).map((item) => item.id), ['h2', 'b1']);
    assert.equal(catalog.length, 3); // Snapshot lama tetap tersedia untuk lookup.
});

test('a linked historical snapshot excludes the entire source from new choices', () => {
    const catalog = [{ id: 'h1', resource_id: 'h', revision: 1 }, { id: 'h2', resource_id: 'h', revision: 2 }, { id: 'b1', resource_id: 'b', revision: 1 }];
    assert.deepEqual(latestResourceChoices(catalog, ['h1']).map((item) => item.id), ['b1']);
});
