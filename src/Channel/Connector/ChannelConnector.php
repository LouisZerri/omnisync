<?php

declare(strict_types=1);

namespace App\Channel\Connector;

use App\Channel\Dto\ProductPayload;
use App\Channel\Exception\ChannelException;
use App\Entity\Channel;

/**
 * Abstraction d'un canal de vente. OmniSync dépend de cette interface, jamais d'une
 * implémentation concrète : brancher un vrai marketplace plus tard = une nouvelle impl,
 * sans toucher au reste (inversion de dépendances).
 */
interface ChannelConnector
{
    /**
     * Vérifie l'accessibilité du canal et la validité de ses identifiants.
     */
    public function testConnection(Channel $channel): bool;

    /**
     * Pousse la fiche produit complète (création ou mise à jour) vers le canal.
     *
     * @throws ChannelException si le canal est injoignable ou répond en erreur
     */
    public function pushProduct(Channel $channel, ProductPayload $product): void;

    /**
     * Met à jour uniquement le stock du produit sur le canal.
     *
     * @throws ChannelException si le canal est injoignable ou répond en erreur
     */
    public function updateStock(Channel $channel, ProductPayload $product): void;

    /**
     * Met à jour uniquement le prix du produit sur le canal.
     *
     * @throws ChannelException si le canal est injoignable ou répond en erreur
     */
    public function updatePrice(Channel $channel, ProductPayload $product): void;
}
