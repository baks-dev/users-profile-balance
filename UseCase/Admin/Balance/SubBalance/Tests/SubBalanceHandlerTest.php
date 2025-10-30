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

namespace BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\SubBalance\Tests;

use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\Balance\Entity\Event\ProfileBalanceEvent;
use BaksDev\Users\Profile\Balance\Entity\ProfileBalance;
use BaksDev\Users\Profile\Balance\Type\Id\ProfileBalanceUid;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\SubBalance\SubBalanceDTO;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\SubBalance\SubBalanceHandler;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\Tests\ProfileBalanceHandlerNewTest;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('users-profile-balance')]
#[Group('users-profile-balance-usecase')]
#[When(env: 'test')]
final class SubBalanceHandlerTest extends KernelTestCase
{
    #[DependsOnClass(ProfileBalanceHandlerNewTest::class)]
    public function testUseCase(): void
    {
        $EntityManager = self::getContainer()->get(EntityManagerInterface::class);

        $profileBalance = $EntityManager
            ->getRepository(ProfileBalance::class)
            ->find(ProfileBalanceUid::TEST);

        if($profileBalance instanceof ProfileBalance)
        {
            $profileBalanceEvent = $EntityManager
                ->getRepository(ProfileBalanceEvent::class)
                ->find($profileBalance->getEvent());

            $balance = $profileBalanceEvent->getDebt()->getBalance();
            $debt = $profileBalanceEvent->getDebt()->getDebt();

            $subBalanceDTO = new SubBalanceDTO()
                ->setProfile(new UserProfileUid(UserProfileUid::TEST))
                ->setSeller(new UserProfileUid(UserProfileUid::TEST))
                ->setMoney(new Money(1));

            $SubBalanceHandler = self::getContainer()->get(SubBalanceHandler::class);
            $handle = $SubBalanceHandler->handle($subBalanceDTO);

            self::assertInstanceOf(ProfileBalance::class, $handle);


            /** @var EntityManagerInterface $em */
            $EntityManager = self::getContainer()->get(EntityManagerInterface::class);

            $profileBalanceEvent = $EntityManager
                ->getRepository(ProfileBalanceEvent::class)
                ->find($handle->getEvent());

            self::assertEquals($profileBalanceEvent->getDebt()->getBalance()->getValue(), $balance->getValue() - 1);
            self::assertEquals($profileBalanceEvent->getDebt()->getDebt()->getValue(), $debt->getValue() - 1);
        }
    }
}