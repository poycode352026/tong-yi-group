<script setup>
defineProps({ modelValue: [String, Number], label: String, name: String, type: { type: String, default: 'text' }, error: String, hint: String, placeholder: String, rows: { type: Number, default: 3 }, min: [String, Number], max: [String, Number], step: [String, Number], list: String });
defineEmits(['update:modelValue']);
</script>
<template>
    <div class="min-w-0">
        <label :for="name" class="field-label">{{ label }}</label>
        <textarea v-if="type === 'textarea'" :id="name" :value="modelValue" :rows="rows" :placeholder="placeholder" :aria-invalid="!!error" :aria-describedby="error ? `${name}-error` : hint ? `${name}-hint` : undefined" class="admin-input resize-y leading-6" @input="$emit('update:modelValue', $event.target.value)"></textarea>
        <input v-else :id="name" :value="modelValue" :type="type" :min="min" :max="max" :step="step" :list="list" :placeholder="placeholder" :aria-invalid="!!error" :aria-describedby="error ? `${name}-error` : hint ? `${name}-hint` : undefined" class="admin-input" @input="$emit('update:modelValue', $event.target.value)" />
        <p v-if="hint" :id="`${name}-hint`" class="mt-2 text-xs leading-5 text-muted">{{ hint }}</p>
        <p v-if="error" :id="`${name}-error`" class="field-error">{{ error }}</p>
    </div>
</template>
