<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-08-14
 * Time: 10:36
 */

namespace App\Service\Website;

class FormLanguageService
{
    public function __construct(private readonly array $languages)
    {
    }


    public function addMissingLngToForm($entity)
    {
        $entityLngs = $entity->getLngs();
        if((is_countable($entityLngs) ? count($entityLngs) : 0) > 0) {
            foreach ($this->languages as $language) {
                $languageExist = false;
                $entityLng = null;
                foreach ($entityLngs as $entityLng) {
                    if($language == $entityLng->getLng()) {
                        $languageExist = true;
                    }
                }
                if($languageExist === false && $entityLng !== null) {
                    $lngClassName = $entityLng::class;
                    $entity->addLng(new $lngClassName($language));
                }
            }
        } else {
            foreach ($this->languages as $language) {
                $lngClassName = $entity::class . 'Lng';
                $entity->addLng(new $lngClassName($language));
            }
        }
        return $entity;
    }
}
