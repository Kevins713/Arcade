<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EditDescriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('description', TextType::class, [
            'label' => 'Modifier la description de profil',
            'attr' => [
                'placeholder' => 'Ajouter une description (maximum 500 caractères)'
            ],
            'constraints' => [
                new Length([
                    'min' => 0,
                    'max' => 500,
                    'minMessage' => 'Le titre doit contenir au minimum {{ limit }} caractères',
                    'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères',
                ]),
                new NotBlank([
                    'message' => 'Merci de renseigner une description',
                ]),
            ]
        ])

        ->add('save', SubmitType::class, [
            'label' => "Enregistrer",
            'attr'  => [
                'class' => 'btn btn-secondary bg-lightblue col-12'
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
