<?php

namespace App\Controller\Website\Frontend;

use Symfony\Component\HttpFoundation\Request;

interface CmsControllerImpl
{
    public function index(Request $request, string $menuId, ?string $token);
}
