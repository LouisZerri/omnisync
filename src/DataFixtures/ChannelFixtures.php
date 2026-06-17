<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Channel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ChannelFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['channels'];
    }

    public function load(ObjectManager $manager): void
    {
        // Les 3 canaux pointent vers les instances du microservice marketplace (Docker).
        // Les URLs/clés correspondent à la configuration de ces instances (voir compose).
        $channelsData = [
            ['Voltura', 'voltura', 'http://localhost:3001', 'voltura-dev-key'],
            ['Cartelio', 'cartelio', 'http://localhost:3002', 'cartelio-dev-key'],
            ['Zelmark', 'zelmark', 'http://localhost:3003', 'zelmark-dev-key'],
        ];

        foreach ($channelsData as [$name, $code, $baseUrl, $apiKey]) {
            $channel = new Channel();
            $channel->setName($name);
            $channel->setCode($code);
            $channel->setBaseUrl($baseUrl);
            $channel->setApiKey($apiKey);
            $channel->setIsActive(true);

            $manager->persist($channel);
        }

        $manager->flush();
    }
}
