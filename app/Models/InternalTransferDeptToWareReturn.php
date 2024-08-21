<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalTransferDeptToWareReturn extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'internal_transfer_dept_to_ware_return';

    protected $fillable = ['internal_transfer_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];
}
