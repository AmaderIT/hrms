<?php

//const URL = '127.0.0.9:2000/';
const CHALLAN_OPEN = 1;
const CHALLAN_PENDING_RETURN = 2;
const CHALLAN_CLOSE = 3;
const GENERAL_WORKFLOW = 1;
const VENDOR_WORKFLOW = 2;
const DELIVERY_CHALLAN_FOLDER = 'delivery_challan';
const RETURN_PENDING = 0;
const RETURN_COMPLETE = 1;
const RETURN_NOT_APPLICABLE = 2;
const DEFAULT_DELIVERED_BY = 57;

use App\Models\Setting;
use App\Models\Termination;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

if (!function_exists('getMonthNameFromMonthNumber')) {
    function getMonthNameFromMonthNumber($monthNumber)
    {
        return date("F", mktime(0, 0, 0, $monthNumber, 1));
    }
}

if (!function_exists('create_fingerprint_data')) {
    function create_fingerprint_data($name, $uid)
    {
        $token = json_decode(generate_token('admin', 'password'))->token;
        $status = json_decode(create_user($name, $uid, $token))->message;

        if ($status == 'success') {
            return true;
        }

        return false;
    }
}

if (!function_exists('generate_token')) {
    function generate_token($username, $password)
    {
        $fields = array('username' => $username, 'password' => $password);
        $headers = array();
        $url = URL . 'gentoken';
        return invoke_curl($url, $headers, $fields, '', 'POST');
    }
}

if (!function_exists('create_user')) {
    function create_user($name, $uid, $token)
    {
        $fields = array('name' => $name, 'uid' => $uid);
        $headers = array();
        $url = URL;
        $params = '?token=' . $token;
        return invoke_curl($url, $headers, $fields, $params, 'POST');
    }
}

if (!function_exists('invoke_curl')) {
    function invoke_curl($url, $headers = [], $fields = [], $params, $method = 'GET')
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => $headers
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}


/**
 * Delete file from storage according to env settings
 *
 * @param $destinationPath
 * @param $file
 * @param string $disk
 */
function deleteStorageFile($destinationPath, $file, $disk = '')
{
    try {
        if ($file != NULL) {
            if ($disk == '') {
                Storage::delete($destinationPath . $file);
            } else {
                Storage::disk($disk)->delete($destinationPath . $file);
            }
        }
    } catch (Exception $e) {

    }
}

/**
 * use for showing image
 * return image url
 * @param string $image
 * @return string
 */
function check_storage_image_exists($image = '')
{
    if (Storage::exists($image)) {
        return Storage::url($image);
    } else {
        return Storage::url($image);
    }

}

function getChallanStatus($status)
{
    if ($status == CHALLAN_OPEN) {
        return '<span class="badge badge-info">Open</span>';
    } elseif ($status == CHALLAN_PENDING_RETURN) {
        return '<span class="badge badge-primary">Return Pending</span>';
    } elseif ($status == CHALLAN_CLOSE) {
        return '<span class="badge badge-success">Close</span>';
    }
}

function getChallanStatusLabel($status)
{
    if ($status == CHALLAN_OPEN) {
        return 'Open';
    } elseif ($status == CHALLAN_PENDING_RETURN) {
        return 'Return Pending';
    } elseif ($status == CHALLAN_CLOSE) {
        return 'Close';
    }
}

function getApproveStatusLabel($status)
{
    if ($status == \App\Models\InternalTransfer::OPERATION_CREATED) {
        return 'Prepared';
    } elseif ($status == \App\Models\InternalTransfer::OPERATION_AUTHORIZED) {
        return 'Authorized';
    } elseif ($status == \App\Models\InternalTransfer::OPERATION_SECURITY_CHECKED_OUT) {
        return 'Security Checked Out';
    } elseif ($status == \App\Models\InternalTransfer::OPERATION_SECURITY_CHECKED_IN) {
        return 'Security Checked In';
    } elseif ($status == \App\Models\InternalTransfer::OPERATION_RECEIVED) {
        return 'Received';
    } elseif ($status == \App\Models\InternalTransfer::OPERATION_REJECT) {
        return 'Rejected';
    }
}

function getReturnStatus($status)
{
    if ($status == RETURN_PENDING) {
        return '<span class="badge badge-info">Returnable</span>';
    } elseif ($status == RETURN_COMPLETE) {
        return '<span class="badge badge-success">Returned</span>';
    } elseif ($status == RETURN_NOT_APPLICABLE) {
        return '<span class="badge badge-primary">Regular</span>';
    }
}

function getReturnStatusLabel($status)
{
    if ($status == RETURN_PENDING) {
        return 'Returnable';
    } elseif ($status == RETURN_COMPLETE) {
        return 'Returned';
    } elseif ($status == RETURN_NOT_APPLICABLE) {
        return 'Regular';
    }
}


function curlrequest($url, $headers, $method, $params = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    } else {
        curl_setopt($ch, CURLOPT_POST, false);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if (!is_null($params)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    }
    $response['result'] = curl_exec($ch);
    $response['error_status'] = curl_errno($ch);
    curl_close($ch);
    return $response;
}

function getSettingInfo($name)
{
    return Setting::where("name", $name)->first()->value;
}

function getUserIdByEmployeeCode($code)
{
    return \App\Models\User::where('fingerprint_no', '=', $code)->first()->id;
}

function convertMinToHrMinSec($total_min)
{
    if ($total_min > 0) {
        $late_in_hr = round(abs($total_min / 60), 5);
        $hr = floor($late_in_hr);
        $hr_padding = strlen((string)$hr);
        if ($hr_padding == 1) {
            $hr_padding = 2;
        }
        $fraction_hr = $late_in_hr - $hr;
        $fraction_hr_in_min = $fraction_hr * 60;
        $min = floor($fraction_hr_in_min);
        $fraction_min = $fraction_hr_in_min - $min;
        $sec = floor($fraction_min * 60);
        $result = str_pad($hr, $hr_padding, "0", STR_PAD_LEFT) . ':' . str_pad($min, 2, "0", STR_PAD_LEFT) . ':' . str_pad($sec, 2, "0", STR_PAD_LEFT);
    } else {
        $result = '00:00:00';
    }
    return $result;
}

function getEmployeesByDepartmentIDs($departmentIds_in_string)
{
    $today = date("Y-m-d");
    $sql = "SELECT `id`, `name`, `fingerprint_no` FROM `users` WHERE `users`.`id` IN( SELECT `promotions`.`user_id` FROM `promotions` WHERE `promotions`.`department_id` IN ( $departmentIds_in_string) AND `promotions`.`id` IN ( SELECT MAX( `p`.`id` ) FROM `promotions` AS `p` WHERE `p`.`promoted_date` <= '$today' GROUP BY `p`.user_id )) AND `users`.`status`=1";
    return DB::select($sql);
}

function getEmployeesByDivisionIDs($office_division_id)
{
    $today = date("Y-m-d");
    $sql = "SELECT `id`, `name`, `fingerprint_no` FROM `users` WHERE `users`.`id` IN( SELECT `promotions`.`user_id` FROM `promotions` WHERE `promotions`.`office_division_id` IN ( $office_division_id) AND `promotions`.`id` IN ( SELECT MAX( `p`.`id` ) FROM `promotions` AS `p` WHERE `p`.`promoted_date` <= '$today' GROUP BY `p`.user_id )) AND `users`.`status`=1";
    return DB::select($sql);
}

function getEmployeesInformationByDepartmentIDs($departmentIds_in_string)
{
    $today = date("Y-m-d");
    $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id AND `pm`.`promoted_date` <= '$today' ) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.id IN ( SELECT promotions.user_id FROM `promotions` WHERE promotions.department_id IN ( $departmentIds_in_string ) AND promotions.id IN ( SELECT MAX( p.id ) FROM `promotions` AS p WHERE `p`.`promoted_date` <= '$today' GROUP BY p.user_id )) AND users.`status`=1";
    return DB::select($sql);
}

/**
 * @param Request $request
 */
function getBanks($search)
{
    if ($search == '') {
        $banks = \App\Models\Bank::orderby('name', 'asc')->select('id', 'name')->limit(5)->get();
    } else {
        $banks = \App\Models\Bank::orderby('name', 'asc')->select('id', 'name')
            ->where('name', 'like', '%' . $search . '%')
            ->limit(5)->get();
    }
    $response = array();
    foreach ($banks as $bank) {
        $response[] = array(
            "id" => $bank->id,
            "text" => $bank->name,
        );
    }
    echo json_encode($response);
    exit;
}

/**
 * @param Request $request
 */
function getBranches($search)
{
    if ($search == '') {
        $branches = \App\Models\Branch::orderby('name', 'asc')->select('id', 'name')->limit(5)->get();
    } else {
        $branches = \App\Models\Branch::orderby('name', 'asc')->select('id', 'name')
            ->where('name', 'like', '%' . $search . '%')
            ->limit(5)->get();
    }
    $response = array();
    foreach ($branches as $branch) {
        $response[] = array(
            "id" => $branch->id,
            "text" => $branch->name,
        );
    }
    echo json_encode($response);
    exit;
}

/**
 * @param Request $request
 */
function getInstitutesFilter($search)
{
    if ($search == '') {
        $institutes = \App\Models\Institute::orderby('name', 'asc')->select('id', 'name')->limit(30)->get();
    } else {
        $institutes = \App\Models\Institute::orderby('name', 'asc')->select('id', 'name')
            ->where('name', 'like', $search . '%')
            ->limit(30)->get();
    }
    $response = array();
    foreach ($institutes as $institute) {
        $response[] = array(
            "id" => $institute->id,
            "text" => $institute->name,
        );
    }
    echo json_encode($response);
    exit;
}


/**
 * @param Request $request
 */
function getEmployeeLists($search)
{
    if ($search == '') {
        $employees = User::orderby('name', 'asc')->select('id', 'name', 'fingerprint_no')->active()->limit(100)->get();
    } else {
        $employees = User::orderby('name', 'asc')->select('id', 'name', 'fingerprint_no')
            ->where('name', 'like', '%' . $search . '%')
            ->orWhere('fingerprint_no', 'like', '%' . $search . '%')
            ->active()
            ->limit(100)
            ->get();
    }

    $response = array();
    foreach ($employees as $employee) {
        $response[] = array(
            "id" => $employee->id,
            "text" => $employee->fingerprint_no . ' - ' . $employee->name,
        );
    }

    echo json_encode($response);
    exit;
}


/**
 * @param Request $request
 */
function getActiveUsers($search)
{
    if ($search == '') {
        $employees = User::orderby('name', 'asc')
            ->select("id", "name", "email", "fingerprint_no", "is_supervisor")
            ->whereNotIn("id", Termination::with("user", "reason", "actionTakenBy")->pluck("user_id"))
            ->where(['status' => User::STATUS_ACTIVE])
            ->limit(100)
            ->get();
    } else {
        $employees = User::orderby('name', 'asc')->select("id", "name", "email", "fingerprint_no", "is_supervisor")
            ->where('name', 'like', '%' . $search . '%')
            ->orWhere('fingerprint_no', 'like', '%' . $search . '%')
            ->whereNotIn("id", Termination::with("user", "reason", "actionTakenBy")->pluck("user_id"))
            ->where(['status' => User::STATUS_ACTIVE])
            ->limit(100)
            ->get();
    }

    $response = array();
    foreach ($employees as $employee) {
        $response[] = array(
            "id" => $employee->id,
            "text" => $employee->fingerprint_no . ' - ' . $employee->name,
        );
    }

    echo json_encode($response);
    exit;
}


/**
 * @param Request $request
 */
function getActionTakenByUsers($search)
{
    if ($search == '') {
        $employees = User::orderby('name', 'asc')
            ->select("id", "name", "email", "fingerprint_no", "is_supervisor")
            ->whereNotIn("id", Termination::with("user", "reason", "actionTakenBy")->pluck("user_id"))
            ->where(['status' => User::STATUS_ACTIVE])
            ->whereIn("is_supervisor",[User::SUPERVISOR_DEPARTMENT,User::SUPERVISOR_OFFICE_DIVISION])
            ->get();
    } else {
        $employees = User::orderby('name', 'asc')->select("id", "name", "email", "fingerprint_no", "is_supervisor")
            ->where('name', 'like', '%' . $search . '%')
            ->orWhere('fingerprint_no', 'like', '%' . $search . '%')
            ->whereNotIn("id", Termination::with("user", "reason", "actionTakenBy")->pluck("user_id"))
            ->where(['status' => User::STATUS_ACTIVE])
            ->whereIn("is_supervisor",[User::SUPERVISOR_DEPARTMENT,User::SUPERVISOR_OFFICE_DIVISION])
            ->get();
    }

    $response = array();
    foreach ($employees as $employee) {
            $response[] = array(
                "id" => $employee->id,
                "text" => $employee->fingerprint_no . ' - ' . $employee->name . ' (' . $employee->email . ')'
            );
    }
    echo json_encode($response);
    exit;
}

function getEmployeesInformationAndOldSalaryByDepartmentIDs($departmentIds_in_string,$year,$valid_date,$last_date_of_requested_year,$terminate_action_reason_ids,$max_year)
{
    $sql = "SELECT A.* FROM( SELECT users.id, users.`name`, users.email, users.fingerprint_no, employee_status.action_date, es2.action_date as last_date, prm.department_id, prm.office_division_id, prm1.pay_grade_id, prm1.salary, departments.`name` AS department_name, office_divisions.`name` AS division_name, designations.title, pay_grades.percentage_of_basic, pay_grades.based_on, user_leaves.initial_leave, user_leaves.`leaves`, ( SELECT MAX( da.date ) FROM `daily_attendances` AS da WHERE da.user_id = users.id  ) as maxadate FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id = ( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) LEFT JOIN employee_status AS es2 ON es2.user_id = users.id AND es2.id = ( SELECT MAX( es21.id ) FROM `employee_status` AS es21 WHERE es21.user_id = users.id AND es21.action_reason_id IN ($terminate_action_reason_ids) ) INNER JOIN promotions AS prm ON prm.user_id = users.id AND prm.id = ( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id ) INNER JOIN designations ON designations.id = prm.designation_id INNER JOIN promotions AS prm1 ON prm1.user_id = users.id AND prm1.id = ( SELECT MAX( pm1.id ) FROM `promotions` AS pm1 WHERE pm1.user_id = users.id AND YEAR ( pm1.promoted_date ) < $max_year ) INNER JOIN pay_grades ON pay_grades.id = prm1.pay_grade_id INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id INNER JOIN user_leaves ON user_leaves.user_id = users.id AND user_leaves.`year` = $year WHERE users.id IN ( SELECT promotions.user_id FROM `promotions` WHERE promotions.department_id IN ( $departmentIds_in_string ) AND promotions.id IN ( SELECT MAX( p.id ) FROM `promotions` AS p GROUP BY p.user_id )) ) AS A HAVING A.action_date <= '$valid_date' AND ( (A.last_date IS NULL AND maxadate>='$last_date_of_requested_year') OR A.last_date >= '$last_date_of_requested_year')";
    return DB::select($sql);
}

/**
 * @param $floatcurr
 * @param string $curr
 * @return string
 */
function currencyFormat($floatcurr, $curr = "BDT")
{
    $currencies['ARS'] = array(2,',','.');          //  Argentine Peso
    $currencies['AMD'] = array(2,'.',',');          //  Armenian Dram
    $currencies['AWG'] = array(2,'.',',');          //  Aruban Guilder
    $currencies['AUD'] = array(2,'.',' ');          //  Australian Dollar
    $currencies['BSD'] = array(2,'.',',');          //  Bahamian Dollar
    $currencies['BHD'] = array(3,'.',',');          //  Bahraini Dinar
    $currencies['BDT'] = array(2,'.',',');          //  Bangladesh, Taka
    $currencies['BZD'] = array(2,'.',',');          //  Belize Dollar
    $currencies['BMD'] = array(2,'.',',');          //  Bermudian Dollar
    $currencies['BOB'] = array(2,'.',',');          //  Bolivia, Boliviano
    $currencies['BAM'] = array(2,'.',',');          //  Bosnia and Herzegovina, Convertible Marks
    $currencies['BWP'] = array(2,'.',',');          //  Botswana, Pula
    $currencies['BRL'] = array(2,',','.');          //  Brazilian Real
    $currencies['BND'] = array(2,'.',',');          //  Brunei Dollar
    $currencies['CAD'] = array(2,'.',',');          //  Canadian Dollar
    $currencies['KYD'] = array(2,'.',',');          //  Cayman Islands Dollar
    $currencies['CLP'] = array(0,'','.');           //  Chilean Peso
    $currencies['CNY'] = array(2,'.',',');          //  China Yuan Renminbi
    $currencies['COP'] = array(2,',','.');          //  Colombian Peso
    $currencies['CRC'] = array(2,',','.');          //  Costa Rican Colon
    $currencies['HRK'] = array(2,',','.');          //  Croatian Kuna
    $currencies['CUC'] = array(2,'.',',');          //  Cuban Convertible Peso
    $currencies['CUP'] = array(2,'.',',');          //  Cuban Peso
    $currencies['CYP'] = array(2,'.',',');          //  Cyprus Pound
    $currencies['CZK'] = array(2,'.',',');          //  Czech Koruna
    $currencies['DKK'] = array(2,',','.');          //  Danish Krone
    $currencies['DOP'] = array(2,'.',',');          //  Dominican Peso
    $currencies['XCD'] = array(2,'.',',');          //  East Caribbean Dollar
    $currencies['EGP'] = array(2,'.',',');          //  Egyptian Pound
    $currencies['SVC'] = array(2,'.',',');          //  El Salvador Colon
    $currencies['ATS'] = array(2,',','.');          //  Euro
    $currencies['BEF'] = array(2,',','.');          //  Euro
    $currencies['DEM'] = array(2,',','.');          //  Euro
    $currencies['EEK'] = array(2,',','.');          //  Euro
    $currencies['ESP'] = array(2,',','.');          //  Euro
    $currencies['EUR'] = array(2,',','.');          //  Euro
    $currencies['FIM'] = array(2,',','.');          //  Euro
    $currencies['FRF'] = array(2,',','.');          //  Euro
    $currencies['GRD'] = array(2,',','.');          //  Euro
    $currencies['IEP'] = array(2,',','.');          //  Euro
    $currencies['ITL'] = array(2,',','.');          //  Euro
    $currencies['LUF'] = array(2,',','.');          //  Euro
    $currencies['NLG'] = array(2,',','.');          //  Euro
    $currencies['PTE'] = array(2,',','.');          //  Euro
    $currencies['GHC'] = array(2,'.',',');          //  Ghana, Cedi
    $currencies['GIP'] = array(2,'.',',');          //  Gibraltar Pound
    $currencies['GTQ'] = array(2,'.',',');          //  Guatemala, Quetzal
    $currencies['HNL'] = array(2,'.',',');          //  Honduras, Lempira
    $currencies['HKD'] = array(2,'.',',');          //  Hong Kong Dollar
    $currencies['HUF'] = array(0,'','.');           //  Hungary, Forint
    $currencies['ISK'] = array(0,'','.');           //  Iceland Krona
    $currencies['INR'] = array(2,'.',',');          //  Indian Rupee
    $currencies['IDR'] = array(2,',','.');          //  Indonesia, Rupiah
    $currencies['IRR'] = array(2,'.',',');          //  Iranian Rial
    $currencies['JMD'] = array(2,'.',',');          //  Jamaican Dollar
    $currencies['JPY'] = array(0,'',',');           //  Japan, Yen
    $currencies['JOD'] = array(3,'.',',');          //  Jordanian Dinar
    $currencies['KES'] = array(2,'.',',');          //  Kenyan Shilling
    $currencies['KWD'] = array(3,'.',',');          //  Kuwaiti Dinar
    $currencies['LVL'] = array(2,'.',',');          //  Latvian Lats
    $currencies['LBP'] = array(0,'',' ');           //  Lebanese Pound
    $currencies['LTL'] = array(2,',',' ');          //  Lithuanian Litas
    $currencies['MKD'] = array(2,'.',',');          //  Macedonia, Denar
    $currencies['MYR'] = array(2,'.',',');          //  Malaysian Ringgit
    $currencies['MTL'] = array(2,'.',',');          //  Maltese Lira
    $currencies['MUR'] = array(0,'',',');           //  Mauritius Rupee
    $currencies['MXN'] = array(2,'.',',');          //  Mexican Peso
    $currencies['MZM'] = array(2,',','.');          //  Mozambique Metical
    $currencies['NPR'] = array(2,'.',',');          //  Nepalese Rupee
    $currencies['ANG'] = array(2,'.',',');          //  Netherlands Antillian Guilder
    $currencies['ILS'] = array(2,'.',',');          //  New Israeli Shekel
    $currencies['TRY'] = array(2,'.',',');          //  New Turkish Lira
    $currencies['NZD'] = array(2,'.',',');          //  New Zealand Dollar
    $currencies['NOK'] = array(2,',','.');          //  Norwegian Krone
    $currencies['PKR'] = array(2,'.',',');          //  Pakistan Rupee
    $currencies['PEN'] = array(2,'.',',');          //  Peru, Nuevo Sol
    $currencies['UYU'] = array(2,',','.');          //  Peso Uruguayo
    $currencies['PHP'] = array(2,'.',',');          //  Philippine Peso
    $currencies['PLN'] = array(2,'.',' ');          //  Poland, Zloty
    $currencies['GBP'] = array(2,'.',',');          //  Pound Sterling
    $currencies['OMR'] = array(3,'.',',');          //  Rial Omani
    $currencies['RON'] = array(2,',','.');          //  Romania, New Leu
    $currencies['ROL'] = array(2,',','.');          //  Romania, Old Leu
    $currencies['RUB'] = array(2,',','.');          //  Russian Ruble
    $currencies['SAR'] = array(2,'.',',');          //  Saudi Riyal
    $currencies['SGD'] = array(2,'.',',');          //  Singapore Dollar
    $currencies['SKK'] = array(2,',',' ');          //  Slovak Koruna
    $currencies['SIT'] = array(2,',','.');          //  Slovenia, Tolar
    $currencies['ZAR'] = array(2,'.',' ');          //  South Africa, Rand
    $currencies['KRW'] = array(0,'',',');           //  South Korea, Won
    $currencies['SZL'] = array(2,'.',', ');         //  Swaziland, Lilangeni
    $currencies['SEK'] = array(2,',','.');          //  Swedish Krona
    $currencies['CHF'] = array(2,'.','\'');         //  Swiss Franc
    $currencies['TZS'] = array(2,'.',',');          //  Tanzanian Shilling
    $currencies['THB'] = array(2,'.',',');          //  Thailand, Baht
    $currencies['TOP'] = array(2,'.',',');          //  Tonga, Paanga
    $currencies['AED'] = array(2,'.',',');          //  UAE Dirham
    $currencies['UAH'] = array(2,',',' ');          //  Ukraine, Hryvnia
    $currencies['USD'] = array(2,'.',',');          //  US Dollar
    $currencies['VUV'] = array(0,'',',');           //  Vanuatu, Vatu
    $currencies['VEF'] = array(2,',','.');          //  Venezuela Bolivares Fuertes
    $currencies['VEB'] = array(2,',','.');          //  Venezuela, Bolivar
    $currencies['VND'] = array(0,'','.');           //  Viet Nam, Dong
    $currencies['ZWD'] = array(2,'.',' ');          //  Zimbabwe Dollar

    if ($curr == "BDT") {
        return formatBDT($floatcurr);
    } else {
        return number_format($floatcurr,$currencies[$curr][0],$currencies[$curr][1],$currencies[$curr][2]);
    }
}

/**
 * @param $input
 * @return string
 */
function formatBDT($input) {
    //CUSTOM FUNCTION TO GENERATE ##,##,###.##
    $dec = "";
    $pos = strpos($input, ".");
    if ($pos === false){
        //no decimals
    } else {
        //decimals
        $dec = substr(round(substr($input,$pos),2),1);
        $input = substr($input,0,$pos);
    }
    $num = substr($input,-3); //get the last 3 digits
    $input = substr($input,0, -3); //omit the last 3 digits already stored in $num
    while(strlen($input) > 0) //loop the process - further get digits 2 by 2
    {
        $num = substr($input,-2).",".$num;
        $input = substr($input,0,-2);
    }
    return $num . $dec;
}

/**
 * @param $number
 * @return string
 */
function getBangladeshCurrency($number)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'One', 2 => 'Two',
        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'eleven', 12 => 'twelve',
        13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
        16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
        19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
        40 => 'forty', 50 => 'fifty', 60 => 'sixty',
        70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
    $digits = array('', 'hundred','thousand','lakh', 'crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
        } else $str[] = null;
    }
    $Taka = implode('', array_reverse($str));
    $poysa = ($decimal) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' poisa' : '';
    $inWords = ($Taka ? $Taka . 'taka ' : '') . $poysa . ' Only' ;
    return ucwords($inWords);
}


/**
 * @param $departmentId
 * @param $month
 * @param $year
 * @return array
 * @throws Exception
 */
function getTotalWorkingDaysOfDepartmentByMonth($departmentId, $month, $year)
{
    $totalHolidays = [
        "weekly"        => 0,
        "public"        => 0,
        "calendarDays"  => 0,
        "workingDays"   => null,
    ];

    $firstDateOfMonth = $year . "-" . $month . "-01";
    $lastDayOfMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $lastDateOfMonth = $year . "-" . $month . "-" . $lastDayOfMonth;

    $weeklyHolidays = \App\Models\WeeklyHoliday::where("department_id", $departmentId)->orderByDesc("id")->first();
    $weeklyHolidays = json_decode($weeklyHolidays->days);

    $dateRange = getDatesFromRange($firstDateOfMonth, $lastDateOfMonth);

    foreach($dateRange as $date) {
        $day = strtolower(date('D', strtotime($date)));
        $isWeeklyHoliday = in_array($day, $weeklyHolidays);
        if($isWeeklyHoliday) $totalHolidays["weekly"]++;
    }

    $publicHolidays = \App\Models\PublicHoliday::where("from_date", ">=", $firstDateOfMonth)
        ->where("to_date", "<=", $lastDateOfMonth)
        ->get();

    foreach ($publicHolidays as $publicHoliday) {
        $dateRange = getDatesFromRange($publicHoliday->from_date, $publicHoliday->to_date);

        foreach ($dateRange as $value) {
            $day = strtolower(date('D', strtotime($value)));
            $isWeeklyHoliday = in_array($day, $weeklyHolidays);

            if(!$isWeeklyHoliday) $totalHolidays["public"]++;
        }
    }

    $totalHolidays["calendarDays"] = $lastDayOfMonth;
    $totalHolidays["workingDays"] = $lastDayOfMonth - ($totalHolidays["weekly"] + $totalHolidays["public"]);

    return $totalHolidays;
}

/**
 * Generate an array of string dates between 2 dates
 *
 * @param string $start Start date
 * @param string $end End date
 * @param string $format Output format (Default: Y-m-d)
 *
 * @return array
 * @throws Exception
 */
function getDatesFromRange($start, $end, $format = 'Y-m-d') {
    $array = array();
    $interval = new DateInterval('P1D');

    $realEnd = new DateTime($end);
    $realEnd->add($interval);

    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

    foreach($period as $date) $array[] = $date->format($format);

    return $array;
}

/**
 * @param int $monthNumber
 * @param string $format
 * @return false|string
 */
function getMonthNameFromMonthNumber($monthNumber = 1, $format = 'F') {
    return date($format, mktime(0, 0, 0, $monthNumber, 10));
}
