<?php

namespace App\Entity\Website\Menu;

use App\Repository\Website\Menu\BlogPageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogPageRepository::class)]
class BlogPage extends Menu
{
}
