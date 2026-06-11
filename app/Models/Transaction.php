<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'date',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
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
