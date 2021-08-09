<?php

namespace App\Contracts;

interface PaymentContract
{

    public function initiatePayment($data);

    public function searchPayment($data);

    public function updatePayment($request);

    public function saveToDb($data, $id);

}