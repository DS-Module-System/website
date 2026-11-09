<?php

namespace App\Form\Website\Blog;

use App\Entity\Website\Blog\Blog;
use App\Form\Core\DefaultForm\EditForm;
use App\Form\Core\DefaultForm\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class BlogForm extends EditForm
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('leadingImage', FileType::class, [
                'label' => 'leadingImage',
                'required' => false,
                'attr' => [
                    'data-managedfiles' => '{"image": true}',
                    'style' => 'display: none',
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'isActive',
                'label_attr' => [
                    'data-on-label' => $this->translator->trans('layout.yesTitle'),
                    'data-off-label' => $this->translator->trans('layout.noTitle'),
                ],
                'attr' => [
                    'data-switch' => 'success',
                ],
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'label' => 'title',
                'constraints' => [new NotBlank()],
            ])
            ->add('slug', TextType::class, [
                'label' => 'slug',
                'constraints' => [new NotBlank()],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'description',
                'required' => false,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'content',
                'required' => false,
                'attr' => [
                    'data-tinymce' => '{}',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
            'translation_domain' => 'website_blog',
        ]);
    }
}
