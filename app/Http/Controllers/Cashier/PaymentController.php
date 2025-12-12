<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class PaymentController extends Controller
{
    private function getHeaders()
    {
        $secretKey = Setting::where('key', 'paymongo_secret_key')->value('value');
        return [
            'Authorization' => 'Basic ' . base64_encode($secretKey),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function createSource(Request $request)
    {
        $amount = $request->amount * 100; // PayMongo uses centavos (e.g. 100.00 -> 10000)
        
        try {
            // 1. Create a "Source" (GCash or GrabPay)
            // Note: PayMongo Links API is simpler, but Source API allows custom UI integration.
            // We will use the 'links' API for simplicity in a POS context (generates a QR/URL).
            
            $response = Http::withHeaders($this->getHeaders())
                ->post('https://api.paymongo.com/v1/links', [
                    'data' => [
                        'attributes' => [
                            'amount' => $amount,
                            'description' => 'POS Transaction',
                            'remarks' => 'POS Payment'
                        ]
                    ]
                ]);

            if ($response->failed()) {
                return response()->json(['success' => false, 'message' => $response->body()]);
            }

            $data = $response->json()['data'];
            
            return response()->json([
                'success' => true,
                'checkout_url' => $data['attributes']['checkout_url'],
                'reference_number' => $data['attributes']['reference_number'],
                'id' => $data['id'] // Store this to check status later
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function checkStatus($id)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get("https://api.paymongo.com/v1/links/{$id}");

            if ($response->failed()) {
                return response()->json(['status' => 'error']);
            }

            $data = $response->json()['data'];
            $status = $data['attributes']['status']; // unpaid, paid

            return response()->json(['status' => $status]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error']);
        }
    }
}