<?php

class BeliOtp{
    private $API_URL = 'https://beliotp.co.id/api/';
    private $api_key;
    private $api_id;
    private $secret_key;

    public function __construct($api_key, $api_id, $secret_key) {
        $this->api_key = $api_key;
        $this->api_id = $api_id;
        $this->secret_key = $secret_key;
    }


    private function request($url, Array $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $array_body = array();
        $array_body['api_id'] = $this->api_id;
        $array_body['api_key'] = $this->api_key;
        $array_body['secret_key'] = $this->secret_key;

        foreach ($data as $key => $value) {
            $array_body[$key] = $value;
        }

        // Check Body
        curl_setopt($ch, CURLOPT_POST, $array_body);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $array_body);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private function isJson($string,$return_data = false) {
        $data = json_decode($string);
          return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
     }

    public function profile()
    {
        $url = $this->API_URL . 'profile';
        $req = $this->request($url);
        if ($this->isJson($req)) {
            $req_decode = json_decode($req);
            if ($req_decode->response) {
                return [
                    'status' => true,
                    'data' => [
                        'balance' => $req_decode->data->balance,
                        'today_orders' => $req_decode->data->today_orders,
                    ]
                ];
            }else{
                return [
                    'status' => false,
                    'data' => $req_decode->data->msg
                ];
            }
        }else{
            return [
                'status' => false,
                'data' => $req
            ];
        }
    }

    public function order($service_id)
    {
        $url = $this->API_URL . 'order';
        $body['service'] = $service_id;
        $req = $this->request($url, $body);
        if ($this->isJson($req)) {
            $req_decode = json_decode($req);
            if ($req_decode->response) {
                return [
                    'status' => true,
                    'data' => [
                        'id' => $req_decode->data->id,
                        'application' => $req_decode->data->application,
                        'phone' => $req_decode->data->phone,
                    ]
                ];
            }else{
                return [
                    'status' => false,
                    'data' => $req_decode->data->msg
                ];
            }
        }else{
            return [
                'status' => false,
                'data' => $req
            ];
        }
    }

    public function orderStatus($order_id)
    {
        $url = $this->API_URL . 'status';
        $body['id'] = $order_id;
        $req = $this->request($url, $body);
        if ($this->isJson($req)) {
            $req_decode = json_decode($req);
            if ($req_decode->response) {
                return [
                    'status' => true,
                    'data' => [
                        'id' => $req_decode->data->id,
                        'status' => $req_decode->data->status,
                        'phone' => $req_decode->data->phone,
                        'otp' => $req_decode->data->otp,
                        'sms' => $req_decode->data->sms,
                    ]
                ];
            }else{
                return [
                    'status' => false,
                    'data' => $req_decode->data->msg
                ];
            }
        }else{
            return [
                'status' => false,
                'data' => $req
            ];
        }
    }

    // Untuk melakukan set status 1 (start) hanya diizinkan jika status pesanan Pending.
    // Untuk melakukan set status 2 (retry) hanya diizinkan jika status pesanan Success.
    // Untuk melakukan set status 3 (done) hanya diizinkan jika status pesanan Success atau Retry.
    // Untuk melakukan set status 4 (cancel) hanya diizinkan jika status pesanan Pending atau Processing.
    public function setStatus($order_id, $status)
    {
        $url = $this->API_URL . 'set_status';
        $body['id'] = $order_id;
        $body['status'] = $status;
        $req = $this->request($url, $body);
        if ($this->isJson($req)) {
            $req_decode = json_decode($req);
            if ($req_decode->response) {
                return [
                    'status' => true,
                    'data' => [
                        'id' => $req_decode->data->id,
                        'status' => $req_decode->data->status,
                        'phone' => $req_decode->data->phone,
                        'otp' => $req_decode->data->otp,
                        'sms' => $req_decode->data->sms,
                    ]
                ];
            }else{
                return [
                    'status' => false,
                    'data' => $req_decode->data->msg
                ];
            }
        }else{
            return [
                'status' => false,
                'data' => $req
            ];
        }
    }

    public function checkStatus($order_id)
    {
        $url = $this->API_URL . 'status';
        $body['id'] = $order_id;
        $req = $this->request($url, $body);
        if ($this->isJson($req)) {
            $req_decode = json_decode($req);
            if ($req_decode->response) {
                return [
                    'status' => true,
                    'data' => [
                        'id' => $req_decode->data->id,
                        'status' => $req_decode->data->status,
                        'phone' => $req_decode->data->phone,
                        'otp' => $req_decode->data->otp,
                        'sms' => $req_decode->data->sms,
                        'status' => $req_decode->data->status,
                    ]
                ];
            }else{
                return [
                    'status' => false,
                    'data' => $req_decode->data->msg
                ];
            }
        }else{
            return [
                'status' => false,
                'data' => $req
            ];
        }
    }
}