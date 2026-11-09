<?php

namespace App\Entity\Website\Menu;

use App\Repository\Website\Menu\HomePageLngRepository;
use App\Entity\Website\Menu\MenuLng;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HomePageLngRepository::class)]
class HomePageLng extends MenuLng {}
