<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'barcode',
        'model_name',
        'quantity',
        'excel_file_name',
        'sheet_name',
    ];
}
