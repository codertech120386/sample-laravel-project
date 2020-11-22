<?php

namespace App\GraphQL\Queries;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

use App\Subscription;
use App\Invoice;
use App\Payment;
use App\Mail\RequestPDFInvoice;

class SubscriptionModel
{
    public function get_subscription($rootValue, array $args)
    {
        return Subscription::with('payment.plan.workspace.images')->find($args['id']);
    }

    public function get_user_subscriptions($rootValue, array $args)
    {
        $status = $args['status'];
        $user_id = request()->user()->id;
        $today = Carbon::now();
        $today_string = $today->toDateString();

        $subscriptions = [];

        if ($status == "pending" || $status == "rejected") {
            $subscriptions = Subscription::with('payment.plan.workspace.images')->where('status', $status)->whereHas('payment', function ($q) use ($user_id) {
                $q->where('user_id', $user_id)->where('status', 'success');
            })->orderBy('start_date', 'asc')->get();
        } elseif ($status == 'active') {
            $subscriptions = Subscription::with('payment.plan.workspace.images')->where('status', "confirmed")->where('start_date', '<=', Carbon::now())->where('end_date', '>=', Carbon::now())->whereHas('payment', function ($q) use ($user_id) {
                $q->where('user_id', $user_id)->where('status', 'success');
            })->orderBy('start_date', 'desc')->get();
        } elseif ($status == 'expired') {
            $subscriptions = Subscription::with('payment.plan.workspace.images')->where('status', "confirmed")->whereDate('end_date', '<', $today_string)->whereHas('payment', function ($q) use ($user_id) {
                $q->where('user_id', $user_id)->where('status', 'success');
            })->orderBy('end_date', 'desc')->get();
        } else if ($status == 'pendingOrActive') {
            $pendingSubscriptions = Subscription::with('payment.plan.workspace.images')->where('status', "pending")->whereHas('payment', function ($q) use ($user_id) {
                $q->where('user_id', $user_id)->where('status', 'success');
            })->orderBy('start_date', 'desc')->get();

            $activeSubscriptions = Subscription::with('payment.plan.workspace.images')->where('status', "confirmed")->where('start_date', '<=', Carbon::now())->where('end_date', '>=', Carbon::now())->whereHas('payment', function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            })->orderBy('start_date', 'desc')->get();

            $subscriptions = new Collection;
            $subscriptions = $subscriptions->merge($pendingSubscriptions);
            $subscriptions = $subscriptions->merge($activeSubscriptions);
        }

        return $subscriptions;
    }

    public function get_user_payments($rootValue, array $args)
    {
        return Payment::with('user')->with('plan.workspace.images')->where('user_id', request()->user()->id)->orderBy('created_at', 'desc')->get();
    }

    public function request_invoice_pdf($rootValue, array $args)
    {
        $invoice = Invoice::updateOrCreate(["payment_id" => $args['paymentId']], ["company" => $args['company'], "gst_number" => $args["gstNumber"], "address" => $args["address"]]);

        Mail::to("suchika@coffic.com")->send(new RequestPDFInvoice(request()->user(), $invoice));

        return $invoice;
    }
}
