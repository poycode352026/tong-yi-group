<script setup>
import { computed, nextTick } from 'vue';
import { formatRupiahInput, parseRupiahInput } from '../utils/rupiah';
const props = defineProps({ modelValue: [String, Number], label: String, name: String, error: String, placeholder: { type: String, default: 'Belum ditetapkan' } });
const emit = defineEmits(['update:modelValue']);
const display = computed(() => formatRupiahInput(props.modelValue));
async function input(event) {
    const element = event.target;
    const tokensBeforeCaret = (element.value.slice(0, element.selectionStart).match(/[\d,]/g) ?? []).length;
    const raw = parseRupiahInput(element.value);
    emit('update:modelValue', raw);
    await nextTick();
    const formatted = formatRupiahInput(raw);
    element.value = formatted;
    let caret = 0;
    let tokens = 0;
    while (caret < formatted.length && tokens < tokensBeforeCaret) {
        if (/[\d,]/.test(formatted[caret])) tokens++;
        caret++;
    }
    element.setSelectionRange(caret, caret);
}
</script>
<template>
    <div class="min-w-0"><label :for="name" class="field-label">{{ label }}</label><div class="relative"><span aria-hidden="true" class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-medium text-muted">Rp</span><input :id="name" :value="display" type="text" inputmode="decimal" autocomplete="off" :placeholder="placeholder" :aria-invalid="!!error" :aria-describedby="error ? `${name}-error` : `${name}-hint`" class="admin-input pl-11 tabular-nums" @input="input" /></div><p :id="`${name}-hint`" class="mt-2 text-[10px] leading-5 text-muted">Titik ribuan, koma desimal.</p><p v-if="error" :id="`${name}-error`" class="field-error">{{ error }}</p></div>
</template>
