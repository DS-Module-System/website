<?php

namespace App\Form\Website\Contact;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Име',
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Имейл',
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Тема',
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Съобщение',
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Изпрати',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'contact_form',
        ]);
    }
}
