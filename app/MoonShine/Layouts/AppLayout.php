<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Resources\Account\AccountResource;
use App\MoonShine\Resources\Transaction\TransactionResource;
use MoonShine\Laravel\Layouts\AppLayout as BaseAppLayout;
use MoonShine\MenuManager\MenuItem;

class AppLayout extends BaseAppLayout
{
    protected function menu(): array
    {
        return [
            MenuItem::make(AccountResource::class, 'Accounts'),
            MenuItem::make(TransactionResource::class, 'Transactions'),
        ];
    }
}
