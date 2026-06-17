<?php

declare(strict_types=1);

namespace App\Channel\Connector;

use App\Channel\Dto\ProductPayload;
use App\Channel\Exception\ChannelException;
use App\Entity\Channel;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Implémentation HTTP générique du connecteur : tous les canaux étant servis par le même
 * microservice marketplace (en plusieurs instances), une seule impl paramétrée par la
 * configuration du Channel suffit.
 */
class HttpChannelConnector implements ChannelConnector
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function testConnection(Channel $channel): bool
    {
        try {
            return 200 === $this->request($channel, 'GET', '/health')->getStatusCode();
        } catch (HttpClientException) {
            return false;
        }
    }

    public function pushProduct(Channel $channel, ProductPayload $product): void
    {
        $this->send($channel, 'POST', '/products', [
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'priceCents' => $product->priceCents,
            'stock' => $product->stock,
        ]);
    }

    public function updateStock(Channel $channel, ProductPayload $product): void
    {
        $this->send($channel, 'PATCH', $this->productPath($product, 'stock'), [
            'stock' => $product->stock,
        ]);
    }

    public function updatePrice(Channel $channel, ProductPayload $product): void
    {
        $this->send($channel, 'PATCH', $this->productPath($product, 'price'), [
            'priceCents' => $product->priceCents,
        ]);
    }

    private function productPath(ProductPayload $product, string $suffix): string
    {
        return sprintf('/products/%s/%s', rawurlencode($product->sku), $suffix);
    }

    /**
     * Envoie une requête modifiant le canal et échoue explicitement si la réponse n'est pas 2xx.
     *
     * @param array<string, mixed> $payload
     *
     * @throws ChannelException
     */
    private function send(Channel $channel, string $method, string $path, array $payload): void
    {
        try {
            $status = $this->request($channel, $method, $path, $payload)->getStatusCode();
        } catch (HttpClientException $e) {
            throw new ChannelException(sprintf('Canal « %s » injoignable : %s', (string) $channel->getName(), $e->getMessage()), previous: $e);
        }

        if ($status < 200 || $status >= 300) {
            throw new ChannelException(sprintf('Le canal « %s » a répondu %d sur %s %s', (string) $channel->getName(), $status, $method, $path));
        }
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    private function request(Channel $channel, string $method, string $path, ?array $payload = null): ResponseInterface
    {
        $options = [
            'headers' => ['X-Api-Key' => (string) $channel->getApiKey()],
            'timeout' => 10,
        ];

        if (null !== $payload) {
            $options['json'] = $payload;
        }

        return $this->httpClient->request($method, rtrim((string) $channel->getBaseUrl(), '/').$path, $options);
    }
}
