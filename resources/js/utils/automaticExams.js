// Tujuan pada ujian menjadi sumber penempatan, tanpa mengubah isian kurikulum.
export function automaticExams(catalog, placement, scope, packageId = null) {
    if (!placement) return [];
    return catalog.filter((item) => {
        const target = item.target;
        return item.kind === 'assessments' && item.is_current === true && item.scope === scope && target
            && target.program_id === placement.program_id
            && target.subprogram_id === placement.subprogram_id
            && target.level_id === placement.level_id
            && (target.package_id ?? null) === (scope === 'package' ? packageId : null);
    });
}
