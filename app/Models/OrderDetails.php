<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    use HasFactory;
    protected $table = 'order_details';
    protected $primaryKey = 'order_details_id';
    public $timestamps = true;

    protected $fillable = ['order_details_order_id', 'order_details_product_id', 'order_details_product_qty', 'order_details_price', 'order_details_tax'];

    /**
     * Get the product associated with the OrderDetails
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product()
    {
        return $this->hasOne(Products::class, 'product_id', 'order_details_product_id');
    }
}
