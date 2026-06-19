<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Product;
use App\Form\DataTransformer\EurosToCentsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * @extends AbstractType<Product>
 */
class ProductType extends AbstractType
{
    public function __construct(
        private readonly EurosToCentsTransformer $eurosToCentsTransformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
            ])
            ->add('sku', TextType::class, [
                'label' => 'Référence (SKU)',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('priceCents', MoneyType::class, [
                'label' => 'Prix',
                // Le symbole € est affiché côté template (suffixe stylé), pas par le widget.
                'currency' => false,
                'divisor' => 1,
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image du produit',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer l\'image actuelle',
                'download_uri' => false,
                'image_uri' => true,
            ]);

        // Le champ "prix" reçoit/affiche des euros, mais l'entité stocke des centimes.
        $builder->get('priceCents')->addModelTransformer($this->eurosToCentsTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
