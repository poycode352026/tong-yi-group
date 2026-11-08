import test from 'node:test';
import assert from 'node:assert/strict';
import { automaticExams } from '../../resources/js/utils/automaticExams.js';
const placement = { program_id: 'p', subprogram_id: 's', level_id: 'l' };
const exam = { id: 'new', kind: 'assessments', is_current: true, scope: 'package', target: { ...placement, package_id: 'pack' } };
test('exams appear from their target without manual curriculum links and only current snapshots', () => {
    const rows = [exam, { ...exam, id: 'old', is_current: false }, { ...exam, id: 'other', target: { ...exam.target, level_id: 'other' } }, { ...exam, id: 'legacy', target: null }];
    assert.deepEqual(automaticExams(rows, placement, 'package', 'pack'), [exam]);
    assert.deepEqual(automaticExams(rows, placement, 'package', 'another'), []);
    assert.deepEqual(automaticExams(rows, placement, 'level'), []);
    assert.deepEqual(automaticExams(rows, null, 'package', 'pack'), []);
});
test('end-level exams do not appear as package exams and filtering leaves curriculum unchanged', () => {
    const level = { ...exam, scope: 'level', target: { ...placement, package_id: null } };
    const original = JSON.stringify([exam, level]);
    assert.deepEqual(automaticExams([exam, level], placement, 'level'), [level]);
    assert.deepEqual(automaticExams([exam, level], placement, 'package', 'pack'), [exam]);
    assert.equal(JSON.stringify([exam, level]), original);
});
