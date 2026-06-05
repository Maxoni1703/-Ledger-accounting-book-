<?php

declare(strict_types=1);

namespace App\MoonShine;

use App\MoonShine\Resources\Account\AccountResource;
use App\MoonShine\Resources\Transaction\TransactionResource;
use App\MoonShine\Layouts\AppLayout;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use Illuminate\Support\ServiceProvider;

class MoonShineServiceProvider extends ServiceProvider
{
    public function boot(CoreContract $core): void
    {
        $core
            ->resources([
                AccountResource::class,
                TransactionResource::class,
            ])
            ->pages([
                ...$core->getConfig()->getPages(),
                \App\MoonShine\Pages\TrialBalancePage::class,
            ]);
    }

    public function register(): void
    {
        config()->set('moonshine.layout', AppLayout::class);
    }
}
