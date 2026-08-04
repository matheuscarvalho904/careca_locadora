<?php

it('mantém source_damage_mark_id sem foreign key autorreferencial', function (): void {
    $migration = file_get_contents(
        database_path(
            'migrations/2026_08_03_070000_create_visual_damage_map_tables.php'
        )
    );

    $normalized = preg_replace('/\s+/', '', $migration);

    expect($normalized)
        ->toContain("\$table->uuid('source_damage_mark_id')->nullable();")
        ->toContain(
            "\$table->index('source_damage_mark_id','rental_damage_marks_source_idx');"
        )
        ->not->toContain(
            "\$table->foreignUuid('source_damage_mark_id')"
        );
});

it('fornece rotina segura para remover tabelas parciais', function (): void {
    $repair = file_get_contents(
        base_path(
            'scripts/repair-partial-visual-damage-map-migration-11.1.1.php'
        )
    );

    expect($repair)
        ->toContain("where('migration', \$migration)")
        ->toContain("Schema::drop(\$table)")
        ->toContain("'rental_damage_photos'")
        ->toContain("'rental_damage_marks'")
        ->toContain("'inspection_diagram_views'")
        ->toContain("'inspection_diagram_templates'");
});
