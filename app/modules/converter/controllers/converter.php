<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class converter extends MX_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
	}

	public function index(){
		$data = array(
			"module" => get_class($this),
		);
		$this->template->build("index", $data);
	}

	/**
	 * Get exchange rates from API
	 * AJAX endpoint
	 */
	public function get_rates(){
		$base_currency = $this->input->post('base', TRUE);
		
		if (empty($base_currency)) {
			$base_currency = 'USD';
		}
		
		$rates = $this->model->fetch_exchange_rates($base_currency);
		
		if ($rates) {
			echo json_encode([
				'status' => 'success',
				'data' => $rates
			]);
		} else {
			echo json_encode([
				'status' => 'error',
				'message' => 'Failed to fetch exchange rates'
			]);
		}
	}

	/**
	 * Convert currency amount
	 * AJAX endpoint
	 */
	public function convert(){
		$amount = $this->input->post('amount', TRUE);
		$from = $this->input->post('from', TRUE);
		$to = $this->input->post('to', TRUE);
		
		if (empty($amount) || empty($from) || empty($to)) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Missing required parameters'
			]);
			return;
		}
		
		// Validate amount is greater than 0
		$amount_float = floatval($amount);
		if ($amount_float <= 0) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Amount must be greater than 0'
			]);
			return;
		}
		
		$result = $this->model->convert_currency($amount_float, $from, $to);
		
		if ($result !== false) {
			echo json_encode([
				'status' => 'success',
				'data' => [
					'amount' => $amount_float,
					'from' => $from,
					'to' => $to,
					'result' => $result,
					'rate' => $amount_float > 0 ? ($result / $amount_float) : 0
				]
			]);
		} else {
			echo json_encode([
				'status' => 'error',
				'message' => 'Conversion failed'
			]);
		}
	}

	/**
	 * Get list of supported currencies
	 * AJAX endpoint
	 */
	public function get_currencies(){
		$currencies = $this->model->get_supported_currencies();
		
		echo json_encode([
			'status' => 'success',
			'data' => $currencies
		]);
	}
}
