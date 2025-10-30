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

namespace BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\Tests;

use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\Balance\Entity\Event\ProfileBalanceEvent;
use BaksDev\Users\Profile\Balance\Entity\ProfileBalance;
use BaksDev\Users\Profile\Balance\Type\Id\ProfileBalanceUid;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\Debt\ProfileBalanceDebtDTO;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\Invariable\ProfileBalanceInvariableDTO;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\ProfileBalanceDTO;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\ProfileBalanceHandler;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use PHPUnit\Framework\Attributes\Group;

#[Group('users-profile-balance')]
#[Group('users-profile-balance-usecase')]
#[Group('users-profile-balance-controller')]
#[Group('users-profile-balance-repository')]
#[When(env: 'test')]
final class ProfileBalanceHandlerNewTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $em */
        $EntityManager = self::getContainer()->get(EntityManagerInterface::class);

        $profileBalance = $EntityManager
            ->getRepository(ProfileBalance::class)
            ->find(ProfileBalanceUid::TEST);

        if($profileBalance instanceof ProfileBalance)
        {
            $EntityManager->remove($profileBalance);
        }

        $events = $EntityManager
            ->getRepository(ProfileBalanceEvent::class)
            ->findBy(['main' => ProfileBalanceUid::TEST]);

        foreach($events as $event)
        {
            $EntityManager->remove($event);
        }

        $EntityManager->flush();
        $EntityManager->clear();
    }

    public function testUseCase(): void
    {
        $profileBalanceDTO = new ProfileBalanceDTO();

        /**
         * Debt
         */
        $debt = rand(1, 10);
        $balance = rand(1, 10);
        $profileBalanceDebtDTO = new ProfileBalanceDebtDTO()
            ->setDebt(new Money($debt))
            ->setBalance(new Money($balance));

        $profileBalanceDTO->setDebt($profileBalanceDebtDTO);


        /**
         * Invariable
         */
        $profileBalanceInvariableDTO = new ProfileBalanceInvariableDTO()
            ->setProfile(new UserProfileUid(UserProfileUid::TEST))
            ->setSeller(new UserProfileUid(UserProfileUid::TEST));

        $profileBalanceDTO->setInvariable($profileBalanceInvariableDTO);

        $ProfileBalanceHandler = self::getContainer()->get(ProfileBalanceHandler::class);
        $handle = $ProfileBalanceHandler->handle($profileBalanceDTO);

        self::assertInstanceOf(ProfileBalance::class, $handle);
    }
}