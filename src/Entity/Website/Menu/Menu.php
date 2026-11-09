<?php

namespace App\Entity\Website\Menu;

use App\Entity\Core\File;
use App\Enum\Website\SkinMenu;
use Doctrine\DBAL\Types\Types;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\Website\Menu\MenuRepository;
use Doctrine\ORM\Mapping as ORM;

/*
 * AKO NQMA DiscriminatorMap DOCTRINE SI PRAVI SOBSTVENI CAMEL CASE.
 * ZADYLJITELNO TRQBWA DA IMA PONE 1 DISCRIMATOR MAP ZA DA RABOTI PRAVILNO DynamicDiscriminatorSubscription
 * */

#[Gedmo\Tree(type: 'nested')]
#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\InheritanceType(value: 'SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(value: [
    'menu' => Menu::class,
    'home_page' => HomePage::class,
    'static_page' => StaticPage::class,
    'redirect_page' => RedirectPage::class,
    'blog_page' => BlogPage::class,
    'contact_page' => ContactPage::class,
])]
#[ORM\HasLifecycleCallbacks]
abstract class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $tags = null;

    #[Gedmo\TreeLeft]
    #[ORM\Column]
    private ?int $lft = null;

    #[Gedmo\TreeLevel]
    #[ORM\Column]
    private ?int $lvl = null;

    #[Gedmo\TreeRight]
    #[ORM\Column]
    private ?int $rgt = null;

    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: Menu::class)]
    #[ORM\JoinColumn(name: 'tree_root', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Menu $root = null;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Menu $parent = null;

    #[ORM\OneToMany(targetEntity: Menu::class, mappedBy: 'parent')]
    #[ORM\OrderBy(value: ['lft' => 'ASC'])]
    private Collection $children;

    #[ORM\OneToMany(targetEntity: MenuLng::class, mappedBy: 'master', cascade: ['persist', 'remove'])]
    private $lngs;

    #[ORM\OneToOne(cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?File $leadingImage = null;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: MenuImage::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(["position" => "ASC"])]
    private Collection $menuImages;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $params = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $headHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $preBodyHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $posBodyHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $jsonData = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true, enumType: SkinMenu::class)]
    private ?SkinMenu $skin = null;

    public function __construct()
    {
        $this->lngs = new ArrayCollection();
        $this->menuImages = new ArrayCollection();
        $this->children = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function getRoot(): ?Menu
    {
        return $this->root;
    }
    public function setParent(?Menu $parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }
    public function getParent(): ?Menu
    {
        return $this->parent;
    }


    /**
     * @return Collection|MenuLng[]
     */
    public function getLngs(): Collection
    {
        return $this->lngs;
    }
    public function addLng(MenuLng $lngs): self
    {
        if (!$this->lngs->contains($lngs)) {
            $this->lngs[] = $lngs;
            $lngs->setMaster($this);
        }

        return $this;
    }
    public function removeLng(MenuLng $lngs): self
    {
        if ($this->lngs->contains($lngs)) {
            $this->lngs->removeElement($lngs);
            // set the owning side to null (unless already changed)
            if ($lngs->getMaster() === $this) {
                $lngs->setMaster(null);
            }
        }

        return $this;
    }
    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }
    /**
     * @param mixed $tags
     */
    public function setTags($tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getLvl(): int
    {
        return $this->lvl;
    }

    public function getLeadingImage(): ?File
    {
        return $this->leadingImage;
    }

    public function setLeadingImage(?File $leadingImage): self
    {
        $this->leadingImage = $leadingImage;

        return $this;
    }

    /**
     * @return Collection<int, MenuImage>
     */
    public function getMenuImages(): Collection
    {
        return $this->menuImages;
    }

    public function addMenuImage(MenuImage $menuImage): self
    {
        if (!$this->menuImages->contains($menuImage)) {
            $this->menuImages->add($menuImage);
            $menuImage->setMenu($this);
        }

        return $this;
    }

    public function removeMenuImage(MenuImage $menuImage): self
    {
        if ($this->menuImages->removeElement($menuImage)) {
            // set the owning side to null (unless already changed)
            if ($menuImage->getMenu() === $this) {
                $menuImage->setMenu(null);
            }
        }

        return $this;
    }

    public function getParams(): ?string
    {
        return $this->params;
    }

    public function setParams(?string $params): static
    {
        $this->params = $params;

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

    public function getJsonData(): ?string
    {
        return $this->jsonData;
    }

    public function setJsonData(?string $jsonData): static
    {
        $this->jsonData = $jsonData;

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

    public function getSkin(): ?SkinMenu
    {
        return $this->skin;
    }

    public function setSkin(?SkinMenu $skin): static
    {
        $this->skin = $skin;

        return $this;
    }
}
