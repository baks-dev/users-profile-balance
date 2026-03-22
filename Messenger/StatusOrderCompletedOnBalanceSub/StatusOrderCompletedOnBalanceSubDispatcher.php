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

namespace BaksDev\Users\Profile\Balance\Messenger\StatusOrderCompletedOnBalanceSub;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Entity\Order;
use BaksDev\Orders\Order\Type\Status\OrderStatus\Collection\OrderStatusCompleted;
use BaksDev\Orders\Order\UseCase\Admin\Status\OrderStatusDTO;
use BaksDev\Orders\Order\UseCase\Admin\Status\OrderStatusHandler;
use BaksDev\Users\Profile\Balance\Messenger\ProfileBalanceMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class StatusOrderCompletedOnBalanceSubDispatcher
{
    public function __construct(
        private OrderStatusHandler $OrderStatusHandler,
        #[Target('usersProfileBalanceLogger')] private LoggerInterface $Logger,
        private MessageDispatchInterface $MessageDispatch,
    ) {}

    /**
     * Диспатчер меняет статус оплаченного заказа на "Выполнен" и отправляет сообщение на диспатчер
     * SubDebtAndBalanceDispatcher для проверки и возможной оплаты следующего заказа
     *
     * @see SubDebtAndBalanceDispatcher
     */
    public function __invoke(StatusOrderCompletedOnBalanceSubMessage $message): void
    {
        /** Изменение статуса заказа */
        $orderStatusDTO = new OrderStatusDTO(OrderStatusCompleted::STATUS, $message->getOrderEvent())
            ->setProfile($message->getSeller())
            ->setComment($message->getComment());

        $statusHandle = $this->OrderStatusHandler->handle($orderStatusDTO);

        if(false === ($statusHandle instanceof Order))
        {
            $this->Logger->critical(
                'users-profile-balance: Ошибка обновления статуса заказа',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }

        $this->Logger->info(
            'Заказ успешно оплачен',
            [self::class.':'.__LINE__, var_export($message, true)],
        );


        /** Отправляем сообщение для проверки остатка средств на балансе и возможной дальнейшей оплаты других заказов */
        $this->MessageDispatch->dispatch(
            message: new ProfileBalanceMessage($message->getBalanceId()),
            transport: 'users-profile-balance');
    }
}