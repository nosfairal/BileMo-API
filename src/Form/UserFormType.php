<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface as FormFormInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

        ->add('email', EmailType::class, [
            'constraints' => [
            new NotNull()]
        ])
        ->add('userName', TextType::class,  [
            'constraints' => [
                new NotNull(),
                new Length([
                    'min' => 3,
                    'minMessage' => 'Your username must have at least {{ limit }} characters',
                    // max length allowed by Symfony for security reasons
                    'max' => 30,
                    'maxMessage' => 'Votre pseudonyme doit faire au plus {{ limit }} characters'
                ]),
            ],
        ])
        ->add('firstName', TextType::class, [
            'required' => true,
        ])
        ->add('lastName', TextType::class, [
            'required' => true,
 
        ])
        ->add('password', PasswordType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller

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
        /*->add('customer', EntityType::class, [
            'class' => Customer::class,
            'value' => 2

        ])*/
        /*->add('customer', EntityType::class, [
            'class' => Customer::class,
            'getter' => function (User $user, FormInterface $form):object {
                return $form->getUserData()->getCustomer();
            },
            'setter' => function (User &$user, ?object $customer, FormInterface $form): void {
                $user->setCustomer($customer);
            },
        ])*/

    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
            /*'empty_data' => function (FormFormInterface $form) {
                return new User(
                    $form->get('customer')->getData()
                );
            },*/
        ]);
    }
}