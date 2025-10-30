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

namespace BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\SubBalance;

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Core\Validator\ValidatorCollectionInterface;
use BaksDev\Files\Resources\Upload\File\FileUploadInterface;
use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use BaksDev\Users\Profile\Balance\Entity\Event\ProfileBalanceEvent;
use BaksDev\Users\Profile\Balance\Entity\Invariable\ProfileBalanceInvariable;
use BaksDev\Users\Profile\Balance\Entity\ProfileBalance;
use BaksDev\Users\Profile\Balance\Repository\ProfileBalanceByProfiles\ProfileBalanceByProfilesInterface;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\ProfileBalanceDTO;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

final class SubBalanceHandler extends AbstractHandler
{
    public function __construct(
        private readonly ProfileBalanceByProfilesInterface $ProfileBalanceByProfilesRepository,

        EntityManagerInterface $entityManager,
        MessageDispatchInterface $messageDispatch,
        ValidatorCollectionInterface $validatorCollection,
        ImageUploadInterface $imageUpload,
        FileUploadInterface $fileUpload,
    )
    {
        parent::__construct($entityManager, $messageDispatch, $validatorCollection, $imageUpload, $fileUpload);
    }

    /** @see ProfileBalance */
    public function handle(SubBalanceDTO $command): string|ProfileBalance
    {
        $profileBalanceInvariable = $this->ProfileBalanceByProfilesRepository
            ->forProfile($command->getProfile())
            ->forSeller($command->getSeller())
            ->find();

        $profileBalanceDTO = new ProfileBalanceDTO();

        if(false === ($profileBalanceInvariable instanceof ProfileBalanceInvariable))
        {
            throw new InvalidArgumentException('Profile Balance Invariable was not found');
        }

        $profileBalanceEvent = $this
            ->getRepository(ProfileBalanceEvent::class)
            ->find($profileBalanceInvariable->getEvent());

        if(false === ($profileBalanceEvent instanceof ProfileBalanceEvent))
        {
            throw new InvalidArgumentException('Profile Balance Event was not found');
        }

        /** Снимаем баланс */
        $profileBalanceEvent
            ->getDebt()
            ->subBalance($command->getMoney());

        /** Снимаем задолженность */
        $profileBalanceEvent
            ->getDebt()
            ->subDebt($command->getMoney());

        $profileBalanceEvent->getDto($profileBalanceDTO);

        $this->setCommand($profileBalanceDTO);
        $this->preEventPersistOrUpdate(ProfileBalance::class, ProfileBalanceEvent::class);


        /** Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->flush();

        $this->messageDispatch->addClearCacheOther('users-profile-balance');

        return $this->main;
    }
}