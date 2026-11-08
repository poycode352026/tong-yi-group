<script setup>
import { computed } from 'vue';
const props = defineProps({ form: Object, programs: Array });
const program = computed(() => props.programs?.find((p) => p.id === props.form.target.program_id));
const sub = computed(() => program.value?.subprograms.find((s) => s.id === props.form.target.subprogram_id));
const level = computed(() => sub.value?.levels.find((l) => l.id === props.form.target.level_id));
const summary = computed(() => {
    if (!level.value) return '';
    let start = 1, end = 0;
    if (props.form.scope === 'package') {
        let found = false;
        for (const p of level.value.packages) { end += p.sessions; if (p.id === props.form.target.package_id) { found = true; break; } start = end + 1; }
        if (!found) return '';
    } else end = level.value.packages.reduce((sum, p) => sum + p.sessions, 0);
    return end >= start ? `Cakupan sesi ${start}–${end}. Diberikan setelah sesi ${end}.` : 'Susun paket dan sesi level terlebih dahulu.';
});
function changeProgram() { props.form.target.program_revision = program.value?.revision ?? null; props.form.target.subprogram_id = ''; props.form.target.level_id = ''; props.form.target.package_id = ''; }
function changeSub() { props.form.target.level_id = ''; props.form.target.package_id = ''; }
const fields = computed(() => [
    { key: 'program_id', label: 'Program', options: props.programs ?? [], change: changeProgram },
    { key: 'subprogram_id', label: 'Subprogram', options: program.value?.subprograms ?? [], change: changeSub },
    { key: 'level_id', label: 'Level', options: sub.value?.levels ?? [], change: () => { props.form.target.package_id = ''; } },
]);
</script>
<template>
    <section class="learning-panel"><div class="learning-panel-heading"><h2>Tujuan ujian</h2></div><div class="space-y-5 p-6">
        <p v-if="!programs?.length" class="text-sm text-muted">Belum ada program. Susun program dan level di Program Akademik terlebih dahulu.</p>
        <div class="grid min-w-0 gap-5 sm:grid-cols-2"><div v-for="field in fields" :key="field.key" class="min-w-0"><label :for="`exam-${field.key}`" class="field-label">{{ field.label }}</label><select :id="`exam-${field.key}`" v-model="form.target[field.key]" class="admin-input min-w-0 max-w-full" @change="field.change"><option value="">Pilih {{ field.label.toLowerCase() }}</option><option v-for="item in field.options" :key="item.id" :value="item.id">{{ item.name || 'Belum diberi nama' }}</option></select><p v-if="form.errors[`target.${field.key}`]" class="field-error">{{ form.errors[`target.${field.key}`] }}</p></div>
        <div class="min-w-0"><label for="exam-scope" class="field-label">Jenis ujian</label><select id="exam-scope" v-model="form.scope" class="admin-input min-w-0 max-w-full" @change="form.target.package_id = ''"><option value="package">Ujian Akhir Paket</option><option value="level">Ujian Akhir Level</option></select></div>
        <div v-if="form.scope === 'package'" class="min-w-0"><label for="exam-package" class="field-label">Paket</label><select id="exam-package" v-model="form.target.package_id" class="admin-input min-w-0 max-w-full" :disabled="!level"><option value="">Pilih paket</option><option v-for="p in level?.packages ?? []" :key="p.id" :value="p.id">{{ p.name }}</option></select><p v-if="form.errors['target.package_id']" class="field-error">{{ form.errors['target.package_id'] }}</p></div></div>
        <div v-if="summary" class="rounded-xl bg-brand-soft p-4 text-sm"><p class="font-semibold text-brand">{{ program.name }} → {{ sub.name }} → {{ level.name }}</p><p class="mt-2 text-muted">{{ summary }}</p></div>
        <p v-if="form.errors.target" class="field-error">{{ form.errors.target }}</p>
    </div></section>
</template>
