<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Transaction\Pages;

use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use App\MoonShine\Resources\Transaction\TransactionResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Number;
use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Throwable;


/**
 * @extends FormPage<TransactionResource>
 */
class TransactionFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                Date::make('Дата', 'date')->required(),
                Textarea::make('Описание', 'description'),
                Json::make('Проводки', 'entries_data')
                    ->fields([
                        Select::make('Счёт', 'account_id')
                            ->options(Account::pluck('name', 'id')->toArray())
                            ->required(),
                        Number::make('Сумма', 'amount')
                            ->min(0.01)
                            ->step(0.01)
                            ->required(),
                        Select::make('Тип', 'type')
                            ->options(['debit' => 'Дебет', 'credit' => 'Кредит'])
                            ->required(),
                    ])
                    ->creatable(limit: null)
                    ->removable()
                    ->onApply(function (Model $item, $value) {
                        $item->_entries_data = $value;
                        return $item;
                    }),
            ]),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function formButtons(): ListOf
    {
        return parent::formButtons();
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'entries_data' => [
                'required',
                'array',
                'min:2',
                function ($attribute, $value, $fail) {
                    $debit = 0;
                    $credit = 0;
                    foreach((array)$value as $entry) {
                        if (isset($entry['amount']) && isset($entry['type'])) {
                            if ($entry['type'] === 'debit') {
                                $debit += (float) $entry['amount'];
                            } elseif ($entry['type'] === 'credit') {
                                $credit += (float) $entry['amount'];
                            }
                        }
                    }
                    if (abs($debit - $credit) > 0.001) {
                        $fail('Сумма дебета (' . $debit . ') не равна сумме кредита (' . $credit . ')');
                    }
                }
            ],
            'entries_data.*.account_id' => 'required',
            'entries_data.*.amount' => 'required|numeric|min:0.01',
            'entries_data.*.type' => 'required|in:debit,credit',
        ];
    }

    /**
     * @param  FormBuilder  $component
     *
     * @return FormBuilder
     */
    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
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
