<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Form;

use App\Manager\Module\User\Command\UserRegistration\UserRegisterCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'registration.email.label',
                'attr' => ['placeholder' => 'registration.email.placeholder'],
            ])
            ->add(
                'password',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => true,
                    'error_bubbling' => false,
                    'invalid_message' => 'registration.password.mismatch',
                    'first_options'  => [
                        'label' => 'registration.password.label',
                        'attr' => [
                            'placeholder' => 'registration.password.placeholder'
                        ],
                    ],
                    'second_options' => [
                        'label' => 'registration.repeat_password.label',
                        'attr' => [
                            'placeholder' => 'registration.repeat_password.placeholder'
                        ],
                    ],
                ]
            )
            ->add('managerName', TextType::class, [
                'label' => 'registration.manager_name.label',
                'attr' => ['placeholder' => 'registration.manager_name.placeholder'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserRegisterCommand::class,
            'translation_domain' => 'messages',
        ]);
    }
}
