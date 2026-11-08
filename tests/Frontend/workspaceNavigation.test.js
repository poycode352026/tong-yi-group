import test from 'node:test';
import assert from 'node:assert/strict';
import { isWorkspaceDestination } from '../../resources/js/utils/workspaceNavigation.js';
const origin = 'http://127.0.0.1:8000';
test('internal tab URLs are recognized as paths, absolute strings and URL objects', () => {
    for (const value of ['/admin', '/admin/programs/create?from=tab', `${origin}/admin/learning/frameworks`, new URL(`${origin}/admin/settings/menus`)]) assert.equal(isWorkspaceDestination(value, origin), true);
});
test('leaving workspace and authentication URLs do not suppress exit protection', () => {
    for (const value of ['/', '/administrator', '/admin/login', '/admin/logout?next=/admin', 'https://other.test/admin', '//other.test/admin', '', null]) assert.equal(isWorkspaceDestination(value, origin), false);
});
