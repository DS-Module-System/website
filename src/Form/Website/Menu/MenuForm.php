<?php

/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-02
 * Time: 14:23
 */

namespace App\Form\Website\Menu;

use App\Entity\Website\Menu\Menu;
use App\Enum\Website\SkinMenu;
use App\Form\Core\DefaultForm\EditForm;
use App\Form\Core\DefaultForm\FileAssociationCollectionType;
use App\Form\Core\DefaultForm\FileType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

abstract class MenuForm extends EditForm
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder, $options);

        //        $pageModuleOptions = [];
        //        foreach ($options['pageModuleOptions'] as $k => $v) {
        //            $pageModuleOptions[$v['title']] = $k;
        //        }

        $menuTagOptions = array_flip($options['menuTagOptions']);


        $builder
            ->add('title', TextType::class, [
                'label' => 'title',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                ]
            ])
            ->add('skin', EnumType::class, [
                'class' => SkinMenu::class,
                'label' => 'skin',
                'required' => false,
            ])
            ->add('tags', ChoiceType::class, [
                'label' => 'tags',
                'choices' => $menuTagOptions,
                'placeholder' => '',
                'expanded' => true,
                'multiple' => true,
                'label_attr' => [
                    'class' => 'custom-checkboxes',
                ],
                'required' => false,
            ])
            //            ->add('lngs', CollectionType::class, [
            //                'entry_type' => MenuLngForm::class,
            //            ])

            ->add('leadingImage', FileType::class, [
                'label' => 'leadingImage',
                'attr' => [
                    'data-managedfiles' => '{"image": true}',
                    'style' => 'display: none',
                ]
            ])
            ->add('menuImages', FileAssociationCollectionType::class, [
                'entry_type' => MenuImageType::class,
                'label' => 'images',
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => false,
                'delete_empty' => true,
                'attr' => [
                    'data-managedfiles' => '{"multiple":true, "image": true}',
                    'style' => 'display: none',
                ],
            ])
            ->add('url', TextType::class, [
                'label' => 'url',
                'required' => false,
            ])
            ->add('headHtml', TextareaType::class, [
                'label' => 'headHtml',
                'required' => false,
            ])
            ->add('preBodyHtml', TextareaType::class, [
                'label' => 'preBodyHtml',
                'required' => false,
            ])
            ->add('posBodyHtml', TextareaType::class, [
                'label' => 'posBodyHtml',
                'required' => false,
            ])
            ->add('jsonData', TextareaType::class, [
                'label' => 'jsonData',
                'required' => false,
            ])
        ;

        $builder->get('tags')->addModelTransformer(new CallbackTransformer(
            function ($tagsAsString) {
                // transform the string back to an array
                return explode(',', $tagsAsString);
            },
            function ($tagsAsArray) {
                // transform the array to a string
                return implode(',', $tagsAsArray);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
            'pageModuleOptions' => [],
            'menuTagOptions' => [],
            'translation_domain' => 'website_menu',
        ]);
    }
}
