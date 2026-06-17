<?php

declare(strict_types=1);

namespace App\Controller;

use App\Channel\Connector\ChannelConnector;
use App\Entity\Channel;
use App\Repository\ChannelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/canaux', name: 'app_channel_')]
#[IsGranted('ROLE_ADMIN')]
class ChannelController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ChannelRepository $channels): Response
    {
        return $this->render('channel/index.html.twig', [
            'channels' => $channels->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/{id}/tester', name: 'test', methods: ['GET'])]
    public function test(Channel $channel, ChannelConnector $connector): JsonResponse
    {
        return $this->json(['ok' => $connector->testConnection($channel)]);
    }
}
