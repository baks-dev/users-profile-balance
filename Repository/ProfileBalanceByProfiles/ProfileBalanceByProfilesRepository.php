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

namespace BaksDev\Users\Profile\Balance\Repository\ProfileBalanceByProfiles;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\Balance\Entity\Invariable\ProfileBalanceInvariable;
use BaksDev\Users\Profile\Balance\Entity\ProfileBalance;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use InvalidArgumentException;

final class ProfileBalanceByProfilesRepository implements ProfileBalanceByProfilesInterface
{
    private ?UserProfileUid $profile = null;

    private ?UserProfileUid $seller = null;

    public function __construct(
        private readonly ORMQueryBuilder $ORMQueryBuilder,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage
    ) {}

    public function forProfile(UserProfile|UserProfileUid $profile): self
    {
        $this->profile = ($profile instanceof UserProfile) ? $profile->getId() : $profile;
        return $this;
    }

    public function forSeller(UserProfile|UserProfileUid $seller): self
    {
        $this->seller = ($seller instanceof UserProfile) ? $seller->getId() : $seller;
        return $this;
    }

    public function find(): ?ProfileBalanceInvariable
    {
        if(false === ($this->profile instanceof UserProfileUid))
        {
            throw new InvalidArgumentException('Invalid Argument Profile');
        }

        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm
            ->select('invariable')
            ->from(ProfileBalanceInvariable::class, 'invariable')
            ->where('invariable.profile = :profile AND invariable.seller = :seller')
            ->setParameter('profile', $this->profile, UserProfileUid::TYPE)
            ->setParameter(
                'seller',
                $this->seller instanceof UserProfileUid ? $this->seller : $this->UserProfileTokenStorage->getProfile(),
                UserProfileUid::TYPE
            );

        $orm->join(
            ProfileBalance::class,
            'main',
            'WITH',
            'main.id = invariable.main'
        );

        return $orm->getOneOrNullResult();
    }
}