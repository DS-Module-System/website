<?php

namespace App\Entity\Website\Menu;

use App\Repository\Website\Menu\RedirectPageLngRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RedirectPageLngRepository::class)]
class RedirectPageLng extends MenuLng
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $redirectToUrl = null;

    public function getRedirectToUrl(): ?string
    {
        return $this->redirectToUrl;
    }

    public function setRedirectToUrl(?string $redirectToUrl): static
    {
        $this->redirectToUrl = $redirectToUrl;

        return $this;
    }
}
