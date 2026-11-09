<?php

namespace App\Form\Website\Menu;

use App\Entity\Website\Menu\ContactPage;
use App\Form\Website\Menu\MenuForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ContactPageForm extends MenuForm
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('lngs', CollectionType::class, [
                'entry_type' => ContactPageLngForm::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactPage::class,
            'pageModuleOptions' => [],
            'menuTagOptions' => [],
        ]);
    }
}
