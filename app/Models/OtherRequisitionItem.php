<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherRequisitionItem extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var string
     */
    protected $table = 'other_requisition_items';

    /**
     * @var string[]
     */
    protected $hidden = ["created_at", "updated_at"];

    public function unitName(){
        return $this->belongsTo(Unit::class, "unit_id",'id')->select('id','name');
    }
}
