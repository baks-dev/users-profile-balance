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

namespace BaksDev\Users\Profile\Balance\Entity\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Users\Profile\Balance\Entity\Debt\ProfileBalanceDebt;
use BaksDev\Users\Profile\Balance\Entity\Invariable\ProfileBalanceInvariable;
use BaksDev\Users\Profile\Balance\Entity\ProfileBalance;
use BaksDev\Users\Profile\Balance\Type\Event\ProfileBalanceEventUid;
use BaksDev\Users\Profile\Balance\Type\Id\ProfileBalanceUid;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'profile_balance_event')]
class ProfileBalanceEvent extends EntityEvent
{
    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ProfileBalanceEventUid::TYPE)]
    private ProfileBalanceEventUid $id;

    /**
     * Идентификатор ProfileBalance
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ProfileBalanceUid::TYPE, nullable: false)]
    private ?ProfileBalanceUid $main = null;

    /**
     * Постоянная величина
     */
    #[ORM\OneToOne(targetEntity: ProfileBalanceInvariable::class, mappedBy: 'event', cascade: ['all'])]
    private ?ProfileBalanceInvariable $invariable;

    /**
     * Задолженность
     */
    #[ORM\OneToOne(targetEntity: ProfileBalanceDebt::class, mappedBy: 'event', cascade: ['all'])]
    private ?ProfileBalanceDebt $debt = null;

    public function __construct()
    {
        $this->id = new ProfileBalanceEventUid();
    }

    /**
     * Идентификатор события
     */

    public function __clone()
    {
        $this->id = clone new ProfileBalanceEventUid();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getMain(): ?ProfileBalanceUid
    {
        return $this->main;
    }

    /**
     * Идентификатор ProfileBalance
     */
    public function setMain(ProfileBalanceUid|ProfileBalance $main): void
    {
        $this->main = $main instanceof ProfileBalance ? $main->getId() : $main;
    }

    public function getId(): ProfileBalanceEventUid
    {
        return $this->id;
    }

    public function getDto($dto): mixed
    {
        if($dto instanceof ProfileBalanceEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof ProfileBalanceEventInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function getInvariable(): ?ProfileBalanceInvariable
    {
        return $this->invariable;
    }

    public function setInvariable(ProfileBalanceInvariable|false $invariable): self
    {
        if($invariable instanceof ProfileBalanceInvariable)
        {
            $this->invariable = $invariable;
        }

        return $this;
    }

    public function getDebt(): ?ProfileBalanceDebt
    {
        return $this->debt ?: new ProfileBalanceDebt($this);
    }
}