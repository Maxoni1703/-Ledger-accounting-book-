<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;
use App\MoonShine\Resources\Transaction\Pages\TransactionIndexPage;
use App\MoonShine\Resources\Transaction\Pages\TransactionFormPage;
use App\MoonShine\Resources\Transaction\Pages\TransactionDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends ModelResource<Transaction, TransactionIndexPage, TransactionFormPage, TransactionDetailPage>
 */
class TransactionResource extends ModelResource
{
    protected string $model = Transaction::class;

    protected string $title = 'Transactions';
    
    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            TransactionIndexPage::class,
            TransactionFormPage::class,
            TransactionDetailPage::class,
        ];
    }
}
