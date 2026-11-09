<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-02
 * Time: 14:23
 */

namespace App\Form\Website\Menu;

use App\Entity\Website\Menu\RedirectPageLng;
use App\Form\Website\Menu\MenuLngForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class RedirectPageLngForm extends AbstractType
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
                'label' => "admin.menu.isActive",
                'label_attr' => [
                    'data-on-label' => $this->translator->trans('admin.layout.yesTitle'),
                    'data-off-label' => $this->translator->trans('admin.layout.noTitle'),
                ],
                'attr' => [
                    'data-switch' => 'success',
                ],
            ])
            ->add('menuTitle', TextType::class, [
                'label' => 'admin.menu.menuTitle',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('url', TextType::class, [
                'label' => 'admin.menu.url',
                'required' => false,
            ])
            ->add('redirectToUrl', TextType::class, [
                'label' => 'admin.menu.redirectToUrl',
                'required' => false,
            ])

        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RedirectPageLng::class,
            'pageModuleOptions' => [],
            'menuTagOptions' => [],
        ]);
    }
}
