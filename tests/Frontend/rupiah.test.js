import test from 'node:test';
import assert from 'node:assert/strict';
import { formatRupiahInput, parseRupiahInput } from '../../resources/js/utils/rupiah.js';

test('Rupiah has Indonesian grouping and decimal separators without rounding', () => {
    assert.equal(formatRupiahInput('1250000.50'), '1.250.000,50');
    assert.equal(parseRupiahInput('Rp 1.250.000,50'), '1250000.50');
    assert.equal(formatRupiahInput('900719925474099312345.67'), '900.719.925.474.099.312.345,67');
    assert.equal(parseRupiahInput(formatRupiahInput('900719925474099312345.67')), '900719925474099312345.67');
});

test('empty is distinct from zero and typed decimal input is preserved', () => {
    assert.equal(formatRupiahInput(null), '');
    assert.equal(parseRupiahInput(''), '');
    assert.equal(parseRupiahInput('Rp 0'), '0');
    assert.equal(formatRupiahInput('0'), '0');
    assert.equal(parseRupiahInput('12,'), '12.');
    assert.equal(formatRupiahInput('12.'), '12,');
});

test('invalid, negative, and overprecision amounts are not silently corrected', () => {
    assert.equal(parseRupiahInput('-500'), '-500');
    assert.equal(formatRupiahInput('-500'), '-500');
    assert.equal(parseRupiahInput('abc'), 'abc');
    assert.equal(parseRupiahInput('1,234'), '1.234');
    assert.equal(formatRupiahInput('1.234'), '1,234');
});
