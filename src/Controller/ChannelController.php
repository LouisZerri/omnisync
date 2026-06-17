<?php

declare(strict_types=1);

namespace App\Controller;

use App\Channel\Connector\ChannelConnector;
use App\Entity\Channel;
use App\Repository\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/canaux', name: 'app_channel_')]
#[IsGranted('ROLE_ADMIN')]
class ChannelController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ChannelRepository $channels, ChannelConnector $connector): Response
    {
        $channelList = $channels->findBy([], ['name' => 'ASC']);

        // Santé testée au rendu : pastille « en ligne / hors ligne » par canal.
        $health = [];
        foreach ($channelList as $channel) {
            $health[$channel->getId()] = $connector->testConnection($channel);
        }

        return $this->render('channel/index.html.twig', [
            'channels' => $channelList,
            'health' => $health,
        ]);
    }

    #[Route('/{id}/basculer', name: 'toggle', methods: ['POST'])]
    public function toggle(Request $request, Channel $channel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_channel_'.$channel->getId(), $request->getPayload()->getString('_token'))) {
            $channel->setIsActive(!$channel->isActive());
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'Le canal « %s » a été %s.',
                (string) $channel->getName(),
                $channel->isActive() ? 'activé' : 'désactivé',
            ));
        }

        return $this->redirectToRoute('app_channel_index');
    }
}
