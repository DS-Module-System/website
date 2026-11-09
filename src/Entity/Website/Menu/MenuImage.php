<?php

namespace App\Entity\Website\Menu;

use App\Entity\Core\FileAssociation;
use App\Repository\Website\Menu\MenuImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuImageRepository::class)]
class MenuImage extends FileAssociation {

    #[ORM\ManyToOne(inversedBy: 'menuImages')]
    private ?Menu $menu = null;

    public function getMenu(): ?Menu {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self {
        $this->menu = $menu;

        return $this;
    }
}
