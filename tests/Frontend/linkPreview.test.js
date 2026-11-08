import test from 'node:test';
import assert from 'node:assert/strict';
import { linkPreview } from '../../resources/js/utils/linkPreview.js';
test('link preview shows HTTPS destination without downloading remote content', () => {
    assert.deepEqual(linkPreview(' https://example.org/hsk-2?q=book#reading '), { href: 'https://example.org/hsk-2?q=book#reading', domain: 'example.org', path: '/hsk-2?q=book#reading' });
});
test('invalid URLs, active schemes and embedded credentials have no preview', () => {
    for (const value of ['', null, 'invalid', 'http://example.org', 'javascript:alert(1)', 'data:text/html,test', 'https://user:secret@example.org']) assert.equal(linkPreview(value), null);
});
