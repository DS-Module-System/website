<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-02
 * Time: 14:23
 */

namespace App\Form\Website\Menu;

use App\Entity\Website\Menu\StaticPage;
use App\Form\Website\Menu\MenuForm;
use App\Form\Website\Menu\StaticPageLngForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class StaticPageForm extends MenuForm
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder, $options);

        $builder
            ->add('lngs', CollectionType::class, [
                'entry_type' => StaticPageLngForm::class,
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StaticPage::class,
            'pageModuleOptions' => [],
            'menuTagOptions' => [],
        ]);
    }
}
