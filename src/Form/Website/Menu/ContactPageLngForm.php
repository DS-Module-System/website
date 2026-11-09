<?php

namespace App\Form\Website\Menu;

use App\Entity\Website\Menu\ContactPageLng;
use App\Form\Website\Menu\MenuLngForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactPageLngForm extends MenuLngForm
{
    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactPageLng::class,
            'pageModuleOptions' => [],
            'menuTagOptions' => [],
        ]);
    }
}
