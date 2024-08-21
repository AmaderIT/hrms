<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Device extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var string[]
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Bank has been {$eventName}";
    }

    protected function getAttendanceServerToken()
    {
        try {
            $http_response_header = array(
                "Content-Type" => "application/json"
            );
            $url = env("ZKTECO_SERVER_PORT") . "/jwt-api-token-auth/";
            $payLoad = array(
                "username" => env("ZKTECO_BIOTIME_USERNAME"),
                "password" => env("ZKTECO_BIOTIME_PASSWORD")
            );

            $response = Http::withHeaders($http_response_header)->post($url, $payLoad);
            $jwtToken = $response->json()["token"] ?? null;
        } catch (Exception $exception) {
            $jwtToken = null;
        }

        return $jwtToken;
    }

    public function getAttendanceDeviceListByApi()
    {
        try {
            # Get JWT Token
            $jwtToken = self::getAttendanceServerToken();
            if ($jwtToken) {

                $http_response_header = array(
                    "Content-Type" => "application/json",
                    "Authorization" => "JWT " . $jwtToken
                );

                $url = env("ZKTECO_SERVER_PORT") . "/iclock/api/terminals/";
                $response = Http::withHeaders($http_response_header)->get($url,[]);
                return $response->json();
            }
            return null;

        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 500);
        }

    }
}
