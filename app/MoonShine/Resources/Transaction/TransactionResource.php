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
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;

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

    protected function afterCreated(DataWrapperContract $item): DataWrapperContract
    {
        $this->saveEntries($item->original());
        return $item;
    }

    protected function afterUpdated(DataWrapperContract $item): DataWrapperContract
    {
        $this->saveEntries($item->original());
        return $item;
    }

    private function saveEntries($model)
    {
        if (isset($model->_entries_data)) {
            $model->journalEntries()->delete();
            
            foreach($model->_entries_data as $entry) {
                if (empty($entry['account_id']) || empty($entry['amount']) || empty($entry['type'])) {
                    continue;
                }
                $model->journalEntries()->create([
                    'account_id' => $entry['account_id'],
                    'amount' => $entry['amount'],
                    'type' => $entry['type'],
                ]);
            }
        }
    }
}
