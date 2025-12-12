<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class PaymentController extends Controller
{
    private function getSecretKey()
    {
        return Setting::where('key', 'paymongo_secret_key')->value('value');
    }

    public function createSource(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);

        // PayMongo requires amount in centavos (100.00 -> 10000)
        $amount = (int) ($request->amount * 100); 
        $secretKey = $this->getSecretKey();

        if (empty($secretKey)) {
            return response()->json(['success' => false, 'message' => 'PayMongo Secret Key is missing in Settings.']);
        }
        
        try {
            // FIX: Added 'withoutVerifying' and 'timeout' to solve cURL 28 error
            $response = Http::withoutVerifying()
                ->timeout(60) // Wait up to 60 seconds
                ->withBasicAuth($secretKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post('https://api.paymongo.com/v1/links', [
                    'data' => [
                        'attributes' => [
                            'amount' => $amount,
                            'description' => 'POS Transaction',
                            'remarks' => 'POS Payment - ' . now()->format('Y-m-d H:i')
                        ]
                    ]
                ]);

            if ($response->failed()) {
                // Log error for debugging
                \Illuminate\Support\Facades\Log::error('PayMongo API Error: ' . $response->body());
                
                $errorBody = $response->json();
                $errorMsg = $errorBody['errors'][0]['detail'] ?? 'Unknown API Error';
                return response()->json(['success' => false, 'message' => 'API: ' . $errorMsg]);
            }

            $data = $response->json()['data'];
            
            return response()->json([
                'success' => true,
                'checkout_url' => $data['attributes']['checkout_url'],
                'reference_number' => $data['attributes']['reference_number'],
                'id' => $data['id']
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
        }
    }

    public function checkStatus($id)
    {
        $secretKey = $this->getSecretKey();

        try {
            // Also apply fixes here
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withBasicAuth($secretKey, '')
                ->withHeaders(['Accept' => 'application/json'])
                ->get("https://api.paymongo.com/v1/links/{$id}");

            if ($response->failed()) return response()->json(['status' => 'error']);

            $data = $response->json()['data'];
            return response()->json(['status' => $data['attributes']['status']]); // unpaid, paid

        } catch (\Exception $e) {
            return response()->json(['status' => 'error']);
        }
    }
}