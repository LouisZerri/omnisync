<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
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

        foreach ($productsData as [$sku, $name, $description, $priceCents, $stock]) {
            $product = new Product();
            $product->setSku($sku);
            $product->setName($name);
            $product->setDescription($description);
            $product->setPriceCents($priceCents);
            $product->setStock($stock);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
