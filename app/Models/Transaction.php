<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    // $fillable — разрешаем массовое заполнение
    protected $fillable=[
        'date',
        'description',
    ];
    // Указываю, какие поля нужно преобразовывать в определённые типы данных
    protected $casts = [
        'date' => 'date',  // Строка из БД → объект Carbon
    ];
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
        // hasMany(JournalEntry::class) означает:
        // в таблице journal_entries есть поле transaction_id, которое ссылается на id транзакции
    }

    public function getEntriesDataAttribute()
    {
        return $this->journalEntries->map(function($entry) {
            return [
                'account_id' => $entry->account_id,
                'amount' => $entry->amount,
                'type' => $entry->type,
            ];
        })->toArray();
    }
}
