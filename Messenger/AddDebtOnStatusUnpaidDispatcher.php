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

namespace BaksDev\Users\Profile\Balance\Messenger;

use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Orders\Order\Entity\Products\OrderProduct;
use BaksDev\Orders\Order\Messenger\OrderMessage;
use BaksDev\Orders\Order\Repository\OrderEvent\OrderEventInterface;
use BaksDev\Orders\Order\Type\Status\OrderStatus\Collection\OrderStatusUnpaid;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\Balance\Entity\ProfileBalance;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\AddDebt\AddDebtDTO;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\AddDebt\AddDebtHandler;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class AddDebtOnStatusUnpaidDispatcher
{
    public function __construct(
        private DeduplicatorInterface $Deduplicator,
        private OrderEventInterface $OrderEventRepository,
        #[Target('usersProfileBalanceLogger')] private LoggerInterface $Logger,
        private AddDebtHandler $AddDebtHandler,
        private UserProfileTokenStorageInterface $UserProfileTokenStorage,
    ) {}

    /**
     * Диспатчер увеличивает задолженность пользователя при отправке ег заказа в статус "Не оплачен"
     */
    public function __invoke(OrderMessage $message): void
    {
        $Deduplicator = $this->Deduplicator
            ->namespace('users-profile-balance')
            ->deduplication([(string) $message->getId(), self::class]);

        if($Deduplicator->isExecuted())
        {
            return;
        }

        $orderEvent = $this->OrderEventRepository->find($message->getEvent());

        if(false === $orderEvent)
        {
            $this->Logger->critical(
                'users-profile-balance: Не найдено событие Order',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }

        /**
         * Если статус не Unpaid «Не оплачен» - завершаем обработчик
         */
        if(false === $orderEvent->isStatusEquals(OrderStatusUnpaid::class))
        {
            return;
        }

        $priceTotal = new Money(0);

        /** @var OrderProduct $product */
        foreach($orderEvent->getProduct() as $product)
        {
            $priceTotal->add($product->getPrice());
        }


        $addDebtDTO = new AddDebtDTO()
            ->setProfile($orderEvent->getOrderProfile())
            ->setSeller($this->UserProfileTokenStorage->getProfile())
            ->setMoney($priceTotal);

        $handle = $this->AddDebtHandler->handle($addDebtDTO);

        if($handle instanceof ProfileBalance)
        {
            $this->Logger->info(
                'users-profile-balance: Успешно увеличена задолженность',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }

        $this->Logger->critical(
            'users-profile-balance: Ошибка при увеличении задолженности',
            [self::class.':'.__LINE__, var_export($message, true)],
        );
    }
}