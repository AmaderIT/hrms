<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class LeaveType extends Model
{
    use HasFactory, LogsActivity;

    const PAID = 1;
    const UNPAID = 0;
    const ENCASHMENT_PAID = 2;
    const UNPAID_LEAVE_ID = 8;

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    public static function leaveMode(){
        return [
            self::PAID => 'Paid',
            self::UNPAID => 'Unpaid',
            self::ENCASHMENT_PAID => 'Encashment paid'
        ];
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Leave type has been {$eventName}";
    }
}
