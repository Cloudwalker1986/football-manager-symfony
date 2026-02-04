<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Form;

use App\Manager\Module\User\Command\UpdateProfile\UpdateProfileCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'profile.email.label',
                'attr' => ['placeholder' => 'profile.email.placeholder'],
            ])
            ->add('managerName', TextType::class, [
                'label' => 'profile.manager_name.label',
                'attr' => ['placeholder' => 'profile.manager_name.placeholder'],
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'profile.locale.label',
                'choices' => [
                    'profile.locale.german' => 'de',
                    'profile.locale.english' => 'en',
                ],
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => 'profile.current_password.label',
                'required' => false,
                'attr' => ['placeholder' => 'profile.current_password.placeholder'],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'invalid_message' => 'profile.password.mismatch',
                'first_options'  => [
                    'label' => 'profile.new_password.label',
                    'attr' => ['placeholder' => 'profile.new_password.placeholder'],
                ],
                'second_options' => [
                    'label' => 'profile.repeat_password.label',
                    'attr' => ['placeholder' => 'profile.repeat_password.placeholder'],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateProfileCommand::class,
            'translation_domain' => 'messages',
        ]);
    }
}
