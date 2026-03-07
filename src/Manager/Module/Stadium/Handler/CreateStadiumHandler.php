<?php

declare(strict_types=1);

namespace App\Manager\Module\Stadium\Handler;

use App\Entity\Stadium;
use App\Entity\StadiumBlock;
use App\Manager\Framework\Command\Interface\CommandHandlerInterface;
use App\Manager\Framework\Command\Interface\CommandInterface;
use App\Manager\Module\Stadium\Command\CreateStadiumCommand;
use App\Repository\ClubRepository;
use App\Repository\StadiumRepository;

readonly class CreateStadiumHandler implements CommandHandlerInterface
{
    public function __construct(
        private StadiumRepository $stadiumRepository,
        private ClubRepository $clubRepository
    ) {
    }

    public function supports(CommandInterface $command): bool
    {
        return $command instanceof CreateStadiumCommand;
    }

    public function handle(CommandInterface $command): void
    {
        /** @var CreateStadiumCommand $command */
        $club = $this->clubRepository->findOneByUuid($command->clubUuid);

        if ($club === null) {
            return;
        }

        $stadium = new Stadium();
        $stadium->setName($club->getName() . ' Stadium');
        $stadium->setClub($club);

        // Create 4 blocks as required
        $blockNames = ['Main Stand', 'Opposite Stand', 'North Stand', 'South Stand'];
        foreach ($blockNames as $name) {
            $block = new StadiumBlock();
            $block->setName($name);
            $block->setStandSeatCapacity(1500);
            $block->setSitSeatCapacity(800);
            $block->setVipLogeCapacity(200);
            $stadium->addBlock($block);
        }

        $this->stadiumRepository->persist($stadium);
    }
}
