<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\AddBalance;

use BaksDev\Users\Profile\Balance\Entity\Event\ProfileBalanceEvent;
use BaksDev\Users\Profile\Balance\Entity\Invariable\ProfileBalanceInvariable;
use BaksDev\Users\Profile\Balance\Entity\ProfileBalance;
use BaksDev\Users\Profile\Balance\Repository\ProfileBalanceByProfiles\ProfileBalanceByProfilesInterface;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\Debt\ProfileBalanceDebtDTO;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\Invariable\ProfileBalanceInvariableDTO;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\ProfileBalanceDTO;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\ProfileBalanceHandler;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

final readonly class AddBalanceHandler
{
    public function __construct(
        private ProfileBalanceByProfilesInterface $ProfileBalanceByProfilesRepository,
        private ProfileBalanceHandler $ProfileBalanceHandler,
        private EntityManagerInterface $EntityManager,
    ) {}

    public function handle(AddBalanceDTO $command): string|ProfileBalance
    {
        $profileBalanceInvariable = $this->ProfileBalanceByProfilesRepository
            ->forProfile($command->getProfile())
            ->forSeller($command->getSeller())
            ->find();

        $profileBalanceDTO = new ProfileBalanceDTO();

        if(false === ($profileBalanceInvariable instanceof ProfileBalanceInvariable))
        {
            $profileBalanceInvariableDTO = new ProfileBalanceInvariableDTO()
                ->setSeller($command->getSeller())
                ->setProfile($command->getProfile());

            $profileBalanceDTO->setInvariable($profileBalanceInvariableDTO);

            $profileBalanceDebtDTO = new ProfileBalanceDebtDTO()->setBalance($command->getMoney());

            $profileBalanceDTO->setDebt($profileBalanceDebtDTO);
        }

        if(true === ($profileBalanceInvariable instanceof ProfileBalanceInvariable))
        {
            $profileBalanceEvent = $this->EntityManager
                ->getRepository(ProfileBalanceEvent::class)
                ->find($profileBalanceInvariable->getEvent());

            if(false === ($profileBalanceEvent instanceof ProfileBalanceEvent))
            {
                throw new InvalidArgumentException('Profile Balance Event was not found');
            }

            $profileBalanceEvent
                ->getDebt()
                ->addBalance($command->getMoney());

            $profileBalanceEvent->getDto($profileBalanceDTO);
        }

        return $this->ProfileBalanceHandler->handle($profileBalanceDTO);
    }
}