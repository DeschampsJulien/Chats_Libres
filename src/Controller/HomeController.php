<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'app_name' => 'Chats Libres',
            'database_name' => 'chats_libres',
        ]);
    }

    #[Route('/test-db')]
    public function test(Connection $connection): Response
    {
        try {
            $connection->executeQuery('SELECT 1');
            return new Response('DB OK');
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }
    }
}
