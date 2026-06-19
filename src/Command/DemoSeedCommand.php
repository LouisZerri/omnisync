<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Channel;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\ChannelRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Initialise les données de démonstration (comptes, canaux, catalogue) sur l'instance
 * de démo publique. Idempotente : ne recrée pas ce qui existe déjà, donc rejouable à
 * chaque déploiement. Disponible en prod (contrairement aux DataFixtures, réservées au dev).
 */
#[AsCommand(name: 'app:demo:seed', description: 'Crée les données de démonstration (comptes, canaux, produits)')]
final class DemoSeedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly ChannelRepository $channelRepository,
        private readonly ProductRepository $productRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $created = $this->seedUsers() + $this->seedChannels() + $this->seedProducts();
        $this->entityManager->flush();

        $io->success(sprintf('Données de démonstration prêtes (%d élément(s) créé(s)).', $created));

        return Command::SUCCESS;
    }

    private function seedUsers(): int
    {
        $usersData = [
            ['admin@omnisync.test', 'Alice Admin', ['ROLE_ADMIN'], true],
            ['manager@omnisync.test', 'Marc Gestionnaire', ['ROLE_MANAGER'], true],
            ['inactif@omnisync.test', 'Inès Inactive', ['ROLE_MANAGER'], false],
        ];

        $created = 0;
        foreach ($usersData as [$email, $name, $roles, $isActive]) {
            if (null !== $this->userRepository->findOneBy(['email' => $email])) {
                continue;
            }

            $user = new User();
            $user->setEmail($email);
            $user->setName($name);
            $user->setRoles($roles);
            $user->setIsActive($isActive);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

            $this->entityManager->persist($user);
            ++$created;
        }

        return $created;
    }

    private function seedChannels(): int
    {
        $channelsData = [
            ['Voltura', 'voltura'],
            ['Cartelio', 'cartelio'],
            ['Zelmark', 'zelmark'],
        ];

        $created = 0;
        foreach ($channelsData as [$name, $code]) {
            if (null !== $this->channelRepository->findOneBy(['code' => $code])) {
                continue;
            }

            $channel = new Channel();
            $channel->setName($name);
            $channel->setCode($code);
            $channel->setIsActive(true);

            $this->entityManager->persist($channel);
            ++$created;
        }

        return $created;
    }

    private function seedProducts(): int
    {
        $productsData = [
            ['SKU-CASQUE-01', 'Casque audio sans fil Pro', 'Casque circum-auriculaire Bluetooth 5.3 avec réduction de bruit active et 40h d\'autonomie.', 19999, 45],
            ['SKU-CHARGEUR-65', 'Chargeur USB-C 65W', 'Chargeur secteur compact GaN, 3 ports, charge rapide pour ordinateurs portables et smartphones.', 4990, 120],
            ['SKU-COQUE-IP15', 'Coque silicone iPhone 15', 'Coque de protection en silicone souple, intérieur microfibre, compatible MagSafe.', 1490, 300],
            ['SKU-SOURIS-ERGO', 'Souris ergonomique sans fil', 'Souris verticale Bluetooth, capteur 4000 DPI, rechargeable, réduit la tension du poignet.', 3990, 0],
            ['SKU-CLAVIER-MEK', 'Clavier mécanique compact', 'Clavier 75% rétroéclairé RGB, switches tactiles, connexion sans fil ou filaire.', 8990, 28],
            ['SKU-SSD-1TO', 'SSD externe 1 To', 'Disque SSD portable USB 3.2, vitesse jusqu\'à 1050 Mo/s, boîtier aluminium résistant.', 10990, 60],
            ['SKU-WEBCAM-HD', 'Webcam Full HD 1080p', 'Webcam avec autofocus, microphone stéréo intégré et cache de confidentialité.', 5490, 15],
            ['SKU-ENCEINTE-BT', 'Enceinte Bluetooth portable', 'Enceinte étanche IPX7, son 360°, 20h d\'autonomie, idéale pour l\'extérieur.', 6990, 80],
        ];

        $created = 0;
        foreach ($productsData as [$sku, $name, $description, $priceCents, $stock]) {
            if (null !== $this->productRepository->findOneBy(['sku' => $sku])) {
                continue;
            }

            $product = new Product();
            $product->setSku($sku);
            $product->setName($name);
            $product->setDescription($description);
            $product->setPriceCents($priceCents);
            $product->setStock($stock);

            $this->entityManager->persist($product);
            ++$created;
        }

        return $created;
    }
}
