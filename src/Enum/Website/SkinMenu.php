<?php

namespace App\Enum\Website;

enum SkinMenu: string
{
    case MainSkin = 'MainSkin';
    case DarkSkin = 'DarkSkin';

    public function getSkinFile(): string
    {
        return match ($this) {
            self::MainSkin => '/website/frontend/main_skin.html.twig',
            self::DarkSkin => '/website/frontend/dark_skin.html.twig',
        };
    }
}
