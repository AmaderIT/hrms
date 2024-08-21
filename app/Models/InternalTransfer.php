<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class InternalTransfer extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;

    const OPERATION_TYPE_CHALLAN         = "delivery-challan";
    const OPERATION_CREATED              = 1;
    const OPERATION_AUTHORIZED           = 2;
    const OPERATION_SECURITY_CHECKED_OUT  = 3;
    const OPERATION_SECURITY_CHECKED_IN = 4;
    const OPERATION_RECEIVED             = 5;
    const OPERATION_REJECT               = 6;

    /**
     * @var string
     */
    protected $table = "internal_transfers";

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at", "deleted_at"];

    /**
     * @return HasMany
     */
    public function items()
    {
        return $this->hasMany(InternalTransferItems::class)->with("measurement");
    }

    /**
     * @return BelongsTo
     */
    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, "source_warehouse_id");
    }

    /**
     * @return BelongsTo
     */
    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, "destination_warehouse_id");
    }

    /**
     * @return BelongsTo
     */
    public function sourceDepartment()
    {
        return $this->belongsTo(Department::class, "source_department_id");
    }

    /**
     * @return BelongsTo
     */
    public function destinationDepartment()
    {
        return $this->belongsTo(Department::class, "destination_department_id");
    }

    /**
     * @return BelongsTo
     */
    public function destinationSupplier()
    {
        return $this->belongsTo(Supplier::class,"to_supplier_id","id")->select('id','name','bin');
    }

    /**
     * @return BelongsTo
     */
    public function sourceSupplier()
    {
        return $this->belongsTo(Supplier::class,"from_supplier_id","id")->select('id','name','bin');
    }

    /**
     * @return BelongsTo
     */
    public function preparedBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, "updated_by");
    }

    /**
     * @return BelongsTo
     */
    public function authorizedBy()
    {
        return $this->belongsTo(User::class, "authorized_by");
    }

    /**
     * @return BelongsTo
     */
    public function securityCheckedOutBy()
    {
        return $this->belongsTo(User::class, "dispatch_security_checked");
    }

    /**
     * @return BelongsTo
     */
    public function securityCheckedBy()
    {
        return $this->belongsTo(User::class, "security_checked_by");
    }

    /**
     * @return BelongsTo
     */
    public function securityCheckedInBy()
    {
        return $this->belongsTo(User::class, "receive_security_checked");
    }

    /**
     * @return BelongsTo
     */
    public function deliveredBy()
    {
        return $this->belongsTo(User::class, "delivered_by");
    }

    /**
     * @return BelongsTo
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, "received_by");
    }

    /**
     * @return BelongsTo
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, "rejected_by");
    }

    /**
     * @return BelongsTo
     */
    public function parents(){
        return $this->belongsTo(InternalTransfer::class , 'parent_id');
    }
}
