<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\SyncProductToChannel;
use App\Repository\ChannelRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:product:sync',
    description: 'Publie la synchronisation d\'un produit vers tous les canaux actifs',
)]
class SyncProductCommand extends Command
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ChannelRepository $channelRepository,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('product', InputArgument::REQUIRED, 'ID ou SKU du produit à synchroniser');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $reference = (string) $input->getArgument('product');

        $product = ctype_digit($reference)
            ? $this->productRepository->find((int) $reference)
            : $this->productRepository->findOneBy(['sku' => $reference]);

        if (null === $product) {
            $io->error(sprintf('Produit introuvable : %s', $reference));

            return Command::FAILURE;
        }

        $channels = $this->channelRepository->findBy(['isActive' => true]);
        foreach ($channels as $channel) {
            $this->bus->dispatch(new SyncProductToChannel((int) $product->getId(), (int) $channel->getId()));
        }

        $io->success(sprintf(
            '%d message(s) de synchronisation publié(s) pour « %s ».',
            \count($channels),
            (string) $product->getSku(),
        ));

        return Command::SUCCESS;
    }
}
