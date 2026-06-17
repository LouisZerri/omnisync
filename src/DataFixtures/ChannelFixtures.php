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
        // Identité métier des canaux uniquement. La config de connexion (URL, clé API)
        // vit dans les variables d'environnement, résolue par le code du canal.
        $channelsData = [
            ['Voltura', 'voltura'],
            ['Cartelio', 'cartelio'],
            ['Zelmark', 'zelmark'],
        ];

        foreach ($channelsData as [$name, $code]) {
            $channel = new Channel();
            $channel->setName($name);
            $channel->setCode($code);
            $channel->setIsActive(true);

            $manager->persist($channel);
        }

        $manager->flush();
    }
}
