<?php

namespace App\Http\Controllers;

use Anand\LaravelPaytmWallet\Facades\PaytmWallet;
use App\Subscription;
use App\User;
use App\WorkspacePlan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaytmController extends Controller
{
    public function getRequest()
    {
        $order_id = request('ORDER_ID');
        $txn_amount = request('TXN_AMOUNT');
        $plan_id = request('PLAN_ID');
        $subs_id = request('SUBS_ID');

        Cache::put($order_id, [$plan_id, $subs_id]);

        return view('paytm.index', ['order_id' => $order_id, 'txn_amount' => $txn_amount]);
    }

    public function postRequest()
    {
        $user = User::where('email', 'chhedadhaval120386@gmail.com')->first();

        $payment = PaytmWallet::with('receive');

        $app = env('APP_URL');
        $callback_url = $app . "/api/paytm/response";

        $payment->prepare([
            'order' => request('ORDER_ID'),
            'user' => $user->id,
            'mobile_number' => $user->phone,
            'email' => $user->email, // your user email address
            'amount' => request('TXN_AMOUNT'), // amount will be paid in INR.
            'callback_url' => $callback_url // callback URL
        ]);
        return $payment->receive();  // initiate a new payment
    }

    public function postResponse()
    {
        $transaction = PaytmWallet::with('receive');

        $response = $transaction->response();

        $order_id = $transaction->getOrderId(); // return a order id

        $transaction->getTransactionId(); // return a transaction id

        if ($response['RESPCODE'] == '01') {
            [$plan_id, $subs_id] = Cache::get($order_id);

            Cache::forget($order_id);

            $plan = WorkspacePlan::find($plan_id);
            $subscription = Subscription::find($subs_id);
            if (!$plan || !$subscription) return view('paytm.response', ['status' => false, 'result' => $response]);

            $payment = $subscription->payment;
            if ($payment) {
                $payment->status = "success";
                $payment->save();
            }
            $amount = ($plan->cost / 100) * 1.18;

            if ($amount == $response['TXNAMOUNT']) {
                return view('paytm.response', ['status' => true, 'result' => $response]);
            }
        }
        return view('paytm.response', ['status' => false, 'result' => $response]);
    }
}
