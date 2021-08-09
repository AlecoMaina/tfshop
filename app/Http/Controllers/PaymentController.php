<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\PaymentService;

class PaymentController extends Controller
{

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        //
    }

    public function paymentConfirmation(Request $request)
    {
        //check from database where the order of the payment
        //and make necessary changes to the user
    }

    public function testPayment()
    {
        $order = new Order();
        $order-> user_id = 4;
        $order-> ordernumber = strtoupper(uniqid('ORD-'));
        $order-> invoicenumber = strtoupper(uniqid('INV-'));
        $order-> subtotal = '20000.00';
        $order-> discount = '200.00';
        $order-> tax = '50.00';
        $order-> total = '19750.00';;
        $order-> firstname  = 'Anthony';
        $order-> lastname = 'Toroyteach';
        $order-> email = 'tonny35toro@gmail.com';
        $order-> mobile = '254710516288';
        $order-> line1 = 'this line';
        $order-> line2 = 'this line 2';
        $order-> city = 'Kitale';
        $order-> province = 'RiftValley';
        $order-> country = 'Kenya';
        $order-> zipcode = '123-here';
        $order-> status = 'ordered';
        $order-> is_shipping_different = 0; 
        $order-> save();

        //dd($order->all());

        //make the payment request
        $this->paymentService->initiatePayment($order);
    }

    public function searchPayment()
    {

        $key = 'demoCHANGED';
        $datastring = 'ORD60FEDB4E266F8'.'demo';
        $generated_hash = hash_hmac('sha256',$datastring , $key);

        $curlData = [
            'hash' => $generated_hash,
            'oid' => 'ORD60FEDB4E266F8',
            'vid' => 'demo',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://apis.ipayafrica.com/payments/v2/transaction/search");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);

        curl_close ($ch);

        dd($server_output);

        // further processing ....
        if ($server_output == "OK") { dd($server_output); } else {  }
    }
}
