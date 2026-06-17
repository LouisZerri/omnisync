<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Convertit un prix entre sa représentation en centimes (stockée en base, côté entité)
 * et sa représentation en euros (saisie et affichée à l'utilisateur).
 *
 * @implements DataTransformerInterface<mixed, string>
 */
class EurosToCentsTransformer implements DataTransformerInterface
{
    /**
     * Centimes (entier, côté modèle) vers euros (chaîne affichée dans le champ).
     */
    public function transform(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        if (!is_int($value)) {
            throw new TransformationFailedException('Le prix attendu doit être un entier en centimes');
        }

        return number_format($value / 100, 2, '.', '');
    }

    /**
     * Euros (chaîne saisie) vers centimes (entier, côté modèle).
     */
    public function reverseTransform(mixed $value): ?int
    {
        if (null === $value || '' === $value) {
            return null;
        }

        // On accepte la virgule comme séparateur décimal (saisie française).
        $normalized = str_replace(',', '.', (string) $value);

        if (!is_numeric($normalized)) {
            throw new TransformationFailedException('Le prix saisi n\'est pas un nombre valide');
        }

        return (int) round((float) $normalized * 100);
    }
}
