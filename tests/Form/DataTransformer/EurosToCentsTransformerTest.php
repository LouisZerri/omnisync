<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\EurosToCentsTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Vérifie la conversion prix euros ↔ centimes (logique financière sensible aux arrondis).
 */
final class EurosToCentsTransformerTest extends TestCase
{
    private EurosToCentsTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new EurosToCentsTransformer();
    }

    public function testTransformCentsToEuros(): void
    {
        self::assertSame('199.99', $this->transformer->transform(19999));
        self::assertSame('5.00', $this->transformer->transform(500));
        self::assertSame('0.00', $this->transformer->transform(0));
        self::assertSame('', $this->transformer->transform(null));
    }

    public function testTransformRejectsNonInteger(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->transformer->transform('199.99');
    }

    public function testReverseTransformEurosToCents(): void
    {
        self::assertSame(19999, $this->transformer->reverseTransform('199.99'));
        self::assertSame(500, $this->transformer->reverseTransform('5'));
        self::assertNull($this->transformer->reverseTransform(''));
        self::assertNull($this->transformer->reverseTransform(null));
    }

    public function testReverseTransformAcceptsFrenchComma(): void
    {
        self::assertSame(1250, $this->transformer->reverseTransform('12,50'));
    }

    public function testReverseTransformRejectsNonNumeric(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->transformer->reverseTransform('abc');
    }

    public function testRoundTripIsStable(): void
    {
        // centimes → euros → centimes ne doit pas perdre d'information
        self::assertSame(8990, $this->transformer->reverseTransform($this->transformer->transform(8990)));
    }
}
