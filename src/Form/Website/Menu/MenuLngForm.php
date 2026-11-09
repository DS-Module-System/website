<?php

/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-02
 * Time: 14:23
 */

namespace App\Form\Website\Menu;

use App\Entity\Website\Menu\MenuLng;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class MenuLngForm extends AbstractType
{
    private $translator;
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isActive', CheckboxType::class, [
                'label' => "isActive",
                'label_attr' => [
                    'data-on-label' => $this->translator->trans('layout.yesTitle'),
                    'data-off-label' => $this->translator->trans('layout.noTitle'),
                ],
                'attr' => [
                    'data-switch' => 'success',
                ],
            ])
            ->add('menuTitle', TextType::class, [
                'label' => 'menuTitle',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('contentTitle', TextType::class, [
                'label' => 'contentTitle',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'metaDescription',
                'required' => false,
            ])
            ->add('metaKeywords', TextType::class, [
                'label' => 'metaKeywords',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'description',
                'required' => false,
            ])
            ->add('body', TextareaType::class, [
                'label' => 'body',
                'required' => false,
                'attr' => [
                    'data-tinymce' => '{}',
                ],
            ])
            ->add('url', TextType::class, [
                'label' => 'url',
                'required' => false,
            ])
            ->add('pageTitle', TextType::class, [
                'label' => 'pageTitle',
                'constraints' => [
                    new NotBlank(),
                ],
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MenuLng::class,
            'translation_domain' => 'website_menu',
        ]);
    }
}
