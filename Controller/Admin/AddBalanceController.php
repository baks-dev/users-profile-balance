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

namespace BaksDev\Users\Profile\Balance\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\Profile\Balance\Entity\ProfileBalance;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\AddBalance\AddBalanceDTO;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\AddBalance\AddBalanceForm;
use BaksDev\Users\Profile\Balance\UseCase\Admin\Balance\AddBalance\AddBalanceHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_PROFILE_BALANCE_NEW')]
final class AddBalanceController extends AbstractController
{
    #[Route('/admin/profile/balance/add', name: 'admin.balance.add', methods: ['GET', 'POST'])]
    public function add(
        Request $request,
        AddBalanceHandler $addBalanceHandler,
    ): Response
    {
        $addBalanceDTO = new AddBalanceDTO()->setSeller($this->getProfileUid());

        // Форма
        $form = $this
            ->createForm(
                AddBalanceForm::class,
                $addBalanceDTO,
                ['action' => $this->generateUrl('users-profile-balance:admin.balance.add')],
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('add_profile_balance'))
        {
            $handle = $addBalanceHandler->handle($addBalanceDTO);

            $this->addFlash
            (
                'page.new',
                $handle instanceof ProfileBalance ? 'success.new' : 'danger.new',
                'users-profile-balance.admin',
                $handle,
            );

            return $handle instanceof ProfileBalance ? $this->redirectToRoute('users-profile-balance:admin.index') : $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}