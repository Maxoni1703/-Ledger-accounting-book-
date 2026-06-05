<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Transaction\Pages;

use App\Models\Account;
use App\MoonShine\Resources\Transaction\TransactionResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use Throwable;


/**
 * @extends IndexPage<TransactionResource>
 */
class TransactionIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Date::make('Дата', 'date'),
            Text::make('Описание', 'description'),
        ];
    }

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            // Фильтр «Дата от» — ищет транзакции с этой даты включительно
            Date::make('Дата от', 'date')
                ->onApply(function ($query, $value) {
                    // whereDate — сравниваем только дату, без времени
                    return $query->whereDate('date', '>=', $value);
                }),

            // Фильтр «Дата до» — ищет транзакции до этой даты включительно
            Date::make('Дата до', 'date_to')
                ->onApply(function ($query, $value) {
                    return $query->whereDate('date', '<=', $value);
                }),

            // Фильтр по счёту — ищет транзакции у которых есть проводка по выбранному счёту
            Select::make('Счёт', 'account_id')
                ->options(
                    // Формируем список: ['id' => 'name', ...] из активных счетов
                    Account::where('is_active', true)
                        ->orderBy('code')
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->nullable()         // Позволяет сбросить фильтр (выбрать «не выбрано»)
                ->onApply(function ($query, $value) {
                    // whereHas — ищем транзакции у которых ЕСТЬ проводка с account_id = $value
                    return $query->whereHas('journalEntries', function ($q) use ($value) {
                        $q->where('account_id', $value);
                    });
                }),
        ];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        return [];
    }

    /**
     * @param  TableBuilder  $component
     *
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer()
        ];
    }
}
