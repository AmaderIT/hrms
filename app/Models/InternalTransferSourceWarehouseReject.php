<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalTransferSourceWarehouseReject extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'internal_transfer_source_warehouse_reject';

    protected $fillable = ['internal_transfer_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];
}
