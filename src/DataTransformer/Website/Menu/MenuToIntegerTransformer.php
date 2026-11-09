<?php

namespace App\DataTransformer\Website\Menu;

use App\Entity\Website\Menu\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class MenuToIntegerTransformer implements DataTransformerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    /**
     * Transforms an object (file) to an integer (number).
     */
    public function transform($file): string
    {
        if (null === $file) {
            return '';
        }

        return $file->getId();
    }

    /**
     * Transforms an integer (number) to an object (file).
     */
    public function reverseTransform($integer): ?Menu
    {
        // no integer number? It's optional, so that's ok
        if (!$integer) {
            return null;
        }

        $file = $this->entityManager
            ->getRepository(Menu::class)
            // query for the issue with this id
            ->find($integer);

        if (null === $file) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An issue with number "%s" does not exist!',
                $integer
            ));
        }

        return $file;
    }
}
