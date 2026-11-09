<?php

namespace App\Entity\Website\Menu;

use App\Repository\Website\Menu\StaticPageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StaticPageRepository::class)]
class StaticPage extends Menu
{
}
