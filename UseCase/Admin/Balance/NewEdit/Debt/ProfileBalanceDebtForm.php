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

namespace BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\NewEdit\Debt;

use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProfileBalanceDebtForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('debt', MoneyType::class,
            [
                'attr' => [
                    'data-min' => new Money(1)
                ],
                'currency' => false,
                'auto_initialize' => false,
                'scale' => 0,
                'required' => false,
            ]);

        $builder->get('debt')
            ->addModelTransformer(
                new CallbackTransformer(
                    function(?Money $money) {
                        return $money instanceof Money ? $money->getValue() : $money;
                    },
                    function(?int $money) {
                        return new Money($money);
                    },
                ),
            );

        $builder->add('balance', MoneyType::class,
            [
                'attr' => [
                    'data-min' => new Money(1)
                ],
                'currency' => false,
                'auto_initialize' => false,
                'scale' => 0,
                'required' => false,
            ]);

        $builder->get('balance')
            ->addModelTransformer(
                new CallbackTransformer(
                    function($money) {
                        return $money instanceof Money ? $money->getValue() : $money;
                    },
                    function($money) {
                        return new Money($money);
                    },
                ),
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProfileBalanceDebtDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}