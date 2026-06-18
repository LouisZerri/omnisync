<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Statut d'un job d'import CSV.
 */
enum ImportStatus: string
{
    case Pending = 'pending';   // en file
    case Running = 'running';   // en cours
    case Done = 'done';         // terminé
    case Failed = 'failed';     // échec

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En file',
            self::Running => 'En cours',
            self::Done => 'Terminé',
            self::Failed => 'Échec',
        };
    }
}
