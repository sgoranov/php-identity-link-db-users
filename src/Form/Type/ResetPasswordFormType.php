<?php
declare(strict_types=1);

namespace App\Form\Type;

use sgoranov\IdentityLinkShared\Validator\PasswordStrength;
use sgoranov\IdentityLinkShared\Validator\PasswordStrengthValidator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => [
                    'label' => 'form_reset_password.new_password.label',
                    'constraints' => [
                        new NotBlank(),
                        new PasswordStrength(),
                    ],
                ],
                'second_options' => [
                    'label' => 'form_reset_password.confirm_password.label',
                    'translation_domain' => 'messages',
                ],
                'invalid_message' => 'form_reset_password.passwords_do_not_match',
                'translation_domain' => 'messages',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'app.submit',
            ]);
    }
}