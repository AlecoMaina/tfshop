<?php

namespace App\Services;

use App\Models\Payment;
use App\Contracts\PaymentContract;


class PaymentService implements PaymentContract
{

    public function initiatePayment($data)
    {
        $key ='demoCHANGED';//use "demoCHANGED" for testing where vid is set to "demo"

        $live = 0;//live or not
        $oid = $data->ordernumber;//order id
        $inv = $data->invoicenumber;//invoice number
        $amount = $data->total;//amount
        $tel = $data->mobile;//telephone
        $eml = $data->email;//email
        $vid =  'demo';//vendor id set by ipay
        $curr = 'KES';//currency
        $p1 = 'Order for Buying Goods';//
        $cst = 0;//customer email notification
        $cbk = route('thankyou');//callback URL

        $datastring = $live.$oid.$inv.$amount.$tel.$eml.$vid.$curr.$p1.$cst.$cbk;
        /*********************************************************************************************************/

        $generated_hash = hash_hmac('sha256',$datastring , $key);

        // Make Post Fields Array
        $body = [
            'live' => $live,
            'key' => $key,
            'oid' => $oid,
            'inv' => $inv,
            'amount' => $amount,
            'tel' => $tel,
            'eml' => $eml,
            'vid' => $vid,
            'curr' => $curr,
            'p1' => $p1,
            'cst' => $cst,
            'cbk' => $cbk,
            'crl' => 0,
            'hash' => $generated_hash,
            'autopay' => 1
        ];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL,"https://apis.ipayafrica.com/payments/v2/transact");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            return null;
        } else {
            //save to database and make another call back request
            $resultResponse = $this->saveToDb(json_decode($response), $data->id, $tel);
            
            if($resultResponse){

                //dd($resultResponse[0]['text']);
                
                $dataResponse = json_decode($response);

                $cleanedData = [
                    'mpesa' => $dataResponse->data->payment_channels[0],
                    'airtel' => $dataResponse->data->payment_channels[1],
                    'equitel' => $dataResponse->data->payment_channels[2],
                    'text' => ($resultResponse[0]['text'] == "") ? "NO STK" : $resultResponse[0]['text']
                ];

            }
        }

        return $cleanedData;
    }

    public function saveToDb($data, $id, $number)
    {
        // insert to database and await payment confirmation
        //dd($data['header_status']);

        $order = Payment::create([
            'order_id' => $id,
            'oid' => $data->data->oid,
            'sid' => $data->data->sid,
            'account' => $data->data->account,
            'transaction_amount' => $data->data->amount,
        ]);

        //dd('payment_created');

        $key = env('IPAY_VENDOR_KEY', 'demoCHANGED');
        $datastring = $data->data->sid.env('IPAY_VENDOR_ID', 'demo');
        $generated_hash = hash_hmac('sha256',$datastring , $key);

        $curlData = [
            'hash' => $generated_hash,
            'sid' => $data->data->sid,
            'vid' => env('IPAY_VENDOR_ID', 'demo'),
        ];

        //regular expression to check the number and then make the stk push request.
        //and also show message to the user of the stk push or the ussd for airtell


            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,"https://apis.ipayafrica.com/payments/v2/transact/mobilemoney");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

            //dd(curl_getinfo($ch, CURLINFO_HTTP_CODE));


            // receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec ($ch);

            curl_close ($ch);

            $serverResponse = json_decode($server_output);

            $message = array();

            // further processing ....
            if ($serverResponse->status == "bdi6p2yy76etrs") { 
                //$received_data = json_decode($server_output);
                //dd($received_data); 
                //make the other request for the stk and the airtell ussd
                $key = env('IPAY_VENDOR_KEY', 'demoCHANGED');
                $datastringsec = $number.env('IPAY_VENDOR_ID', 'demo').$data->data->sid;
                $generated_hash_sec = hash_hmac('sha256',$datastringsec , $key);

                $responseData = [
                    'hash' => $generated_hash_sec,
                    'sid' => $data->data->sid,
                    'vid' => env('IPAY_VENDOR_ID', 'demo'),
                    'phone' => $number,
                ];

                if(preg_match("/(\+?254|0|^){1}[-. ]?([7]{1}([0-2]{1}[0-9]{1}|[4]{1}([0-3]{1}|[5-6]{1})|[5]{1}[7-9]{1}|[6]{1}[8-9]{1}|[9]{1}[0-9]{1})|[1]{2}[0-5]{1})[0-9]{6}\z/", $number)){ //safaricom

                    $response = $this->mpesaPay($responseData);

                    if($response){

                        array_push($message, ['text' => "An Stk request was sent to your Safaricom phone"]);

                    }

                } else if(preg_match("/(\+?254|0|^){1}[-. ]?([7]{1}([3]{1}[0-9]{1}|[5]{1}[0-6]{1}|[8]{1}[0-9]{1}|[6]{1}[2]{1})|[1]{1}[0]{1}[0-2]{1})[0-9]{6}\z/", $number)) { //airtel

                    $response = $this->airtelPay($responseData);

                    if($response){

                        array_push($message, ['text' => "An stk request was sent to your Airtel phone"]);

                    }

                }

                array_push($message, ['text' => ""]);

            } else {  

                //dd('pay');

            }

            //update ui accordingly
            return $message;

    }

    public function searchPayment($data)
    {
        //dd($data->oid);
        $vid = env('IPAY_VENDOR_ID', 'demo');
        $key = env('IPAY_VENDOR_KEY', 'demoCHANGED');
        $datastring = $data->oid.$vid;
        $generated_hash = hash_hmac('sha256',$datastring , $key);

        $curlData = [
            'hash' => $generated_hash,
            'oid' => $data->oid,
            'vid' => $vid,
        ];


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://apis.ipayafrica.com/payments/v2/transaction/search");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $server_output = curl_exec ($ch);

        curl_close ($ch);

        // further processing ....
        if ($server_output == "OK") {
            
            //dd('inform user of the success payment and update database accordingly'); 
            $updateData = $this->updatePayment(json_decode($server_output));

        } else {  
            
            //dd(json_decode($server_output), 'inform user no payment has been made yet');
            return false;

        }

        return $updateData;
    }

    public function updatePayment($request)
    {

        $account = Payment::where('oid', $request->oid)->first();
        $account->update([
            'transaction_code' => $request['transaction_code'],
            'session_id' => $request['session_id,'],
            'lastname' => $request['lastname'],
            'firstname' => $request['firstname'],
            'paid_at' => $request['paid_at'],
            'payment_mode' => $request['payment_mode'],
            'payment_status' => 1,
        ]);

        return $account;

    }

    public function mpesaPay($data)
    {
        //dd($data);

        $ch2 = curl_init();

        curl_setopt($ch2, CURLOPT_URL,"https://apis.ipayafrica.com/payments/v2/transact/push/mpesa");
        curl_setopt($ch2, CURLOPT_POST, 1);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        //dd(curl_getinfo($ch, CURLINFO_HTTP_CODE));


        // receive server response ...
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch2);

        curl_close ($ch2);

        $serverResponse = json_decode($server_output);

        if($serverResponse->header_status == 200){

            return true;

        }

        return false;
    }

    public function airtelPay($data)
    {

        //dd($data);

        $ch2 = curl_init();

        curl_setopt($ch2, CURLOPT_URL,"https://apis.ipayafrica.com/payments/v2/transact/push/airtel");
        curl_setopt($ch2, CURLOPT_POST, 1);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        //dd(curl_getinfo($ch, CURLINFO_HTTP_CODE));


        // receive server response ...
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch2);

        curl_close ($ch2);

        $serverResponse = json_decode($server_output);

        dd($serverResponse);

        if($serverResponse->header_status == 200){

            return true;

        }

        return false;
    }

}