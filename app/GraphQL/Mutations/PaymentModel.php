<?php

namespace App\GraphQL\Mutations;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Payment;
use App\WorkspacePlan;
use App\CouponCode;
use App\Razorpay;
use App\Subscription;
use App\Invoice;
use App\Paytm;

class PaymentModel
{
    public function generate_order($rootValue, array $args)
    {
        $plan_id = $args['planId'];
        $code = $args['couponCode'];
        $number_of_seats = $args['numberOfSeats'];
        $gateway = $args['gateway'];
        $start_date = $args['startDate'];
        $user = request()->user();

        [$final_plan_cost, $plan_cost_with_discount, $plan] = $this->get_final_purchase_amount($plan_id, $number_of_seats, $code);

        if ($final_plan_cost != 0) {

            $api_response_object = $this->get_order($final_plan_cost);

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
        } else {
            $random_order_id = "order_" . $this->rand_string(14);
            $razorpay = Razorpay::create([
                'order_id' => $random_order_id,
                'payment_id' => $random_order_id,
                'signature' => 'dummytext'
            ]);
            $payable_id = $razorpay->id;
            $payable_type = 'App\Razorpay';
        }

        $payment = Payment::create([
            'user_id' => $user->id,
            'workspace_plan_id' => $plan_id,
            'amount' => $plan_cost_with_discount,
            'gst' => $plan_cost_with_discount * 0.18,
            'number_of_seats' => $number_of_seats,
            'status' => 'pending',
            'payable_id' => $payable_id,
            'payable_type' => $payable_type,
            'coupon_code' => $code
        ]);
        [$carbon_start_date, $carbon_end_date] = $this->get_end_date($start_date, $plan);
        if ($final_plan_cost != 0) {
            $subscription = Subscription::create([
                'payment_id' => $payment->id,
                'start_date' => $carbon_start_date,
                'end_date' => $carbon_end_date,
                'status' => 'pending'
            ]);
            return ['order_id' => $api_response_object->id, 'amount' => $final_plan_cost, 'user' => $user, 'subscription_id' => $subscription->id];
        }
        $subscription = Subscription::create([
            'payment_id' => $payment->id,
            'start_date' => $carbon_start_date,
            'end_date' => $carbon_end_date,
            'status' => 'pending'
        ]);
        Invoice::create(['payment_id' => $payment->id]);
        return ['order_id' => $random_order_id, 'amount' => $final_plan_cost, 'user' => $user, 'subscription_id' => $subscription->id];
    }

    public function update_order($rootValue, array $args)
    {
        $plan_id = $args['planId'];
        $number_of_seats = $args['numberOfSeats'];
        $gateway = $args['gateway'];
        $order_id = $args['razorpay_order_id'];
        $payment_id = $args['razorpay_payment_id'];
        $signature = $args['razorpay_signature'];
        $start_date = $args['startDate'];
        $code = $args['couponCode'];
        $userId = request()->user()->id;

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

            return ["status" => "success", "workspace" => $plan->workspace, "plan" => $plan, "subscription_id" => $subscription->id];
        } catch (\Exception $ex) {
            return ["status" => "failed"];
        }
    }

    public function generate_order_v1($rootValue, array $args)
    {
        $plan_id = $args['planId'];
        $code = $args['couponCode'];
        $number_of_seats = $args['numberOfSeats'];
        $gateway = $args['gateway'];
        $start_date = $args['startDate'];
        $user = request()->user();

        [$final_plan_cost, $plan_cost_with_discount, $plan] = $this->get_final_purchase_amount($plan_id, $number_of_seats, $code);

        if ($final_plan_cost != 0) {
            $api_response_object = $this->get_order($final_plan_cost);

            if ($gateway == 'paytm') {
                $paytm = Paytm::create([
                    'order_id' => $api_response_object->id,
                    'payment_id' => $api_response_object->id
                ]);
                $payable_id = $paytm->id;
                $payable_type = 'App\Paytm';
            }
        } else {
            $random_order_id = "order_" . $this->rand_string(14);
            $paytm = Paytm::create([
                'order_id' => $random_order_id,
                'payment_id' => $random_order_id,
            ]);
            $payable_id = $paytm->id;
            $payable_type = 'App\Paytm';
        }

        $payment = Payment::create([
            'user_id' => $user->id,
            'workspace_plan_id' => $plan_id,
            'amount' => $plan_cost_with_discount,
            'gst' => $plan_cost_with_discount * 0.18,
            'number_of_seats' => $number_of_seats,
            'status' => 'pending',
            'payable_id' => $payable_id,
            'payable_type' => $payable_type,
            'coupon_code' => $code
        ]);

        [$carbon_start_date, $carbon_end_date] = $this->get_end_date($start_date, $plan);

        if ($final_plan_cost != 0) {

            $subscription = Subscription::create([
                'payment_id' => $payment->id,
                'start_date' => $carbon_start_date,
                'end_date' => $carbon_end_date,
                'status' => 'pending'
            ]);

            return ['order_id' => $api_response_object->id, 'amount' => $final_plan_cost, 'user' => $user, 'subscription_id' => $subscription->id];
        }

        $subscription = Subscription::create([
            'payment_id' => $payment->id,
            'start_date' => $carbon_start_date,
            'end_date' => $carbon_end_date,
            'status' => 'pending'
        ]);

        Invoice::create(['payment_id' => $payment->id]);

        return ['order_id' => $random_order_id, 'amount' => $final_plan_cost, 'user' => $user, 'subscription_id' => $subscription->id];
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

        $api_response_object = json_decode($api_response);

        return $api_response_object;
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

        $api_response_object = json_decode($api_response);

        return [$api_response, $api_response_object];
    }

    private function get_end_date($start_date, $plan)
    {
        $carbon_start_date = Carbon::createFromFormat('d-m-Y', $start_date);
        $carbon_end_date = Carbon::createFromFormat('d-m-Y', $start_date);
        if ($plan->duration % 30 == 0) {
            $carbon_end_date->addMonths($plan->duration / 30)->subDay();
        } else {
            $carbon_end_date->addDays($plan->duration)->subDay();
        }

        return [$carbon_start_date, $carbon_end_date];
    }

    private function rand_string($length)
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $size = strlen($chars);
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $size - 1)];
        }
        return $str;
    }
}
