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

namespace BaksDev\Users\Profile\Balance\Repository\ProfileBalanceByProfiles\Tests;

use BaksDev\Users\Profile\Balance\Entity\Invariable\ProfileBalanceInvariable;
use BaksDev\Users\Profile\Balance\Repository\ProfileBalanceByProfiles\ProfileBalanceByProfilesInterface;
use BaksDev\Users\Profile\Balance\Repository\ProfileBalanceByProfiles\ProfileBalanceByProfilesRepository;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\Tests\ProfileBalanceHandlerNewTest;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('users-profile-balance')]
#[Group('users-profile-balance-repository')]
#[When(env: 'test')]
final class ProfileBalanceByProfilesRepositoryTest extends KernelTestCase
{
    #[DependsOnClass(ProfileBalanceHandlerNewTest::class)]
    public function testUseCase(): void
    {
        /** @var ProfileBalanceByProfilesRepository $ProfileBalanceByProfilesRepository */
        $ProfileBalanceByProfilesRepository = self::getContainer()->get(ProfileBalanceByProfilesInterface::class);

        $result = $ProfileBalanceByProfilesRepository
            ->forProfile(new UserProfileUid(UserProfileUid::TEST))
            ->forSeller(new UserProfileUid(UserProfileUid::TEST))
            ->find();

        self::assertInstanceOf(ProfileBalanceInvariable::class, $result);
    }
}