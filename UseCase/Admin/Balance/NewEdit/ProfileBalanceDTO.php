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

namespace BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit;

use BaksDev\Users\Profile\Balance\Entity\Event\ProfileBalanceEventInterface;
use BaksDev\Users\Profile\Balance\Type\Event\ProfileBalanceEventUid;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\Debt\ProfileBalanceDebtDTO;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\Invariable\ProfileBalanceInvariableDTO;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProfileBalanceEvent */
final class ProfileBalanceDTO implements ProfileBalanceEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?ProfileBalanceEventUid $id = null;

    /**
     * Постоянная величина
     */
    private ?ProfileBalanceInvariableDTO $invariable;

    /**
     * Задолженность
     */
    private ProfileBalanceDebtDTO $debt;

    /**
     * Идентификатор события
     */
    public function getEvent(): ?ProfileBalanceEventUid
    {
        return $this->id;
    }

    public function getInvariable(): ?ProfileBalanceInvariableDTO
    {
        return $this->invariable;
    }

    public function setInvariable(?ProfileBalanceInvariableDTO $invariable): self
    {
        $this->invariable = $invariable;
        return $this;
    }

    public function getDebt(): ProfileBalanceDebtDTO
    {
        return $this->debt;
    }

    public function setDebt(ProfileBalanceDebtDTO $debt): self
    {
        $this->debt = $debt;
        return $this;
    }
}