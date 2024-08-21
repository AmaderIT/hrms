<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function getEmployeeInfoByCodeOrEmail(Request $request){
        try {
            $sql = "SELECT
                users.`name`,
                users.`email`,
                users.`phone` as office_phone,
                promotions.office_division_id,
                promotions.department_id,
                promotions.designation_id,
                office_divisions.`name` as division_name,
                departments.`name` as department_name,
                designations.title as designation_name,
                profiles.personal_phone
            FROM
                users
                INNER JOIN promotions ON promotions.id = ( SELECT MAX( promotions.id ) FROM promotions WHERE promotions.user_id = users.id )
                LEFT JOIN profiles ON profiles.user_id = users.id
                INNER JOIN office_divisions ON office_divisions.id=promotions.office_division_id
                INNER JOIN departments ON departments.id = promotions.department_id
                INNER JOIN designations ON designations.id = promotions.designation_id
            WHERE
                users.`$request->type` = '$request->value'";
            $employee_info = DB::select($sql);
            if($employee_info){
                return response()->json(['success'=>true,'data'=>$employee_info[0],'message'=>'Employee information fetched successfully!']);
            }
            return response()->json(['success'=>false,'data'=>null,'message'=>'No data found!']);
        }catch (\Exception $exception){
            Log::info($exception->getMessage());
            return response()->json(['success'=>false,'data'=>null,'message'=>'Something went wrong!']);
        }
    }

    public function getEmployeeInfoById(Request $request)
    {
        try {
            if (empty($request->employee_id)) {
                throw new \Exception('Employee ID can not be empty!');
            }

            if (empty($request->token)) {
                throw new \Exception('Token can not be empty!');
            }

            /** Generate Token **/
            $inputToken = $request->token;
            $security_salt = "#@Evrydy%=(SMA##*";
            # $providedToken = '0bb4ac5e527d3513cecc7fdc5cbe29f95dab03fcbced84cb2ad2582ae1dc69ef';

            /*$token = uniqid();
            $token_with_salt = hash('sha256', $token . $security_salt);*/

            $string = '3987cd569b51b6e963cdac2030078a5661a60365aabb38f2644d8b6ddfe950b2';
            $generatedToken = hash('sha256', $string . $security_salt);

            if ($inputToken != $generatedToken) {
                throw new \Exception('Invalid Token!!');
            }

            $filePath = null;
            $user = User::select('*')->where(['fingerprint_no' => $request->employee_id])->first();

            if (!$user) {
                throw new \Exception('Employee Not Found!!');
            }

            if ($user->status != 1) {
                throw new \Exception('Employee Not Active!!');
            }

            $path = !empty($user->fingerprint_no) ? "photo/" . $user->fingerprint_no . ".jpg" : null;

            $filePath = file_exists($path) ? asset($path) : asset('assets/media/svg/avatars/001-boy.svg');

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Employee information fetched successfully!',
                    'data' => [
                        'name' => $user->name,
                        'official_email' => $user->email,
                        'personal_email' => $user->profile->personal_email ?? null,
                        'official_phone' => $user->phone,
                        'personal_phone' => $user->profile->personal_phone ?? null,
                        'profile_picture' => $filePath
                    ],
                ]
            );
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
            return response()->json(['status' => false, 'message' => $exception->getMessage(), 'data' => null]);
        }
    }
}
