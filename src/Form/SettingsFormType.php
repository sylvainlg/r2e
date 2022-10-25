<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\User;

class SettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('groupErrorMail', CheckboxType::class, [
                'label' => 'Grouper mes mails d\'erreur lors de la recherche de nouvelles entrées',
                'required' => false,
                'value' => '1',
            ])
            ->add('sendEmailOnError', CheckboxType::class, [
                'label' => 'M\'envoyer un mail lorsqu\'une erreur survient lors de l\'ananlyse des flux',
                'required' => false,
                'value' => '1',
            ])
            ->add('Enregistrer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
