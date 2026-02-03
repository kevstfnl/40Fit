<?php

namespace App\Form;

use App\Entity\Result;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            ->add('date', DateType::class, [
                'attr' => [
                    'max' => new \DateTime()->format('Y-m-d'),
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $result = $event->getData();
            if ($result->getScore()) {
                $event->getForm()->get('score')->setData($result->getScore());
            }
            if ($result->getDate()) {
                $event->getForm()->get('date')->setData($result->getDate());
            } else {
                $event->getForm()->get('date')->setData(new \DateTime());
            }
        });
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
