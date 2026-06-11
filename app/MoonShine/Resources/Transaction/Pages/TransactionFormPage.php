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
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Number;
use App\Models\Account;
use App\Services\LedgerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Throwable;


/**
 * @extends FormPage<TransactionResource>
 */
class TransactionFormPage extends FormPage
{
    /**
     * Собираем форму для ввода транзакции и её проводок.
     * JSON-поле хранит массив проводок, который потом передаётся в сервис.
     *
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
                        // MoonShine передаёт сюда массив проводок из формы.
                        // Сохраняем его во временном свойстве, чтобы потом записать в БД.
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

    /**
     * Проверяем, что проводки есть и что сумма дебета равна сумме кредита.
     * Это важно, потому что иначе транзакция не будет корректной по правилу двойной записи.
     */
    protected function rules(DataWrapperContract $item): array
    {
        return [
            'entries_data' => [
                'required',
                'array',
                'min:2',
                function ($attribute, $value, $fail) {
                    try {
                        app(LedgerService::class)->validateBalance((array) $value);
                    } catch (ValidationException $exception) {
                        $fail($exception->errors()['entries_data'][0] ?? 'Баланс проводок не сходится.');
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
