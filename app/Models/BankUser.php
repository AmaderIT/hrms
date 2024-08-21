<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankUser extends Model
{
    use HasFactory;

    const TYPE_SAVING = "Saving";
    const TYPE_CURRENT = "Current";
    const TYPE_DEPOSIT = "Deposit";

    /**
     * @var string
     */
    protected $table = "bank_user";

    /**
     * @return BelongsTo
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * @return BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @var array
     */
    protected $guarded = ["id"];
}
