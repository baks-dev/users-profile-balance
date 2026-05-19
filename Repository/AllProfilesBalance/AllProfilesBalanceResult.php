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

use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\Balance\Type\Id\ProfileBalanceUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final readonly class AllProfilesBalanceResult
{
    public function __construct(
        private string $main,
        private string $profile,
        private int $debt,
        private int $balance,
        private string|null $profile_username,
        private string|null $account_email,
        private string|null $account_status,
    ) {}

    public function getMain(): ProfileBalanceUid
    {
        return new ProfileBalanceUid($this->main);
    }

    public function getProfile(): UserProfileUid
    {
        return new UserProfileUid($this->profile);
    }

    public function getDebt(): Money
    {
        return new Money($this->debt, true);
    }

    public function getBalance(): Money
    {
        return new Money($this->balance, true);
    }

    public function getProfileUsername(): ?string
    {
        return $this->profile_username;
    }

    public function getAccountEmail(): ?AccountEmail
    {
        return $this->account_email ? new AccountEmail($this->account_email) : null;
    }

    public function getAccountStatus(): ?string
    {
        return $this->account_status;
    }


}