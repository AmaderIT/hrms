<?php

use App\Models\Setting;
use Illuminate\Pagination\LengthAwarePaginator;
use Ramsey\Uuid\Uuid;

class Functions
{
    /**
     * @var int
     */
    protected static $paginate = 10;

    /**
     * @return int
     */
    public static function getPaginate()
    {
        return (int) Setting::where("name", "per_page")->first()->value;
    }

    /**
     * @param $collection
     * @param $path
     * @param null $perPage
     * @return LengthAwarePaginator
     */
    public static function customPaginate($collection, $path, $perPage = null)
    {
        if(is_null($perPage)) $perPage = self::getPaginate();

        return new LengthAwarePaginator(
            $collection->forPage(LengthAwarePaginator::resolveCurrentPage(), $perPage),
            $collection->count(),
            $perPage,
            LengthAwarePaginator::resolveCurrentPage(),
            ["path" => $path]
        );
    }

    /**
     * @param string $datepicker
     * @return array
     */
    public static function getMonthAndYearFromDatePicker(string $datepicker)
    {
        $parse = explode("-", $datepicker);

        return [
            "month" => (int)$parse[0],
            "year"  => (int)$parse[1],
        ];
    }

    /**
     * @param $start
     * @param $end
     * @param string $format
     * @return array
     * @throws Exception
     */
    public static function dateRange($start, $end, $format = 'Y-m-d')
    {
        $array = [];
        $interval = new DateInterval('P1D');

        $realEnd = new DateTime($end);
        $realEnd->add($interval);

        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

        foreach($period as $date) $array[] = $date->format($format);

        return $array;
    }

    /**
     * @param $dateRange
     * @param $separator
     * @return array
     */
    public static function dateRangePicker($dateRange, $separator)
    {
        $dateRange = explode($separator, $dateRange);

        return [
            "start_date"=> $dateRange[0],
            "end_date"  => $dateRange[1]
        ];
    }

    /**
     * @return string
     */
    public static function getNewUuid()
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * @return []
     */
    public static function getOnlineAttendanceInfo($checkInDateTime = null)
    {
        $lateCheckout = false;
        $checkInTime = false;

        $checkInDateTime = is_null($checkInDateTime) ? date('Y-m-d H:i:s'): $checkInDateTime;
        $checkInDate = date('Y-m-d', strtotime($checkInDateTime));
        $lateCheckoutDate  = date('Y-m-d', strtotime($checkInDate. ' + 1 days'));

        $extraTimeStart = date('Y-m-d H:i:s', strtotime(date($lateCheckoutDate . ' 00:00:00')));
        $extraTimeEnd = date('Y-m-d H:i:s', strtotime(date($lateCheckoutDate . ' 05:59:59')));

        $checkInStartTime = date('Y-m-d H:i:s', strtotime(date($checkInDate . ' 06:00:00')));
        $checkInEndTime = date('Y-m-d H:i:s', strtotime(date($checkInDate . ' 23:59:59')));

        /*$extraTimeStart = date('Y-m-d H:i:s', strtotime(date('Y-m-d 16:00:00')));
        $extraTimeEnd = date('Y-m-d H:i:s', strtotime(date('Y-m-d 18:59:59')));*/

        /*$checkInStartTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d 06:00:00')));
        $checkInEndTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d 23:59:59')));*/



        if ($checkInDateTime >= $extraTimeStart && $checkInDateTime <= $extraTimeEnd) {
            $lateCheckout = true;
        }

        /** Check this time is in checkIn time or not **/
        if($checkInDateTime >= $checkInStartTime && $checkInDateTime <= $checkInEndTime){
            $checkInTime = true;
        }

        return [
            'late_checkout' => $lateCheckout,
            'checkin_time' => $checkInTime,
            'checkin_start_time' => $checkInStartTime,
            'checkin_end_time' => $checkInEndTime,
            'late_checkout_start_time' => $extraTimeStart,
            'late_checkout_end_time' => $extraTimeEnd,
            'date_now' => $checkInDateTime
        ];
    }

    public static function getAllMonth($allMonth = true, $totalMonth = 12, $startMonth = false, $loanTenure = false)
    {
        if ($allMonth) {
            return [
                '01' => 'January',
                '02' => 'February',
                '03' => 'march',
                '04' => 'April',
                '05' => 'May',
                '06' => 'June',
                '07' => 'July',
                '08' => 'August',
                '09' => 'September',
                '10' => 'October',
                '11' => 'November',
                '12' => 'December'
            ];
        } else {
            $months = [];
            $startMonth = date('Y-m-01');
            $endMonth = date('Y-m-d', strtotime("+$totalMonth months", strtotime($startMonth)));
            for ($i = $startMonth; $i < $endMonth; $i = date('Y-m-d', strtotime("+1 months", strtotime($i)))) {
                $months[$i] = date('F, Y', strtotime($i));
            }

            return $months;
        }
    }
}
