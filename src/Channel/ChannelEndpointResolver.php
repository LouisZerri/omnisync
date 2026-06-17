<?php

declare(strict_types=1);

namespace App\Channel;

use App\Channel\Exception\ChannelException;
use App\Entity\Channel;

/**
 * Résout la configuration de connexion (URL de base, clé API) d'un canal à partir de son
 * code, en lisant les variables d'environnement (cf. config/packages/channels.yaml).
 * La base ne contient jamais ces données : elles sont propres à chaque environnement.
 */
class ChannelEndpointResolver
{
    /**
     * @param array<string, array{base_url: string, api_key: string}> $channels
     */
    public function __construct(
        private readonly array $channels,
    ) {
    }

    public function baseUrl(Channel $channel): string
    {
        return $this->endpoint($channel)['base_url'];
    }

    public function apiKey(Channel $channel): string
    {
        return $this->endpoint($channel)['api_key'];
    }

    /**
     * @return array{base_url: string, api_key: string}
     */
    private function endpoint(Channel $channel): array
    {
        $code = (string) $channel->getCode();

        if (!isset($this->channels[$code])) {
            throw new ChannelException(sprintf('Aucune configuration de connexion pour le canal « %s »', $code));
        }

        return $this->channels[$code];
    }
}
