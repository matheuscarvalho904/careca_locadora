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

echo "\nCareca Locadora - Patch 16.0.7\n";
echo "Fechamento final do contrato da reserva pública\n\n";

if (!is_file(fp($root, 'artisan'))) {
    fwrite(STDERR, "[ERRO] Projeto Laravel não encontrado.\n");
    exit(1);
}

$p = fp($root, 'app/Services/Rentals/PublicReservationService.php');
$s = rd($p);

$changes = 0;

// item_additional_value: deriva diretamente dos itens comerciais retornados
$old = "'item_additional_value' =>\n                    \$quote['items_total'],";
$new = "'item_additional_value' =>\n                    round((float) collect(\$quote['commercial_items'] ?? [])->sum('total'), 2),";

if (str_contains($s, $old)) {
    $s = str_replace($old, $new, $s);
    $changes++;
    echo "[CORRIGIDO] items_total removido; adicionais calculados de commercial_items.\n";
} elseif (str_contains($s, "\$quote['items_total']")) {
    $s = str_replace(
        "\$quote['items_total']",
        "round((float) collect(\$quote['commercial_items'] ?? [])->sum('total'), 2)",
        $s
    );
    $changes++;
    echo "[CORRIGIDO] Referência legada a items_total substituída.\n";
} else {
    echo "[OK] PublicReservationService já não usa items_total.\n";
}

// coupon_discount defensivo
if (str_contains($s, "\$quote['coupon_discount']")) {
    $s = str_replace(
        "\$quote['coupon_discount']",
        "(\$quote['coupon_discount'] ?? 0)",
        $s
    );
    $changes++;
    echo "[CORRIGIDO] coupon_discount agora possui fallback zero.\n";
}

// deposit_value defensivo
if (str_contains($s, "\$quote['deposit_value']")) {
    $s = str_replace(
        "\$quote['deposit_value']",
        "(\$quote['deposit_value'] ?? 0)",
        $s
    );
    $changes++;
    echo "[CORRIGIDO] deposit_value agora possui fallback zero.\n";
}

// metadata coupon: o pricing atual não retorna chave coupon.
if (str_contains($s, "'coupon' => \$quote['coupon'],")) {
    $s = str_replace(
        "'coupon' => \$quote['coupon'],",
        "'coupon' => \$quote['coupon'] ?? null,",
        $s
    );
    $changes++;
    echo "[CORRIGIDO] metadata coupon agora aceita ausência da chave.\n";
} elseif (str_contains($s, "\$quote['coupon']") && !str_contains($s, "\$quote['coupon'] ?? null")) {
    $s = str_replace("\$quote['coupon']", "\$quote['coupon'] ?? null", $s);
    $changes++;
    echo "[CORRIGIDO] Acesso legado a coupon protegido.\n";
}

wr($p, $s);

// validações
$check = rd($p);

if (str_contains($check, "\$quote['items_total']")) {
    fwrite(STDERR, "[ERRO] Ainda existe acesso a items_total.\n");
    exit(10);
}

if (str_contains($check, "'coupon' => \$quote['coupon'],")) {
    fwrite(STDERR, "[ERRO] Acesso não protegido a coupon ainda existe.\n");
    exit(11);
}

echo "\n[OK] Patch 16.0.7 aplicado. Alterações: {$changes}\n";
