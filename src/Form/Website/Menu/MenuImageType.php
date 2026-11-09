<?php

/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-22
 * Time: 17:36
 */

namespace App\Form\Website\Menu;

use App\Entity\Website\Menu\MenuImage;
use App\Form\Core\DefaultForm\FileAssociationType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuImageType extends FileAssociationType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MenuImage::class,
            'invalid_message' => 'The selected issue does not exist',
        ]);
    }
}
