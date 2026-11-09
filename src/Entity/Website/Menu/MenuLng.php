<?php

namespace App\Entity\Website\Menu;

use App\Repository\Website\Menu\MenuLngRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/*
 * AKO NQMA DiscriminatorMap DOCTRINE SI PRAVI SOBSTVENI CAMEL CASE.
 * ZADYLJITELNO TRQBWA DA IMA PONE 1 DISCRIMATOR MAP ZA DA RABOTI PRAVILNO DynamicDiscriminatorSubscription
 * */

#[ORM\Entity(repositoryClass: MenuLngRepository::class)]
#[ORM\UniqueConstraint(name: "lng_unique", columns: ["lng", "master_id"])]
#[ORM\InheritanceType(value: 'SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(value: [
    'menu' => MenuLng::class,
    'home_page' => HomePageLng::class,
    'static_page' => StaticPageLng::class,
    'redirect_page' => RedirectPageLng::class,
    'blog_page' => BlogPageLng::class,
    'contact_page' => ContactPageLng::class,
])]
#[ORM\HasLifecycleCallbacks]
abstract class MenuLng
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Website\Menu\Menu', inversedBy: 'lngs', cascade: ['persist'])]
    private $master;

    #[ORM\Column(type: 'string', length: 2, options: ['fixed' => true, 'collation' => 'ascii_bin'])]
    private $lng;

    #[ORM\Column(type: 'boolean')]
    private $isActive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $menuTitle;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contentTitle;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $metaDescription;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $metaKeywords;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[ORM\Column(type: 'text', nullable: true)]
    private $body;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pageTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $headHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $preBodyHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $posBodyHtml = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $url = null;

    public function __construct($lng = null)
    {
        if ($lng !== null) {
            $this->lng = $lng;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }
    /**
     * @return mixed
     */
    public function getMaster()
    {
        return $this->master;
    }
    /**
     * @param Menu $master
     * @return self
     */
    public function setMaster(?Menu $master): self
    {
        $this->master = $master;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getLng()
    {
        return $this->lng;
    }
    /**
     * @param string $lng
     * @return self
     */
    public function setLng(string $lng): self
    {
        $this->lng = $lng;

        return $this;
    }
    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
    public function getMenuTitle(): ?string
    {
        return $this->menuTitle;
    }
    public function setMenuTitle(?string $menuTitle): self
    {
        $this->menuTitle = $menuTitle;

        return $this;
    }
    public function getContentTitle(): ?string
    {
        return $this->contentTitle;
    }
    public function setContentTitle(?string $contentTitle): self
    {
        $this->contentTitle = $contentTitle;

        return $this;
    }
    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }
    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }
    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }
    public function setMetaKeywords(?string $metaKeywords): self
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
    public function getBody(): ?string
    {
        return $this->body;
    }
    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getPageTitle(): ?string
    {
        return $this->pageTitle;
    }

    public function setPageTitle(?string $pageTitle): static
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    public function getHeadHtml(): ?string
    {
        return $this->headHtml;
    }

    public function setHeadHtml(?string $headHtml): static
    {
        $this->headHtml = $headHtml;

        return $this;
    }

    public function getPreBodyHtml(): ?string
    {
        return $this->preBodyHtml;
    }

    public function setPreBodyHtml(?string $preBodyHtml): static
    {
        $this->preBodyHtml = $preBodyHtml;

        return $this;
    }

    public function getPosBodyHtml(): ?string
    {
        return $this->posBodyHtml;
    }

    public function setPosBodyHtml(?string $posBodyHtml): static
    {
        $this->posBodyHtml = $posBodyHtml;

        return $this;
    }


    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }
}
