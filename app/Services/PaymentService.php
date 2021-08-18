<?php

namespace App\Services;

use App\Models\Payment;
use App\Contracts\PaymentContract;


class PaymentService implements PaymentContract
{

    public function initiatePayment($data)
    {
        //check hash
        $key = env('IPAY_VENDOR_KEY', 'demoCHANGED');//use "demoCHANGED" for testing where vid is set to "demo"

        $live = 0;//live or not
        $oid = $data->ordernumber;//order id
        $inv = $data->invoicenumber;//invoice number
        $amount = $data->total;//amount
        $tel = $data->mobile;//telephone
        $eml = $data->email;//email
        $vid = env('IPAY_VENDOR_ID', 'demo');//vendor id set by ipay
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
            //dd($data->id);
            $resultResponse = $this->saveToDb(json_decode($response), $data->id);
            if($resultResponse){
                $dataResponse = json_decode($response);
            }
            //dd($response,$body);
        }

        return $dataResponse;
    }

    public function saveToDb($data, $id)
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

        //dd(json_decode($server_output));

        // further processing ....
        if ($server_output == "OK") { 
            //$received_data = json_decode($server_output);
            //dd($received_data); 
        } else {  
            //dd('pay');
        }

        //update ui accordingly
        return json_decode($server_output);

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

}