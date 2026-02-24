<?php

namespace App\Traits;

trait GuardSelectable
{
    protected static function getGuardOptions(): array
    {
        $guardWithOptions = [];
        $guards = config('auth.guards');

        foreach ($guards as $guardName => $guardConfig) {
            $providerName = $guardConfig['provider'] ?? null;
            if ($providerName) {
                $modelClass = config("auth.providers.{$providerName}.model");
                if ($modelClass) {
                    $modelName = class_basename($modelClass);
                    $guardWithOptions[$guardName] = "$guardName (Model: $modelName)";
                } else {
                    $guardWithOptions[$guardName] = $guardName;
                }
            } else {
                $guardWithOptions[$guardName] = $guardName;
            }
        }
        return $guardWithOptions;
    }
}
