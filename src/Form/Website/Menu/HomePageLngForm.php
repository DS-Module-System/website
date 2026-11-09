<?php

/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-02
 * Time: 14:23
 */

namespace App\Form\Website\Menu;

use App\Entity\Website\Menu\HomePageLng;
use App\Form\Website\Menu\MenuLngForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class HomePageLngForm extends MenuLngForm
{
    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator);
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HomePageLng::class,
            'pageModuleOptions' => [],
            'menuTagOptions' => [],
        ]);
    }
}
