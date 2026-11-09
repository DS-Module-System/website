<?php

namespace App\Entity\Website\Menu;

use App\Repository\Website\Menu\RedirectPageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RedirectPageRepository::class)]
class RedirectPage extends Menu
{
    #[ORM\ManyToOne]
    private ?Menu $redirectToMenu = null;

    #[ORM\Column(nullable: true)]
    private ?bool $targetBlank = null;

    public function getRedirectToMenu(): ?Menu
    {
        return $this->redirectToMenu;
    }

    public function setRedirectToMenu(?Menu $redirectToMenu): static
    {
        $this->redirectToMenu = $redirectToMenu;

        return $this;
    }

    public function isTargetBlank(): ?bool
    {
        return $this->targetBlank;
    }

    public function setTargetBlank(?bool $targetBlank): static
    {
        $this->targetBlank = $targetBlank;

        return $this;
    }
}
