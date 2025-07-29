<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariants extends Model
{
    use HasFactory;

    protected $table = 'product_variants';
    protected $primaryKey = 'variant_id';
    public $timestamps = true;
    protected $fillable = ['variant_type', 'variant_value', 'variant_product_id'];

}
