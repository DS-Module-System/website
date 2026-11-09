<?php

namespace App\Entity\Website\Menu;

use App\Repository\Website\Menu\BlogPageLngRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogPageLngRepository::class)]
class BlogPageLng extends MenuLng
{
}
