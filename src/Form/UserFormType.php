<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            new NotBlank([
                'message' => "This field can't be empty",
            ]),
        ])
        ->add('userName', TextType::class,  [
            'attr' => [
                'class' => 'form-control'
            ],
            'label' => 'Votre pseudonyme :*',
            'constraints' => [
                new NotBlank([
                    'message' => "This field can't be empty",
                ]),
                new Length([
                    'min' => 3,
                    'minMessage' => 'Your username must have at least {{ limit }} characters',
                    // max length allowed by Symfony for security reasons
                    'max' => 30,
                    'maxMessage' => 'Votre pseudonyme doit faire au plus {{ limit }} characters'
                ]),
            ],
        ])
        ->add('plainPassword', PasswordType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'mapped' => false,
            'attr' => [
                'autocomplete' => 'new-password'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => "This field can't be empty",
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} characters',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ],
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}