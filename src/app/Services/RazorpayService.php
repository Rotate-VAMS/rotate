<?php

namespace App\Services;

use Razorpay\Api\Api;
use Exchanger\ExchangeRateQueryBuilder;
use Exchanger\Service\Chain;
use Exchanger\Service\CurrencyLayer;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Exchanger\Exchanger;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );
    }

    /**
     * Create a Razorpay order
     */
    public function createOrder(float $amount): array
    {
        $amountInInr = $this->convertUsdToInr($amount);

        $order = $this->api->order->create([
            'receipt' => 'rcpt_' . uniqid(),
            'amount' => $amountInInr * 100, // in paise
            'currency' => 'INR',
            'payment_capture' => 1,
        ]);

        return $order->toArray();
    }

    /**
     * Fetch a Razorpay payment
     */
    public function fetchPayment(string $paymentId): array
    {
        return $this->api->payment->fetch($paymentId)->toArray();
    }

    /**
     * Convert USD amount to INR using live exchange rate with fallback
     * 
     * @param float $usdAmount Amount in USD
     * @return int Amount in paise (INR * 100) for Razorpay
     */
    public function convertUsdToInr(float $usdAmount): int
    {
        // Try to get live exchange rate first
        $exchangeRate = $this->getLiveExchangeRate();
        
        if ($exchangeRate !== null) {
            $inrAmount = $usdAmount * $exchangeRate;
            
            Log::info('USD to INR conversion successful', [
                'usd_amount' => $usdAmount,
                'exchange_rate' => $exchangeRate,
                'inr_amount' => $inrAmount,
                'source' => 'live_api'
            ]);
            
            return (int) round($inrAmount); // return amount in paise for Razorpay
        }
        
        // Fallback to configured rate
        $fallbackRate = config('services.razorpay.usd_to_inr_rate', 83);
        $inrAmount = $usdAmount * $fallbackRate;

        Log::warning('USD to INR conversion using fallback rate', [
            'usd_amount' => $usdAmount,
            'fallback_rate' => $fallbackRate,
            'inr_amount' => $inrAmount,
            'source' => 'fallback'
        ]);

        return (int) round($inrAmount); // return amount in paise for Razorpay
    }
    
    /**
     * Get live exchange rate from CurrencyLayer API
     * 
     * @return float|null Exchange rate or null if failed
     */
    private function getLiveExchangeRate(): ?float
    {
        // Check cache first
        $cachedRate = cache('usd_to_inr_rate');
        if ($cachedRate !== null) {
            Log::info('Using cached USD to INR exchange rate', [
                'rate' => $cachedRate,
                'source' => 'cache'
            ]);
            return $cachedRate;
        }
        
        try {
            $apiKey = config('exchanger.services.' . CurrencyLayer::class . '.access_key');
            
            if (empty($apiKey)) {
                Log::warning('CurrencyLayer API key not configured');
                return null;
            }
            
            // Use a simple HTTP request to get the rate
            $url = "http://api.currencylayer.com/live?access_key={$apiKey}&currencies=INR&source=USD&format=1";
            $response = file_get_contents($url);
            
            if ($response === false) {
                Log::warning('Failed to fetch exchange rate from CurrencyLayer');
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (!isset($data['quotes']['USDINR'])) {
                Log::warning('Invalid response from CurrencyLayer API', ['response' => $data]);
                return null;
            }
            
            $rate = (float) $data['quotes']['USDINR'];
            
            // Cache the rate for 24 hours
            cache(['usd_to_inr_rate' => $rate], now()->addHours(24));
            
            Log::info('Fetched and cached new USD to INR exchange rate', [
                'rate' => $rate,
                'source' => 'api',
                'cache_duration' => '24 hours'
            ]);
            
            return $rate;
            
        } catch (\Exception $e) {
            Log::error('Error fetching live exchange rate', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}