<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Form\Model\ChangePassword;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('oldPassword', PasswordType::class, [
            'label' => 'Mot de passe actuel',
            ])
        ->add('newPassword', PasswordType::class, [
            'label' => 'Nouveau mot de passe',
        ])
        ->add('Modifier', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChangePassword::class,
        ]);
    }
}
