<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\CouponCode;
use App\Payment;
use App\User;
use App\UserToken;
use App\WorkspacePlan;
use App\Razorpay;
use App\Subscription;
use App\Invoice;
use App\AppNotification;
use App\Notifications\NewNotification;
use App\Exceptions\AuthenticationException;

class PaymentController extends Controller
{
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

        $user_token = $request->get('userToken');
        $plan_id = $request->get('planId');
        $code = $request->get('couponCode');
        $number_of_seats = $request->get('numberOfSeats');
        $gateway = $request->get('gateway');

        [$final_plan_cost, $plan_cost_with_discount, $plan] = $this->get_final_purchase_amount($plan_id, $number_of_seats, $code);

        [$api_response, $api_response_object] = $this->get_order($final_plan_cost);

        $user_token = UserToken::where("token", $user_token)->first();

        if ($user_token) {
            $user_id = $user_token->user_id;

            $payable_type = 'App\Razorpay';
            $payable_id = 1;

            if ($gateway == 'razorpay') {
                $razorpay = Razorpay::create([
                    'order_id' => $api_response_object->id,
                    'payment_id' => $api_response_object->id,
                    'signature' => 'dummytext'
                ]);
                $payable_id = $razorpay->id;
                $payable_type = 'App\Razorpay';
            }

            Payment::create([
                'user_id' => $user_id,
                'workspace_plan_id' => $plan_id,
                'amount' => $plan_cost_with_discount,
                'gst' => $plan_cost_with_discount * 0.18,
                'number_of_seats' => $number_of_seats,
                'status' => 'pending',
                'payable_id' => $payable_id,
                'payable_type' => $payable_type
            ]);

            return ['order_id' => $api_response_object->id, 'amount' => $final_plan_cost, 'user' => $user_token->user];
        }
        throw new AuthenticationException('User already exists', 'email id is already taken', 'email');
    }

    public function update_order(Request $request)
    {
        $plan_id = $request->get('planId');
        $code = $request->get('couponCode');
        $number_of_seats = $request->get('numberOfSeats');
        $gateway = $request->get('gateway');
        $order_id = $request->get('razorpay_order_id');
        $payment_id = $request->get('razorpay_payment_id');
        $signature = $request->get('razorpay_signature');
        $start_date = $request->get('startDate');
        $userId = $request->get('userId');

        try {
            [$final_plan_cost, $plan_cost_with_discount, $plan] = $this->get_final_purchase_amount($plan_id, $number_of_seats, $code);

        [$carbon_start_date, $carbon_end_date] = $this->get_end_date($start_date, $plan);
        $payment = null;

        DB::beginTransaction();

        if ($gateway == 'razorpay') {
            $razorpay_update = Razorpay::where('order_id', $order_id)->update([
                'payment_id' => $payment_id,
                'signature' => $signature
            ]);

            if ($razorpay_update) {
                $razorpay = Razorpay::with('payments')->where('payment_id', $payment_id)->where('signature', $signature)->first();
            }
            $payment =  $razorpay->payments->first();
        }

        //capture the payment
        [$api_response, $api_response_object] = $this->capture_order($final_plan_cost, $payment_id);

        // if it is verified then send success response
        if (property_exists($api_response_object, "error")) {
            return ["status" => "failed", "workspace" => null, "plan" => null];
        }

        $invoice = Invoice::create(['payment_id' => $payment->id]);

        $subscription = Subscription::create([
            'payment_id' => $payment->id,
            'start_date' => $carbon_start_date,
            'end_date' => $carbon_end_date,
            'status' => 'pending'
        ]);

        DB::commit();

        $user = User::find($userId);

        // $post_request = array(
        //     'start_date' => $carbon_start_date,
        //     'end_date' => $carbon_end_date,
        //     'number_of_seats' => $number_of_seats,
        //     'purchase_date' => $subscription->created_at,
        //     'status' => $subscription->status,
        //     'invoice_number' => $invoice->id,
        //     'plan_id' => $plan_id,
        //     'customer_email' => $user->email,
        //     'customer_profile_image' => $user->profile_image,
        //     'customer_name' => $user->name
        // );

        return view('payment_success', ["status" => "success"]);
        // return ["status" => "success", "workspace" => $plan->workspace, "plan" => $plan, "subscription_id" => $subscription->id];
        } catch (\Exception $ex) {
            return ["status" => "failed"];
        }
        

        // $vendor_url = env('VENDOR_URL') . '/api/reservations';
        // $cURL_connection = curl_init($vendor_url);
        // curl_setopt($cURL_connection, CURLOPT_POSTFIELDS, $post_request);
        // curl_setopt($cURL_connection, CURLOPT_RETURNTRANSFER, true);

        // $api_response = curl_exec($cURL_connection);
        // curl_close($cURL_connection);

        // $api_response_object = json_decode($api_response);

        // if ($api_response_object->message === 'success') {
        //     DB::commit();
        //     return ["status" => "success"];
        // } else {
        //     DB::rollBack();
        //     return ["status" => "error"];
        // }

        // $notification = AppNotification::create([
        //     'title' => "Payment Received",
        //     'description' => "We have received your payment and sent to workspace for confirmation",
        //     'button_text' => 'My Spaces',
        //     'notifiable_id' => $subscription->id,
        //     'notifiable_type' => 'App\Subscription'
        // ]);

        // $user = User::find($userId);
        // $user->notify(new NewNotification($notification, $user));

        // return redirect()->away("http://localhost:8100/workspace/{$subscription->id}/purchased");
        // return ["status" => "success", "workspace" => $plan->workspace, "plan" => $plan];

    }

    public function get_user_subscriptions(Request $request)
    {
        $token = $request->get('userToken');
        $status = $request->get('status');

        $user_token = UserToken::with('user')->where('token', $token)->first();

        $subscriptions = Subscription::with('payment.plan.workspace.images')->where('status', $status)->whereHas('payment', function ($q) use ($user_token) {
            $q->where('user_id', $user_token->user->id);
        })->get();

        return $subscriptions;
    }

    private function get_final_purchase_amount($plan_id, $number_of_seats, $code)
    {
        $plan = WorkspacePlan::with('workspace')->find($plan_id);
        if (!$plan) {
            return null;
        }
        $plan_cost = $plan->cost;
        $plan_cost_before_code_and_gst = $plan_cost * $number_of_seats;

        $coupon_code = CouponCode::where('code', $code)->first();

        $discount_amount = 0;
        if ($coupon_code) {
            $discount_amount = $coupon_code->discount_amount;
        }

        $plan_cost_with_discount = $plan_cost_before_code_and_gst - $discount_amount;

        $final_plan_cost = $plan_cost_with_discount * 1.18;

        return [$final_plan_cost, $plan_cost_with_discount, $plan];
    }

    private function get_order($final_plan_cost)
    {
        $username = env("RAZORPAY_KEY");
        $password = env("RAZORPAY_SECRET");

        $post_request = array(
            'receipt' => '1',
            'amount' => $final_plan_cost,
            'currency' => 'INR'
        );

        $cURL_connection = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt($cURL_connection, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($cURL_connection, CURLOPT_POSTFIELDS, $post_request);
        curl_setopt($cURL_connection, CURLOPT_RETURNTRANSFER, true);

        $api_response = curl_exec($cURL_connection);
        curl_close($cURL_connection);

        $api_response_array = json_decode($api_response);

        return [$api_response, $api_response_array];
    }

    private function capture_order($final_plan_cost, $payment_id)
    {
        $username = env("RAZORPAY_KEY");
        $password = env("RAZORPAY_SECRET");

        $post_request = array(
            'amount' => $final_plan_cost,
            'currency' => 'INR'
        );

        $cURL_connection = curl_init("https://api.razorpay.com/v1/payments/{$payment_id}/capture");
        curl_setopt($cURL_connection, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($cURL_connection, CURLOPT_POSTFIELDS, $post_request);
        curl_setopt($cURL_connection, CURLOPT_RETURNTRANSFER, true);

        $api_response = curl_exec($cURL_connection);
        curl_close($cURL_connection);

        $api_response_array = json_decode($api_response);

        return [$api_response, $api_response_array];
    }

    private function get_end_date($start_date, $plan)
    {
        $carbon_start_date = Carbon::createFromFormat('d-m-Y', $start_date);
        $carbon_end_date = Carbon::createFromFormat('d-m-Y', $start_date);
        if ($plan->duration % 30 == 0) {
            $carbon_end_date->addMonths($plan->duration / 30)->subDays(1);
        } else {
            $carbon_end_date->addDays($plan->duration)->subDays(1);
        }

        return [$carbon_start_date->toDateString(), $carbon_end_date->toDateString()];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Payment  $payment
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
     * @param  \App\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        //
    }
}
