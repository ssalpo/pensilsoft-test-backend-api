<?php

namespace App\Models;

use Core\Model;

class Expense extends Model
{
    protected static string $table = 'expenses';

    protected static array $fillable = [
        'date',
        'sum',
        'comment'
    ];
}
