<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("searchInput", TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Nom du challenge']
            ])
            ->add("search", SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
