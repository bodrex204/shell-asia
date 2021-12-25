<?php
include_once('library/sms-activate/SMSActivate.php');
include_once('library/beliotp/BeliOtp.php');
require_once 'Request.php';

$config = require __DIR__ . '/Config.php';
$request = new Request();
$refferal_code = $config['shell']['reff_code'];
$beli_otp = new BeliOtp(
    $config['beli_otp']['api_key'],
    $config['beli_otp']['api_id'],
    $config['beli_otp']['secret_key']
);

echo 'Shell Asia Account Creator' . PHP_EOL . PHP_EOL;
$total_account = readline("How many account(s): ");
for ($i=0; $i < intval($total_account); $i++) { 
    $get_profile = $beli_otp->profile();
    if ($get_profile['status']) {
        $balance = $get_profile['data']['balance'];
        echo '[+] Balance: ' . $balance . ' IDR' . PHP_EOL;

        if ($balance >= 1150) {
            // ID: 119 = Semua Aplikasi 
            $service_id = 109;
            $buy_number = $beli_otp->order($service_id);
            if ($buy_number['status']) {
                $id = $buy_number['data']['id'];
                $set_status = $beli_otp->setStatus($id, 1);
                if ($set_status['status']) {
                    $phone_number = $set_status['data']['phone'];
                    echo '[+] Phone Number: ' . $phone_number . PHP_EOL;

                    $generate_token = $request->generateToken($phone_number);
                    if ($generate_token['success']) {
                        $generate_otp = $request->generateOtp($phone_number);
                        if ($generate_otp['success']) {

                            echo '[+] OTP Sent, ';
                            $otp_code = '';
                            $status_otp = false;

                            echo 'Waiting sms code.';
                            $wait_time = 1;
                            while ($otp_code == '') {
                                $get_sms = $beli_otp->checkStatus($id);
                                if ($get_sms['status']) {
                                    if ($get_sms['data']['status'] == 'Success') {
                                        $otp_code = $request->get_numerics(urldecode($get_sms['data']['sms']));
                                        echo 'OTP: ' . $otp_code . PHP_EOL;
                                        $status_otp = true;
                                        break;
                                    }else{
                                        echo '.';
                                    }
                                }
                                sleep(10);
                                $wait_time++;
                                if ($wait_time >= 60) {
                                    echo PHP_EOL. '[-] Canceled' . PHP_EOL;
                                    // Cancel sms
                                    $beli_otp->setStatus($id, 4);
                                    break;
                                }
                            }

                            $validate_otp = $request->validateOtp($phone_number, $otp_code);
                            if ($validate_otp['success']) {
                                $register = $request->register($phone_number, $refferal_code);
                                // print_r($register);
                                if ($register['success']) {
                                    $beli_otp->setStatus($id, 3);
                                    echo '[+] Register Success!';
                                }else{
                                    echo '[-] Register Failed!';
                                }
                                echo PHP_EOL;

                            }else{
                                $beli_otp->setStatus($id, 3);
                                echo 'Validate OTP Failed!' . PHP_EOL;
                            }
                        }else{
                            echo '[-] OTP Failed.' . PHP_EOL;
                            $beli_otp->setStatus($id, 4);
                        }
                    }
                }
            }else{
                echo '[-] Error: ' . $buy_number['data'] . PHP_EOL;
            }
        }else{
            echo '[-] Not Enough Balance. ' . PHP_EOL;
        }
    }
    echo PHP_EOL;
}