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

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Users\Profile\Balance\Repository\AllProfilesBalance\AllProfilesBalanceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[RoleSecurity('ROLE_PROFILE_BALANCE_INDEX')]
final class IndexController extends AbstractController
{
    #[Route('/admin/profiles/balance/{page<\d+>}', name: 'admin.index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        AllProfilesBalanceInterface $AllProfilesBalanceRepository,
        int $page = 0,
    ): Response
    {
        // Поиск
        $search = new SearchDTO();

        $searchForm = $this
            ->createForm(
                type: SearchForm::class,
                data: $search,
                options: ['action' => $this->generateUrl('users-profile-balance:admin.index')]
            )
            ->handleRequest($request);

        // Получаем список
        $query = $AllProfilesBalanceRepository
            ->search($search)
            ->forSeller($this->getProfileUid())
            ->findPaginator();

        return $this->render(['search' => $searchForm->createView(), 'query' => $query]);
    }
}