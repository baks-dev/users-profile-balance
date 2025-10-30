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

namespace BaksDev\Users\Profile\Balance\Repository\AllProfilesBalance;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Users\Profile\Balance\Entity\Debt\ProfileBalanceDebt;
use BaksDev\Users\Profile\Balance\Entity\Invariable\ProfileBalanceInvariable;
use BaksDev\Users\Profile\Balance\Entity\ProfileBalance;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class AllProfilesBalanceRepository implements AllProfilesBalanceInterface
{
    private ?UserProfileUid $seller;

    private ?SearchDTO $search = null;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage,
    ) {}

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function forSeller(UserProfile|UserProfileUid $seller): self
    {
        $this->seller = ($seller instanceof UserProfile) ? $seller->getId() : $seller;
        return $this;
    }

    public function findPaginator(): PaginatorInterface
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('balance_invariable.profile AS profile')
            ->addSelect('balance_invariable.main AS main')
            ->from(ProfileBalanceInvariable::class, 'balance_invariable')
            ->where('balance_invariable.seller = :seller')
            ->setParameter(
                'seller',
                $this->seller instanceof UserProfileUid ? $this->seller : $this->UserProfileTokenStorage->getProfile(),
                UserProfileUid::TYPE
            );

        $dbal->join(
            'balance_invariable',
            ProfileBalance::class,
            'balance',
            'balance.event = balance_invariable.event'
        );

        $dbal
            ->addSelect('balance_debt.debt AS debt')
            ->addSelect('balance_debt.balance AS balance')
            ->join(
                'balance_invariable',
                ProfileBalanceDebt::class,
                'balance_debt',
                'balance_debt.event = balance_invariable.event'
            );


        // ПРОФИЛЬ ПОЛЬЗОВАТЕЛЯ

        // UserProfile
        $dbal->leftJoin(
            'balance_invariable',
            UserProfile::class,
            'profile',
            'profile.id = balance_invariable.profile'
        );

        $dbal
            ->leftJoin(
                'balance_invariable',
                UserProfileInfo::class,
                'profile_info',
                'profile_info.profile = balance_invariable.profile'
            );


        // Personal
        $dbal
            ->addSelect('profile_personal.username AS profile_username')
            ->leftJoin(
                'profile',
                UserProfilePersonal::class,
                'profile_personal',
                'profile_personal.event = profile.event'
            );


        /* Поиск */
        if(($this->search instanceof SearchDTO) && $this->search->getQuery())
        {
            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchLike('profile_personal.username');
        }

        $dbal->enableCache('users-profile-balance', 86400);

        return $this->paginator->fetchAllHydrate($dbal, AllProfilesBalanceResult::class);
    }
}