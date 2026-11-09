<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-02
 * Time: 14:23
 */

namespace App\Form\Website\Menu;

use App\Entity\Website\Menu\Menu;
use App\Entity\Website\Menu\RedirectPage;
use App\Form\Website\Menu\MenuForm;
use App\Form\Website\Menu\RedirectPageLngForm;
use App\Form\Core\DefaultForm\EditForm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class RedirectPageForm extends EditForm
{
    private $translator;
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder, $options);

        $menuTagOptions = array_flip($options['menuTagOptions']);

        $builder
            ->add('title', TextType::class, [
                'label' => 'admin.menu.title',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                ]
            ])
            ->add('tags', ChoiceType::class, [
                'label' => 'admin.menu.tags',
                'choices' => $menuTagOptions,
                'placeholder' => '',
                'expanded' => true,
                'multiple' => true,
                'label_attr' => [
                    'class' => 'custom-checkboxes',
                ],
                'required' => false,
            ])
            ->add('url', TextType::class, [
                'label' => 'admin.menu.url',
                'required' => false,
            ])
            ->add('targetBlank', CheckboxType::class, [
                'label' => "admin.menu.targetBlank",
                'label_attr' => [
                    'data-on-label' => $this->translator->trans('admin.layout.yesTitle'),
                    'data-off-label' => $this->translator->trans('admin.layout.noTitle'),
                ],
                'attr' => [
                    'data-switch' => 'success',
                ],
            ])
            ->add('redirectToMenu', EntityType::class, [
                'class' => Menu::class,
                'choice_label' => 'title',
                'required' => false,
            ])
        ;

        $builder
            ->add('lngs', CollectionType::class, [
                'entry_type' => RedirectPageLngForm::class,
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
            'data_class' => RedirectPage::class,
            'pageModuleOptions' => [],
            'menuTagOptions' => [],
        ]);
    }
}
