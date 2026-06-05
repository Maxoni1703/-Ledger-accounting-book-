<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Transaction;

use App\Models\Transaction;
use App\MoonShine\Resources\Transaction\Pages\TransactionDetailPage;
use App\MoonShine\Resources\Transaction\Pages\TransactionFormPage;
use App\MoonShine\Resources\Transaction\Pages\TransactionIndexPage;
use App\Services\LedgerService;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\ImportExport\Contracts\HasImportExportContract;
use MoonShine\ImportExport\Traits\ImportExportConcern;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Date;

/**
 * @extends ModelResource<Transaction, TransactionIndexPage, TransactionFormPage, TransactionDetailPage>
 */
class TransactionResource extends ModelResource implements HasImportExportContract
{
    use ImportExportConcern;
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

    protected function exportFields(): iterable
    {
        return [
            ID::make(),
            Date::make('Дата', 'date'),
            Text::make('Описание', 'description'),
        ];
    }

    protected function afterCreated(DataWrapperContract $item): DataWrapperContract
    {
        $model = $item->original();

        if (isset($model->_entries_data)) {
            // Делегируем сохранение проводок в LedgerService
            app(LedgerService::class)->saveEntries($model, $model->_entries_data);
        }

        return $item;
    }

    protected function afterUpdated(DataWrapperContract $item): DataWrapperContract
    {
        $model = $item->original();

        if (isset($model->_entries_data)) {
            // Делегируем сохранение проводок в LedgerService
            app(LedgerService::class)->saveEntries($model, $model->_entries_data);
        }

        return $item;
    }
}
