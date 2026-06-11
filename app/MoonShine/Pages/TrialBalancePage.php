<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Text;
use MoonShine\Support\Enums\FormMethod;
use App\Services\LedgerService;

class TrialBalancePage extends Page
{
    protected string $title = 'Оборотно-сальдовая ведомость';

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        $request = request();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $ledgerService = app(LedgerService::class);
        $reportData = $ledgerService->getTrialBalance($dateFrom, $dateTo);

        return [
            Box::make([
                FormBuilder::make(route('moonshine.page', ['pageUri' => $this->getUriKey()]))
                    ->method(FormMethod::GET)
                    ->fields([
                        Date::make('Период с', 'date_from')
                            ->default($dateFrom),
                        Date::make('по', 'date_to')
                            ->default($dateTo),
                    ])
                    ->submit('Сформировать', ['class' => 'btn-primary'])
            ]),

            Box::make([
                TableBuilder::make()
                    ->fields([
                        Text::make('Код', 'account_code'),
                        Text::make('Счёт', 'account_name'),
                        Text::make('Начало (Дт)', 'opening_debit')->badge('green'),
                        Text::make('Начало (Кт)', 'opening_credit')->badge('red'),
                        Text::make('Оборот (Дт)', 'turnover_debit')->badge('gray'),
                        Text::make('Оборот (Кт)', 'turnover_credit')->badge('gray'),
                        Text::make('Конец (Дт)', 'closing_debit')->badge('green'),
                        Text::make('Конец (Кт)', 'closing_credit')->badge('red'),
                    ])
                    ->items($reportData)
            ])
        ];
    }
}
