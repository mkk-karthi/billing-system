<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVarients extends Model
{
    use HasFactory;

    protected $table = 'product_varients';
    protected $primaryKey = 'varient_id';
    public $timestamps = true;
    protected $fillable = ['varient_type', 'varient_value', 'varient_product_id'];

}
