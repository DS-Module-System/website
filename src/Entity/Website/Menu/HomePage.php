<?php

namespace App\Entity\Website\Menu;

use App\Entity\Website\Menu\Menu;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\Website\Menu\HomePageRepository;

#[ORM\Entity(repositoryClass: HomePageRepository::class)]
class HomePage extends Menu
{
}
