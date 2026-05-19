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

namespace BaksDev\Users\Profile\Balance\Entity\Debt;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\Balance\Entity\Event\ProfileBalanceEvent;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'profile_balance_debt')]
class ProfileBalanceDebt extends EntityEvent
{
    /** Profile Balance Event */
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: ProfileBalanceEvent::class, inversedBy: 'debt')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private ProfileBalanceEvent $event;

    /** Задолженность покупателя */
    #[Assert\NotBlank]
    #[ORM\Column(type: Money::TYPE)]
    private ?Money $debt = null;

    /** Баланс покупателя */
    #[Assert\NotBlank]
    #[ORM\Column(type: Money::TYPE)]
    private ?Money $balance = null;

    public function __construct(ProfileBalanceEvent $event)
    {
        $this->event = $event;
    }

    public function getDebt(): Money
    {
        return $this->debt ?: new Money(0);
    }

    public function getBalance(): Money
    {
        return $this->balance ?: new Money(0);
    }

    public function addDebt(Money $money): self
    {
        if(false === isset($this->debt))
        {
            $this->debt = new Money($money);
            return $this;
        }

        $this->debt->add($money);
        return $this;
    }

    public function subDebt(Money $money): bool
    {
        if(false === isset($this->debt))
        {
            return false;
        }

        $this->debt->sub($money);
        return true;
    }

    public function addBalance(Money $money): self
    {
        if(false === isset($this->balance))
        {
            $this->balance = new Money($money);
            return $this;
        }

        $this->balance->add($money);
        return $this;
    }

    public function subBalance(Money $money): bool
    {
        if(false === isset($this->balance))
        {
            return false;
        }

        $this->balance->sub($money);
        return true;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof ProfileBalanceDebtInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof ProfileBalanceDebtInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}