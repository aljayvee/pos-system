<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class PaymentController extends Controller
{
    // Helper to get the secret key
    private function getSecretKey()
    {
        return Setting::where('key', 'paymongo_secret_key')->value('value');
    }

    public function createSource(Request $request)
    {
        // 1. Validation
        $request->validate(['amount' => 'required|numeric|min:1']);

        // 2. Convert to Centavos (PayMongo format: 100.00 -> 10000)
        $amount = (int) ($request->amount * 100); 
        $secretKey = $this->getSecretKey();

        if (empty($secretKey)) {
            return response()->json(['success' => false, 'message' => 'PayMongo Secret Key is missing in Settings.']);
        }
        
        try {
            // 3. Call PayMongo API (Using Basic Auth)
            $response = Http::withBasicAuth($secretKey, '') // <--- FIX: Correct Auth Format
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

            // 4. Handle API Errors
            if ($response->failed()) {
                // Log the error for debugging if needed
                \Illuminate\Support\Facades\Log::error('PayMongo Error: ' . $response->body());
                
                // Return readable error message
                $errorMsg = $response->json()['errors'][0]['detail'] ?? 'Unknown API Error';
                return response()->json(['success' => false, 'message' => 'API: ' . $errorMsg]);
            }

            // 5. Success
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
            $response = Http::withBasicAuth($secretKey, '')
                ->withHeaders(['Accept' => 'application/json'])
                ->get("https://api.paymongo.com/v1/links/{$id}");

            if ($response->failed()) return response()->json(['status' => 'error']);

            $data = $response->json()['data'];
            // Possible statuses: unpaid, paid, archived
            return response()->json(['status' => $data['attributes']['status']]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error']);
        }
    }
}