<?php
require_once 'vendor/autoload.php';

class Request{
    private $config;
    private $url_auth;
    private $url_gateway;
    private $headers;
    private $headers_gateway;
    private $body;
    private $body_gateway;
    private $device_id;
    private $faker;
    private $token;
    private $key;
    private $session_id;

    public function __construct() {
        $this->config = require __DIR__ . '/Config.php';

        $this->url_auth = $this->config['shell']['auth_url'];
        $this->url_gateway = $this->config['shell']['gateway_url'];
        $this->faker = Faker\Factory::create();
    }

    public function generateToken($phone_number)
    {
        $this->device_id = $this->faker->uuid;
        $path = 'auth/v1/token/generate';
        $body_array = [
            'brand' => 'SHELLINDONESIALIVE',
            'mobile' => $phone_number,
            'deviceId' => $this->device_id
        ];
        
        $this->setHeaderAuth();
        $this->setBodyAuth(json_encode($body_array));
        $exec = $this->execAuth($path);
        if($this->isJson($exec)){
            $data = json_decode($exec);
            if ($data->status->success == 1) {
                
                $this->session_id = $data->user->sessionId;
                $this->token = $data->auth->token;

                $response_array = [
                    'token' => $this->token,
                    'session_id' => $this->session_id
                ];

                return $this->makeResponse($response_array);
            }else{

                $response_array = '';
                return $this->makeResponse($response_array, false);
            }
        }else{
            return $exec;
        }
    }

    public function generateOtp($phone_number)
    {
        $path = 'auth/v1/otp/generate';
        $body_array = [
            'deviceId' => $this->device_id,
            'sessionId' => $this->session_id,
            'brand' => 'SHELLINDONESIALIVE',
            'mobile' => $phone_number,
        ];
        
        $this->setHeaderAuth();
        $this->setBodyAuth(json_encode($body_array));
        $exec = $this->execAuth($path);
        if($this->isJson($exec)){
            $data = json_decode($exec);
            if ($data->status->success == 1) {
                $response_array = [
                    'message' => 'success',
                ];
                return $this->makeResponse($response_array);
            }else{
                $response_array = '';
                return $this->makeResponse($response_array, false);
            }
        }else{
            return $exec;
        }
    }

    public function validateOtp($phone_number, $otp_code)
    {
        $path = 'auth/v1/otp/validate';
        $body_array = [
            'deviceId' => $this->device_id,
            'otp' => $otp_code,
            'brand' => 'SHELLINDONESIALIVE',
            'mobile' => $phone_number,
            'sessionId' => $this->session_id
        ];

        
        $this->setHeaderAuth();
        $this->setBodyAuth(json_encode($body_array));
        $exec = $this->execAuth($path);
        if($this->isJson($exec)){
            $data = json_decode($exec);
            if ($data->status->success == 1) {
                
                $this->token = $data->auth->token;
                $this->key = $data->auth->key;

                $response_array = [
                    'message' => 'success',
                ];
                return $this->makeResponse($response_array);
            }else{
                $response_array = '';
                return $this->makeResponse($response_array, false);
            }
        }else{
            return $exec;
        }
    }

    public function register($phone_number, $refferal_code)
    {
        $path = 'mobile/v2/api/v2/customers';
        $data_array = array (
            'statusLabel' => 'Active',
            'referralCode' => $refferal_code,
            'loyaltyInfo' => 
            array (
              'loyaltyType' => 'loyalty',
            ),
            'statusLabelReason' => 'App Registration',
            'profiles' => 
            array (
              0 => 
              array (
                'firstName' => $this->faker->firstName(),
                'lastName' => $this->faker->lastName,
                'identifiers' => 
                array (
                  0 => 
                  array (
                    'type' => 'mobile',
                    'value' => $phone_number,
                  ),
                  1 => 
                  array (
                    'value' => $this->faker->freeEmail,
                    'type' => 'email',
                  ),
                ),
                'fields' => 
                array (
                  'goplus_tnc' => '1',
                  'onboarding' => 'pending',
                  'app_privacy_policy' => '1',
                ),
              ),
            ),
            'extendedFields' => 
            array (
              'dob' => '2000-02-02',
              'acquisition_channel' => 'mobileApp',
              'verification_status' => false,
            ),
        );
        $this->setHeaderGateway($phone_number);
        $this->setBodyGateway(json_encode($data_array));
        $exec = $this->execGateway($path);
        if($this->isJson($exec)){
            $data = json_decode($exec);
            if (isset($data->createdId)) {
                $response_array = [
                    'message' => 'success',
                ];
                return $this->makeResponse($response_array);
            }else{
                return $this->makeResponse($data, false);
            }
        }else{
            return $exec;
        }
    }
    
    private function setHeaderAuth()
    {
        $headers = array();
        $headers[] = 'Host: apac2-auth-api.capillarytech.com';
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/json';
        // $headers[] = 'Accept-Encoding: gzip, deflate';
        $headers[] = 'User-Agent: ShellGoPlus%20Production/17 CFNetwork/1312 Darwin/21.0.0';
        $headers[] = 'Accept-Language: en-us';
        $headers[] = 'Connection: close';
        $this->headers = $headers;
    }
    
    private function setHeaderGateway($phone_number)
    {
        $headers = array();
        $headers[] = 'Host: apac2-api-gateway.capillarytech.com';
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Accept: application/json';
        $headers[] = 'Cap_device_id: ' . $this->device_id;
        $headers[] = 'Cap_brand: SHELLINDONESIALIVE';
        $headers[] = 'Cap_mobile: ' . $phone_number;
        $headers[] = 'Accept-Language: en-us';
        $headers[] = 'Cap_authorization: ' . $this->token;
        // $headers[] = 'Accept-Encoding: gzip, deflate';
        $headers[] = 'User-Agent: ShellGoPlus%20Production/17 CFNetwork/1312 Darwin/21.0.0';
        $headers[] = 'Connection: close';
        $this->headers_gateway = $headers;
    }

    private function setBodyAuth($body_json)
    {
        $this->body = $body_json;
    }

    private function setBodyGateway($body_json)
    {
        $this->body_gateway = $body_json;
    }

    private function execAuth($path)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url_auth . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Check Body
        if ($this->body != null || $this->body != '') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
        }else{
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }

        // Check Headers
        if ($this->headers != null || $this->headers != '') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch) . PHP_EOL;
        }
        curl_close($ch);
        return $result;
    }

    private function execGateway($path)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url_gateway . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Check Body
        if ($this->body_gateway != null || $this->body_gateway != '') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body_gateway);
        }else{
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }

        // Check Headers
        if ($this->headers_gateway != null || $this->headers_gateway != '') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers_gateway);
        }

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch) . PHP_EOL;
        }
        curl_close($ch);
        return $result;
    }

    private function isJson($string,$return_data = false) {
        $data = json_decode($string);
          return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
    }

    private function makeResponse($data_array, $isSuccess = true)
    {
        return [
            'success' => $isSuccess,
            'data' => $data_array
        ];
    }

    public function get_numerics ($str) {
        $str1 = str_replace("-", "", $str);
        preg_match_all('/\d+/', $str1, $matches);
        return $matches[0][0];
    }
}