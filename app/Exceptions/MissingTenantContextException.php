<?php

namespace App\Exceptions;

use RuntimeException;

final class MissingTenantContextException extends RuntimeException
{
    public static function forModel(string $model): self
    {
        return new self(
            "Nenhuma organização ativa foi definida para o model [{$model}]."
        );
    }
}
