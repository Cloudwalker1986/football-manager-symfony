<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DummyController extends AbstractController
{
    private string $dummyContent = '<div>dummy content</div>';
    #[Route('/forget_password', name: 'user_forgot_password')]
    public function index(): Response
    {
        return new Response($this->dummyContent);
    }
}
