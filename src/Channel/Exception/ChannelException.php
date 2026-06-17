<?php

declare(strict_types=1);

namespace App\Channel\Exception;

/**
 * Levée quand un appel vers un canal échoue (canal injoignable ou réponse non 2xx).
 * Permettra de déclencher les retries / la dead-letter queue au Palier 1 (Phase 4).
 */
class ChannelException extends \RuntimeException
{
}
