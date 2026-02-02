<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('currentPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir votre mot de passe actuel.',
                        groups: ['password_change'],
                    ),
                    new UserPassword(
                        message: 'Le mot de passe actuel est incorrect.',
                        groups: ['password_change'],
                    ),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'first_options' => ['label' => false],
                'second_options' => ['label' => false],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir un nouveau mot de passe.',
                        groups: ['password_change'],
                    ),
                    new Length(
                        min: 6,
                        max: 4096,
                        minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caracteres.',
                        groups: ['password_change'],
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => static function (FormInterface $form): array {
                $groups = ['Default'];
                $newPassword = $form->get('newPassword')->getData();

                if (!empty($newPassword)) {
                    $groups[] = 'password_change';
                }

                return $groups;
            },
        ]);
    }
}
