<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductHistory extends Model
{
    protected $fillable = [
        'model_name',
        'quantity',
        'replacement_date',
        'excel_file_name',
        'process',
        'management_code',
        'executor',
        'confirm',
        'note',
    ];

    protected $casts = [
        'replacement_date' => 'date',
    ];
}
