<?php

namespace App\Models;

//Импортируем BelongsTo  нужен, чтобы указать, что проводка принадлежит одному счёту и одной транзакции
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class JournalEntry extends Model
{
        // $fillable — разрешаем массовое заполнение
        protected $fillable=[
            'transaction_id',
            'account_id',
            'amount',
            'type',
        ];
        public function transaction():BelongsTo
        {
            return $this->belongsTo(Transaction::class);
        }

        public function account():BelongsTo
        {
            return $this->belongsTo(Account::class);
        }
}
