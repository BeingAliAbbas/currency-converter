<?php
/**
 * Currency Converter - Usage Examples
 * 
 * This file demonstrates how to use the currency converter module
 * in your SMM Panel Script application.
 * 
 * NOTE: This is an example file for reference only.
 * Do not include this file in production.
 */

// ====================================================================
// EXAMPLE 1: Basic Currency Conversion
// ====================================================================

class Example_controller extends MX_Controller {
    
    public function basic_conversion() {
        // Load the converter model
        $this->load->model('converter/converter_model');
        
        // Convert 100 USD to EUR
        $result = $this->converter_model->convert_currency(100, 'USD', 'EUR');
        
        if ($result !== false) {
            echo "100 USD = " . $result . " EUR";
        } else {
            echo "Conversion failed";
        }
    }
}

// ====================================================================
// EXAMPLE 2: Get Exchange Rates for Multiple Currencies
// ====================================================================

class Example_rates extends MX_Controller {
    
    public function get_all_rates() {
        $this->load->model('converter/converter_model');
        
        // Get all rates for USD
        $rates_data = $this->converter_model->fetch_exchange_rates('USD');
        
        if ($rates_data && isset($rates_data['rates'])) {
            echo "Exchange rates for USD:\n";
            
            foreach ($rates_data['rates'] as $currency => $rate) {
                echo "1 USD = " . $rate . " " . $currency . "\n";
            }
        }
    }
}

// ====================================================================
// EXAMPLE 3: Calculate Product Price in Different Currencies
// ====================================================================

class Product_controller extends MX_Controller {
    
    public function show_price_in_currencies($product_id) {
        $this->load->model('converter/converter_model');
        
        // Assume product price is in USD
        $product_price_usd = 99.99;
        
        // Currencies to show
        $currencies = ['EUR', 'GBP', 'JPY', 'INR'];
        
        echo "<h3>Product Price</h3>";
        echo "<p>USD: $" . $product_price_usd . "</p>";
        
        foreach ($currencies as $currency) {
            $converted = $this->converter_model->convert_currency(
                $product_price_usd, 
                'USD', 
                $currency
            );
            
            if ($converted !== false) {
                echo "<p>" . $currency . ": " . $converted . "</p>";
            }
        }
    }
}

// ====================================================================
// EXAMPLE 4: User Balance in Different Currencies
// ====================================================================

class User_balance extends MX_Controller {
    
    public function show_balance() {
        $this->load->model('converter/converter_model');
        
        // Get user's balance (assuming it's in USD)
        $user_balance = 250.00; // This would come from database
        
        // User's preferred currency
        $user_currency = 'EUR'; // This would come from user settings
        
        $converted_balance = $this->converter_model->convert_currency(
            $user_balance,
            'USD',
            $user_currency
        );
        
        $data = [
            'balance_usd' => $user_balance,
            'balance_converted' => $converted_balance,
            'currency' => $user_currency
        ];
        
        $this->load->view('balance_view', $data);
    }
}

// ====================================================================
// EXAMPLE 5: AJAX Endpoint for Custom Integration
// ====================================================================

class Custom_converter extends MX_Controller {
    
    public function ajax_convert() {
        $this->load->model('converter/converter_model');
        
        // Get POST parameters
        $amount = $this->input->post('amount', TRUE);
        $from = $this->input->post('from', TRUE);
        $to = $this->input->post('to', TRUE);
        
        // Validate inputs
        if (empty($amount) || empty($from) || empty($to)) {
            echo json_encode([
                'success' => false,
                'error' => 'Missing parameters'
            ]);
            return;
        }
        
        // Perform conversion
        $result = $this->converter_model->convert_currency($amount, $from, $to);
        
        if ($result !== false) {
            // Get the exchange rate
            $rate = $result / $amount;
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'original_amount' => $amount,
                    'from_currency' => $from,
                    'to_currency' => $to,
                    'converted_amount' => $result,
                    'exchange_rate' => $rate,
                    'formatted' => number_format($result, 2)
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Conversion failed'
            ]);
        }
    }
}

// ====================================================================
// EXAMPLE 6: Batch Conversion
// ====================================================================

class Batch_converter extends MX_Controller {
    
    public function convert_multiple_amounts() {
        $this->load->model('converter/converter_model');
        
        // Array of amounts to convert
        $amounts = [10, 50, 100, 500, 1000];
        $from = 'USD';
        $to = 'EUR';
        
        echo "<h3>Batch Conversion: {$from} to {$to}</h3>";
        
        foreach ($amounts as $amount) {
            $result = $this->converter_model->convert_currency($amount, $from, $to);
            
            if ($result !== false) {
                echo "<p>{$amount} {$from} = {$result} {$to}</p>";
            }
        }
    }
}

// ====================================================================
// EXAMPLE 7: Get Supported Currencies
// ====================================================================

class Currency_list extends MX_Controller {
    
    public function show_all_currencies() {
        $this->load->model('converter/converter_model');
        
        $currencies = $this->converter_model->get_supported_currencies();
        
        echo "<h3>Supported Currencies</h3>";
        echo "<ul>";
        
        foreach ($currencies as $code => $name) {
            echo "<li><strong>{$code}</strong>: {$name}</li>";
        }
        
        echo "</ul>";
    }
}

// ====================================================================
// EXAMPLE 8: Order Total in Customer's Currency
// ====================================================================

class Order_controller extends MX_Controller {
    
    public function calculate_order_total() {
        $this->load->model('converter/converter_model');
        
        // Order items (prices in USD)
        $items = [
            ['name' => 'Item 1', 'price' => 29.99],
            ['name' => 'Item 2', 'price' => 49.99],
            ['name' => 'Item 3', 'price' => 19.99]
        ];
        
        // Calculate total in USD
        $total_usd = array_sum(array_column($items, 'price'));
        
        // Customer's currency
        $customer_currency = 'GBP';
        
        // Convert total
        $total_converted = $this->converter_model->convert_currency(
            $total_usd,
            'USD',
            $customer_currency
        );
        
        echo "<h3>Order Summary</h3>";
        foreach ($items as $item) {
            echo "<p>{$item['name']}: \${$item['price']}</p>";
        }
        echo "<hr>";
        echo "<p><strong>Total (USD): \${$total_usd}</strong></p>";
        echo "<p><strong>Total ({$customer_currency}): {$total_converted}</strong></p>";
    }
}

// ====================================================================
// EXAMPLE 9: Service Price Display
// ====================================================================

class Service_pricing extends MX_Controller {
    
    public function show_service_prices() {
        $this->load->model('converter/converter_model');
        $this->load->model('services/services_model');
        
        // Get services (example)
        $services = $this->services_model->get_services();
        
        // User's preferred currency from session or settings
        $user_currency = isset($_SESSION['currency']) ? $_SESSION['currency'] : 'USD';
        
        foreach ($services as $service) {
            // Assume service price is in USD
            $price_usd = $service->price;
            
            if ($user_currency != 'USD') {
                $price_converted = $this->converter_model->convert_currency(
                    $price_usd,
                    'USD',
                    $user_currency
                );
                
                echo "<p>{$service->name}: {$price_converted} {$user_currency}</p>";
            } else {
                echo "<p>{$service->name}: \${$price_usd}</p>";
            }
        }
    }
}

// ====================================================================
// EXAMPLE 10: JavaScript Integration
// ====================================================================
?>

<!-- HTML/JavaScript Example -->
<script>
// Real-time conversion as user types
$('#amount_input').on('input', function() {
    var amount = $(this).val();
    var from = $('#from_currency').val();
    var to = $('#to_currency').val();
    
    if (amount > 0) {
        $.ajax({
            url: '<?=base_url()?>converter/convert',
            type: 'POST',
            data: {
                amount: amount,
                from: from,
                to: to
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#result_display').text(response.data.result);
                }
            }
        });
    }
});

// Convert multiple currencies at once
function convertToMultipleCurrencies(amount, baseCurrency) {
    var currencies = ['EUR', 'GBP', 'JPY', 'INR'];
    var promises = [];
    
    currencies.forEach(function(currency) {
        promises.push(
            $.ajax({
                url: '<?=base_url()?>converter/convert',
                type: 'POST',
                data: {
                    amount: amount,
                    from: baseCurrency,
                    to: currency
                }
            })
        );
    });
    
    Promise.all(promises).then(function(results) {
        results.forEach(function(response, index) {
            if (response.status === 'success') {
                console.log(currencies[index] + ': ' + response.data.result);
            }
        });
    });
}

// Usage
convertToMultipleCurrencies(100, 'USD');
</script>

<?php
// ====================================================================
// NOTES:
// ====================================================================
// 
// 1. Always check if conversion returns false (indicates an error)
// 2. Results are cached for 1 hour to minimize API calls
// 3. The API has rate limits - use caching wisely
// 4. For production, consider error handling and fallback values
// 5. Currency codes are case-insensitive but uppercase is recommended
// 6. Amounts must be numeric and greater than 0
// 7. The module uses exchangerate-api.com by default
// 8. You can modify the API provider in the model if needed
// 
// ====================================================================
?>
