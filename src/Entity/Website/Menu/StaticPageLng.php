<?php

namespace App\Entity\Website\Menu;

use App\Repository\Website\Menu\StaticPageLngRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StaticPageLngRepository::class)]
class StaticPageLng extends MenuLng
{
}
