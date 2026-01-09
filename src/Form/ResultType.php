<?php

namespace App\Form;

use App\Entity\Challenge;
use App\Entity\Result;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('score', NumberType::class, [
                'scale' => 2,
            ])
        ;

        if ($options['include_challenge_field']) {
            $builder->add('challenge', EntityType::class, [
                'class' => Challenge::class,
                'choice_label' => 'title',
                'choices' => $options['challenges'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Result::class,
            'challenges' => [],
            'include_challenge_field' => true,
        ]);

        $resolver->setAllowedTypes('challenges', 'array');
        $resolver->setAllowedTypes('include_challenge_field', 'bool');
    }
}
