<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class converter_model extends MY_Model {
	
	private $cache_duration = 3600; // 1 hour cache
	
	public function __construct(){
		parent::__construct();
	}

	/**
	 * Fetch exchange rates from API
	 * Using exchangerate-api.com free tier
	 */
	public function fetch_exchange_rates($base_currency = 'USD'){
		// Check cache first
		$cached_data = $this->get_cached_rates($base_currency);
		if ($cached_data !== false) {
			return $cached_data;
		}

		// Fetch from API
		$api_url = "https://api.exchangerate-api.com/v4/latest/" . urlencode($base_currency);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($http_code == 200 && $response) {
			$data = json_decode($response, true);
			
			if (isset($data['rates'])) {
				// Cache the results
				$this->cache_rates($base_currency, $data);
				return $data;
			}
		}
		
		return false;
	}

	/**
	 * Convert currency from one to another
	 */
	public function convert_currency($amount, $from, $to){
		$amount = floatval($amount);
		
		if ($amount <= 0) {
			return false;
		}
		
		// If same currency, return same amount
		if (strtoupper($from) === strtoupper($to)) {
			return floatval($amount);
		}
		
		// Get rates for the source currency
		$rates_data = $this->fetch_exchange_rates($from);
		
		if (!$rates_data || !isset($rates_data['rates'][$to])) {
			return false;
		}
		
		$rate = $rates_data['rates'][$to];
		$result = $amount * $rate;
		
		return round($result, 2);
	}

	/**
	 * Get list of supported currencies
	 */
	public function get_supported_currencies(){
		// Common currencies with their full names
		return [
			'USD' => 'US Dollar',
			'EUR' => 'Euro',
			'GBP' => 'British Pound',
			'JPY' => 'Japanese Yen',
			'AUD' => 'Australian Dollar',
			'CAD' => 'Canadian Dollar',
			'CHF' => 'Swiss Franc',
			'CNY' => 'Chinese Yuan',
			'INR' => 'Indian Rupee',
			'MXN' => 'Mexican Peso',
			'BRL' => 'Brazilian Real',
			'ZAR' => 'South African Rand',
			'RUB' => 'Russian Ruble',
			'KRW' => 'South Korean Won',
			'SGD' => 'Singapore Dollar',
			'HKD' => 'Hong Kong Dollar',
			'NOK' => 'Norwegian Krone',
			'SEK' => 'Swedish Krona',
			'DKK' => 'Danish Krone',
			'PLN' => 'Polish Zloty',
			'THB' => 'Thai Baht',
			'IDR' => 'Indonesian Rupiah',
			'HUF' => 'Hungarian Forint',
			'CZK' => 'Czech Koruna',
			'ILS' => 'Israeli Shekel',
			'CLP' => 'Chilean Peso',
			'PHP' => 'Philippine Peso',
			'AED' => 'UAE Dirham',
			'SAR' => 'Saudi Riyal',
			'MYR' => 'Malaysian Ringgit',
			'TRY' => 'Turkish Lira',
			'PKR' => 'Pakistani Rupee',
			'NGN' => 'Nigerian Naira',
			'EGP' => 'Egyptian Pound',
			'VND' => 'Vietnamese Dong',
			'BDT' => 'Bangladeshi Taka',
			'ARS' => 'Argentine Peso',
			'TWD' => 'New Taiwan Dollar',
			'NZD' => 'New Zealand Dollar'
		];
	}

	/**
	 * Cache exchange rates
	 */
	private function cache_rates($base_currency, $data){
		$cache_dir = FCPATH . 'storage/cache/';
		
		// Create cache directory if it doesn't exist
		if (!is_dir($cache_dir)) {
			mkdir($cache_dir, 0755, true);
		}
		
		$cache_file = $cache_dir . 'currency_rates_' . $base_currency . '.json';
		
		$cache_data = [
			'timestamp' => time(),
			'data' => $data
		];
		
		file_put_contents($cache_file, json_encode($cache_data));
	}

	/**
	 * Get cached exchange rates
	 */
	private function get_cached_rates($base_currency){
		$cache_dir = FCPATH . 'storage/cache/';
		$cache_file = $cache_dir . 'currency_rates_' . $base_currency . '.json';
		
		if (!file_exists($cache_file)) {
			return false;
		}
		
		$cache_content = file_get_contents($cache_file);
		$cache_data = json_decode($cache_content, true);
		
		if (!$cache_data || !isset($cache_data['timestamp'])) {
			return false;
		}
		
		// Check if cache is still valid
		if (time() - $cache_data['timestamp'] > $this->cache_duration) {
			return false;
		}
		
		return $cache_data['data'];
	}
}
