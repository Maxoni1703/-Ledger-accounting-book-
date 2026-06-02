<?php

namespace App\Models;


//импорты нгеобходимых классов из Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    //  Разрешаем массовое заполнение этих полей
    // Без этого Laravel будет блокировать создание/обновление через create() или update()
    protected $fillable = [
        'name',      // Название счёта (например "Касса")
        'code',      // Уникальный код счёта (например "1010")
        'type',      // Тип счёта: asset, liability, equity, revenue, expense
        'is_active'  // Активен ли счёт (true/false)
    ];

    // Указываем, какие поля нужно преобразовывать в определённые типы данных
    protected $casts = [
        'is_active' => 'boolean',  // Превращаем 0/1 в true/false
    ];

    // Связь "один ко многим" 
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
        // hasMany(JournalEntry::class) означает:
        // в таблице journal_entries есть поле account_id, которое ссылается на id счёта
    }
}