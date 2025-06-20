<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Item;
use Carbon\Carbon;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature');
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;

                $itemId = $session->metadata->item_id ?? null;
                $buyerId = $session->metadata->user_id ?? null;
                if ($itemId && $buyerId) {
                    $item = Item::find($itemId);

                    if ($item && $item->sold_at === null) {
                        $item->update([
                            'sold_at' => Carbon::now(),
                            'buyer_id' => $buyerId,
                        ]);
                    }
                }
                break;
            default:
                break;
        }

        return response()->json(['status' => 'success'], 200);
    }
}