<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductData extends Model
{
    protected $table = 'product_data';
    protected $fillable = [
        'model_name',
        'quantity',
        'replacement_date',
        'excel_file_name',
    ];
}
