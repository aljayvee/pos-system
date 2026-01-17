<?php

namespace App\Services\Analytics;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForecastingService
{
    public function generateForecast(int $storeId, int $daysToAnalyze = 30)
    {
        $startDate = Carbon::now()->subDays($daysToAnalyze);

        // 1. Fetch Products with Store-Specific Inventory & Sales Data
        $products = Product::select('products.*', 'inventories.stock as current_stock', 'inventories.reorder_point')
            ->join('inventories', function ($join) use ($storeId) {
                $join->on('products.id', '=', 'inventories.product_id')
                    ->where('inventories.store_id', '=', $storeId);
            })
            ->with(['category'])
            ->withSum([
                'saleItems as total_qty_sold' => function ($q) use ($startDate, $storeId) {
                    $q->whereHas('sale', function ($sq) use ($startDate, $storeId) {
                        $sq->where('created_at', '>=', $startDate)
                            ->where('store_id', $storeId);
                    });
                }
            ], 'quantity')
            // Calculate Revenue for ABC Analysis manually later or here if needed
            ->get();

        // 2. Prepare Data Structure
        $forecastData = [];
        $totalRevenue = 0;

        foreach ($products as $p) {
            $qtySold = $p->total_qty_sold ?? 0;
            $revenue = $qtySold * $p->price;

            $p->real_revenue = $revenue; // Attach for sorting
            $totalRevenue += $revenue;

            // Velocity (Items per day)
            $velocity = $qtySold / $daysToAnalyze;

            // Days of Inventory (DOI)
            $doi = ($velocity > 0) ? ($p->current_stock / $velocity) : 999;

            $forecastData[] = [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category->name ?? 'Uncategorized',
                'stock' => $p->current_stock,
                'reorder_point' => $p->reorder_point,
                'velocity' => $velocity,
                'doi' => $doi,
                'revenue' => $revenue,
                'status' => 'Healthy',
                'class' => 'C',
                'movement' => 'Non-Moving',
                'reorder_qty' => 0
            ];
        }

        // 3. ABC Analysis & Classification
        $forecastData = $this->performABCAnalysis($forecastData, $totalRevenue);

        // 4. Movement & Status Classification
        $forecastData = $this->classifyMovementAndHealth($forecastData, $daysToAnalyze);

        // 5. Final Sort (Priority: Status -> ABC)
        usort($forecastData, function ($a, $b) {
            $statusOrder = ['Out of Stock' => 1, 'Critical' => 2, 'Low' => 3, 'Healthy' => 4];
            $statusCompare = $statusOrder[$a['status']] <=> $statusOrder[$b['status']];
            if ($statusCompare !== 0)
                return $statusCompare;

            return strcmp($a['class'], $b['class']);
        });

        // 6. Summary Metrics
        $outOfStockCount = count(array_filter($forecastData, fn($i) => $i['status'] === 'Out of Stock'));
        $criticalCount = count(array_filter($forecastData, fn($i) => $i['status'] === 'Critical'));

        return [
            'forecastData' => $forecastData,
            'outOfStockCount' => $outOfStockCount,
            'criticalCount' => $criticalCount
        ];
    }

    private function performABCAnalysis(array $data, float $totalRevenue): array
    {
        // Sort by Revenue DESC
        usort($data, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        $cumulativeRevenue = 0;
        foreach ($data as &$item) {
            $cumulativeRevenue += $item['revenue'];
            $percentage = ($totalRevenue > 0) ? ($cumulativeRevenue / $totalRevenue) : 0;

            if ($percentage <= 0.80) {
                $item['class'] = 'A';
            } elseif ($percentage <= 0.95) {
                $item['class'] = 'B';
            } else {
                $item['class'] = 'C';
            }
        }
        return $data;
    }

    private function classifyMovementAndHealth(array $data, int $daysToAnalyze): array
    {
        foreach ($data as &$item) {
            $v = $item['velocity'];

            // Movement Logic
            if ($v >= 1.0)
                $item['movement'] = 'Fast Moving';
            elseif ($v > 0.1)
                $item['movement'] = 'Average';
            elseif ($v > 0)
                $item['movement'] = 'Slow Moving';
            else
                $item['movement'] = 'Non-Moving';

            // Stock Health Logic
            if ($item['stock'] == 0) {
                $item['status'] = 'Out of Stock';
            } elseif ($item['stock'] <= $item['reorder_point']) {
                $item['status'] = 'Critical';
            } elseif ($item['doi'] <= 7) {
                $item['status'] = 'Low';
            } else {
                $item['status'] = 'Healthy';
            }

            // Suggested Reorder (Target: 14 Days Safety Stock)
            $targetStock = $item['velocity'] * 14;
            if ($item['stock'] < $targetStock) {
                $item['reorder_qty'] = ceil($targetStock - $item['stock']);
            }
        }
        return $data;
    }
}
