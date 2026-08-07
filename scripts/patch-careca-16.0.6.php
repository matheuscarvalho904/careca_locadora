<?php
declare(strict_types=1);

$root = rtrim($argv[1] ?? 'C:\dev\careca-locadora', "\\/");

function fp(string $root, string $rel): string {
    return $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
}
function rd(string $path): string {
    if (!is_file($path)) { fwrite(STDERR, "[ERRO] Arquivo não encontrado: $path\n"); exit(2); }
    $v = file_get_contents($path);
    if ($v === false) { fwrite(STDERR, "[ERRO] Falha ao ler: $path\n"); exit(2); }
    return $v;
}
function wr(string $path, string $v): void {
    if (file_put_contents($path, $v) === false) { fwrite(STDERR, "[ERRO] Falha ao gravar: $path\n"); exit(3); }
}

echo "\nCareca Locadora - Patch 16.0.6\n";
echo "Foto publica + billing_unit + reserva\n\n";

if (!is_file(fp($root, 'artisan'))) { fwrite(STDERR, "[ERRO] Projeto Laravel não encontrado.\n"); exit(1); }

// 1) Corrige contrato da cotação.
$p = fp($root, 'app/Services/Rentals/RentalCommercialPricingService.php');
$s = rd($p);

$old = "'rate_plan'=>['id'=>\$rate->id,'code'=>\$rate->code,'name'=>\$rate->name]";
$new = "'rate_plan'=>['id'=>\$rate->id,'code'=>\$rate->code,'name'=>\$rate->name,'billing_unit'=>\$rate->billing_unit,'unit_value'=>(float)\$rate->unit_value,'deposit_value'=>(float)\$rate->deposit_value]";

if (str_contains($s, $old)) {
    $s = str_replace($old, $new, $s);
    echo "[CORRIGIDO] rate_plan agora expõe billing_unit/unit_value.\n";
} elseif (!str_contains($s, "'billing_unit'=>\$rate->billing_unit") && !str_contains($s, "'billing_unit' => \$rate->billing_unit")) {
    fwrite(STDERR, "[ERRO] Estrutura rate_plan não encontrada com segurança.\n");
    exit(4);
} else {
    echo "[OK] rate_plan já contém billing_unit.\n";
}
wr($p, $s);

// 2) Corrige foto pública.
$p = fp($root, 'app/Http/Controllers/Api/PublicVehicleController.php');
$s = rd($p);

if (str_contains($s, "'path' => \$photo->path")) {
    $s = str_replace("'path' => \$photo->path", "'path' => \$photo->file_path", $s);
    echo "[CORRIGIDO] API pública usa AssetPhoto.file_path.\n";
}

$s = str_replace(
    "'name' => \$vehicle->branch?->trade_name\n                        ?: \$vehicle->branch?->name",
    "'name' => \$vehicle->branch?->name",
    $s
);
$s = str_replace(
    "'name' => \$vehicle->branch?->trade_name ?: \$vehicle->branch?->name",
    "'name' => \$vehicle->branch?->name",
    $s
);

if (!str_contains($s, "->filter(fn (\$photo): bool => filled(\$photo->file_path))")) {
    $s = str_replace(
        "'photos' => \$vehicle->photos\n                    ->sortByDesc('is_featured')",
        "'photos' => \$vehicle->photos\n                    ->filter(fn (\$photo): bool => filled(\$photo->file_path))\n                    ->sortByDesc('is_featured')",
        $s
    );
}
wr($p, $s);

// 3) Mantém frontend tolerante a path nulo.
$p = fp($root, 'resources/js/pages/public/vehicle-show.tsx');
if (is_file($p)) {
    $s = rd($p);
    $s = str_replace(
        "photos?: { path: string; featured: boolean }[];",
        "photos?: { path: string | null; featured: boolean }[];",
        $s
    );
    $s = str_replace(
        "const photos = vehicle.photos ?? [];",
        "const photos = (vehicle.photos ?? []).filter(\n        (photo) => typeof photo?.path === 'string' && photo.path.trim() !== '',\n    );",
        $s
    );
    wr($p, $s);
}

echo "\n[OK] Patch 16.0.6 aplicado.\n";
