<?php
declare(strict_types=1);

$projectRoot = $argv[1] ?? dirname(__DIR__);
$filamentRoot = $projectRoot . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Filament';

if (! is_dir($filamentRoot)) {
    fwrite(STDERR, "Diretório Filament não encontrado: {$filamentRoot}\n");
    exit(1);
}

function refactorFile(string $path): bool
{
    $code = file_get_contents($path);

    if ($code === false || ! str_contains($code, '$s')) {
        return false;
    }

    $tokens = token_get_all($code);
    $changed = false;
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        if (! is_array($tokens[$i]) || ! in_array($tokens[$i][0], [T_FN, T_FUNCTION], true)) {
            continue;
        }

        $isArrow = $tokens[$i][0] === T_FN;
        $openParen = null;

        for ($j = $i + 1; $j < $count; $j++) {
            if ($tokens[$j] === '(') {
                $openParen = $j;
                break;
            }
            if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                break;
            }
        }

        if ($openParen === null) {
            continue;
        }

        $depth = 0;
        $closeParen = null;
        $parameterVariables = [];

        for ($j = $openParen; $j < $count; $j++) {
            if ($tokens[$j] === '(') {
                $depth++;
            } elseif ($tokens[$j] === ')') {
                $depth--;
                if ($depth === 0) {
                    $closeParen = $j;
                    break;
                }
            } elseif ($depth === 1 && is_array($tokens[$j]) && $tokens[$j][0] === T_VARIABLE) {
                $parameterVariables[] = $j;
            }
        }

        if ($closeParen === null || count($parameterVariables) !== 1) {
            continue;
        }

        $parameterIndex = $parameterVariables[0];

        if ($tokens[$parameterIndex][1] !== '$s') {
            continue;
        }

        $tokens[$parameterIndex][1] = '$state';
        $changed = true;

        if ($isArrow) {
            $doubleArrow = null;

            for ($j = $closeParen + 1; $j < $count; $j++) {
                if (is_array($tokens[$j]) && $tokens[$j][0] === T_DOUBLE_ARROW) {
                    $doubleArrow = $j;
                    break;
                }
                if ($tokens[$j] === ';' || $tokens[$j] === '{') {
                    break;
                }
            }

            if ($doubleArrow === null) {
                continue;
            }

            $paren = $bracket = $brace = 0;

            for ($j = $doubleArrow + 1; $j < $count; $j++) {
                $current = $tokens[$j];

                if ($current === '(') $paren++;
                elseif ($current === ')') {
                    if ($paren === 0 && $bracket === 0 && $brace === 0) break;
                    $paren--;
                } elseif ($current === '[') $bracket++;
                elseif ($current === ']') {
                    if ($bracket === 0 && $paren === 0 && $brace === 0) break;
                    $bracket--;
                } elseif ($current === '{') $brace++;
                elseif ($current === '}') {
                    if ($brace === 0 && $paren === 0 && $bracket === 0) break;
                    $brace--;
                } elseif (($current === ',' || $current === ';') && $paren === 0 && $bracket === 0 && $brace === 0) {
                    break;
                }

                if (is_array($current) && $current[0] === T_VARIABLE && $current[1] === '$s') {
                    $tokens[$j][1] = '$state';
                }
            }
        } else {
            $openBrace = null;

            for ($j = $closeParen + 1; $j < $count; $j++) {
                if ($tokens[$j] === '{') {
                    $openBrace = $j;
                    break;
                }
                if ($tokens[$j] === ';') {
                    break;
                }
            }

            if ($openBrace === null) {
                continue;
            }

            $braceDepth = 0;

            for ($j = $openBrace; $j < $count; $j++) {
                if ($tokens[$j] === '{') $braceDepth++;
                elseif ($tokens[$j] === '}') {
                    $braceDepth--;
                    if ($braceDepth === 0) break;
                }

                if (is_array($tokens[$j]) && $tokens[$j][0] === T_VARIABLE && $tokens[$j][1] === '$s') {
                    $tokens[$j][1] = '$state';
                }
            }
        }
    }

    if (! $changed) {
        return false;
    }

    $rebuilt = '';
    foreach ($tokens as $token) {
        $rebuilt .= is_array($token) ? $token[1] : $token;
    }

    file_put_contents($path, $rebuilt);

    return true;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($filamentRoot, FilesystemIterator::SKIP_DOTS)
);

$changed = 0;

foreach ($iterator as $fileInfo) {
    if (! $fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
        continue;
    }

    if (refactorFile($fileInfo->getPathname())) {
        $changed++;
        echo "[CORRIGIDO] " . str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $fileInfo->getPathname()) . PHP_EOL;
    }
}

echo PHP_EOL . "{$changed} arquivo(s) corrigido(s)." . PHP_EOL;
