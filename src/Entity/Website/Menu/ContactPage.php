<?php

namespace App\Entity\Website\Menu;

use App\Repository\Website\Menu\ContactPageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactPageRepository::class)]
class ContactPage extends Menu
{
}
