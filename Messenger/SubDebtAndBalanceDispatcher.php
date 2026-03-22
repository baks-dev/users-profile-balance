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

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Entity\Event\OrderEvent;
use BaksDev\Orders\Order\Entity\Products\OrderProduct;
use BaksDev\Orders\Order\Repository\GetOldestUnpaidOrder\GetOldestUnpaidOrderInterface;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\Balance\Entity\Event\ProfileBalanceEvent;
use BaksDev\Users\Profile\Balance\Entity\ProfileBalance;
use BaksDev\Users\Profile\Balance\Messenger\StatusOrderCompletedOnBalanceSub\StatusOrderCompletedOnBalanceSubMessage;
use BaksDev\Users\Profile\Balance\Repository\CurrentProfileBalanceEvent\CurrentProfileBalanceEventInterface;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\SubBalance\SubBalanceDTO;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\SubBalance\SubBalanceHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class SubDebtAndBalanceDispatcher
{
    public function __construct(
        private GetOldestUnpaidOrderInterface $GetOldestUnpaidOrderRepository,
        #[Target('usersProfileBalanceLogger')] private LoggerInterface $Logger,
        private SubBalanceHandler $SubBalanceHandler,
        private MessageDispatchInterface $MessageDispatch,
        private CurrentProfileBalanceEventInterface $CurrentProfileBalanceEventRepository,
    ) {}

    /**
     * Диспатчер проверяет наличие заказов данного покупателя в статусе "Не оплачен", проверяет возможность оплаты
     * наиболее старого из них с учетом баланса пользователя и, при необходимости, снимает средства с баланса и
     * задолженности пользователя. При этом отправляется сообщение для диспатчера
     * StatusOrderCompletedOnBalanceSubDispatcher на изменение статуса оплаченного заказа на "Выполнен"
     *
     * @see StatusOrderCompletedOnBalanceSubDispatcher
     */
    public function __invoke(ProfileBalanceMessage $message): void
    {
        /** Получаем данные о балансе покупателя, его профиль и профиль магазина */
        $profileBalanceEvent = $this->CurrentProfileBalanceEventRepository->get($message->getId());

        if(false === ($profileBalanceEvent instanceof ProfileBalanceEvent))
        {
            $this->Logger->critical(
                'users-profile-balance: Событие ProfileBalanceEvent не было найдено',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }

        $balance = $profileBalanceEvent->getDebt()->getBalance();

        $profile = $profileBalanceEvent->getInvariable()->getProfile();
        $seller = $profileBalanceEvent->getInvariable()->getSeller();


        /** Получаем наиболее старый заказ данного покупателя со статусом "неоплачено" */
        $orderEvent = $this->GetOldestUnpaidOrderRepository->forProfile($profile)->forSeller($seller)->get();

        if(false === ($orderEvent instanceof OrderEvent))
        {
            $this->Logger->warning(
                'Для данного покупателя не было найдено неоплаченных заказов',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }


        /** Подсчет общей стоимости заказа */
        $total = 0;

        /** @var OrderProduct $product */
        foreach($orderEvent->getProduct() as $product)
        {
            $total += $product->getPrice()->getValue() * $product->getTotal();
        }


        /** Определяем - можно ли оплатить заказ с имеющимися на балансе средствами */
        if($balance->getValue() >= $total)
        {
            /** Снятие баланса и задолженности */
            $subBalanceDTO = new SubBalanceDTO();
            $subBalanceDTO
                ->setProfile($profile)
                ->setSeller($seller)
                ->setMoney(new Money($total));

            $subBalanceHandle = $this->SubBalanceHandler->handle($subBalanceDTO);

            if(false === ($subBalanceHandle instanceof ProfileBalance))
            {
                $this->Logger->critical(
                    'users-profile-balance: Ошибка уменьшения баланса',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->Logger->info(
                'Сняты баланс и задолженность за неоплаченный заказ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            $this->MessageDispatch->dispatch(
                message: new StatusOrderCompletedOnBalanceSubMessage(
                    $orderEvent->getId(),
                    $profile,
                    $seller,
                    $orderEvent->getComment(),
                    $message->getId(),
                ),
                transport: 'users-profile-balance',
            );
        }
    }
}