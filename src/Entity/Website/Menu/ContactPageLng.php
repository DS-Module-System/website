<?php

namespace App\Entity\Website\Menu;

use App\Repository\Website\Menu\ContactPageLngRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactPageLngRepository::class)]
class ContactPageLng extends MenuLng
{
}
