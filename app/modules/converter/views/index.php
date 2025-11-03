<style>
  .converter-card {
    margin-top: 20px;
  }
  .currency-input {
    font-size: 1.5rem;
    font-weight: 500;
  }
  .currency-select {
    font-size: 1.1rem;
  }
  .exchange-rate-info {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    margin-top: 15px;
  }
  .swap-button {
    margin: 20px 0;
  }
  .conversion-result {
    font-size: 2rem;
    font-weight: 600;
    color: #2c3e50;
  }
  .popular-currencies {
    margin-top: 20px;
  }
  .currency-badge {
    cursor: pointer;
    margin: 5px;
  }
  .loading-spinner {
    display: none;
  }
  .loading-spinner.active {
    display: inline-block;
  }
</style>

<section class="page-title">
  <div class="row">
    <div class="col-md-12">
      <h1 class="page-title">
        <i class="fe fe-dollar-sign" aria-hidden="true"></i> 
        Currency Converter
      </h1>
    </div>
  </div>
</section>

<div class="row justify-content-center">
  <div class="col-md-10 col-lg-8">
    <div class="card converter-card">
      <div class="card-header">
        <h3 class="card-title">Convert Currency</h3>
      </div>
      <div class="card-body">
        
        <!-- From Currency -->
        <div class="row">
          <div class="col-md-8">
            <div class="form-group">
              <label>Amount</label>
              <input type="number" id="amount" class="form-control currency-input" value="1" min="0" step="0.01" placeholder="Enter amount">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>From</label>
              <select id="from_currency" class="form-control currency-select">
                <!-- Will be populated via JavaScript -->
              </select>
            </div>
          </div>
        </div>

        <!-- Swap Button -->
        <div class="row">
          <div class="col-md-12 text-center swap-button">
            <button type="button" id="swap_currencies" class="btn btn-primary">
              <i class="fe fe-repeat"></i> Swap Currencies
            </button>
          </div>
        </div>

        <!-- To Currency -->
        <div class="row">
          <div class="col-md-8">
            <div class="form-group">
              <label>Result</label>
              <input type="text" id="result" class="form-control currency-input" readonly placeholder="Converted amount">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>To</label>
              <select id="to_currency" class="form-control currency-select">
                <!-- Will be populated via JavaScript -->
              </select>
            </div>
          </div>
        </div>

        <!-- Convert Button -->
        <div class="row">
          <div class="col-md-12">
            <button type="button" id="convert_button" class="btn btn-success btn-block btn-lg">
              <span class="loading-spinner spinner-border spinner-border-sm"></span>
              Convert
            </button>
          </div>
        </div>

        <!-- Exchange Rate Info -->
        <div id="exchange_rate_info" class="exchange-rate-info" style="display: none;">
          <div class="row">
            <div class="col-md-12">
              <p class="mb-2">
                <strong>Exchange Rate:</strong> 
                <span id="rate_display">-</span>
              </p>
              <p class="mb-0 text-muted">
                <small>Last updated: <span id="last_updated">-</span></small>
              </p>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Popular Currencies Quick Select -->
    <div class="card popular-currencies">
      <div class="card-header">
        <h3 class="card-title">Popular Currencies</h3>
      </div>
      <div class="card-body">
        <div class="text-center">
          <span class="badge badge-secondary badge-lg currency-badge" data-currency="USD">USD 🇺🇸</span>
          <span class="badge badge-secondary badge-lg currency-badge" data-currency="EUR">EUR 🇪🇺</span>
          <span class="badge badge-secondary badge-lg currency-badge" data-currency="GBP">GBP 🇬🇧</span>
          <span class="badge badge-secondary badge-lg currency-badge" data-currency="JPY">JPY 🇯🇵</span>
          <span class="badge badge-secondary badge-lg currency-badge" data-currency="INR">INR 🇮🇳</span>
          <span class="badge badge-secondary badge-lg currency-badge" data-currency="AUD">AUD 🇦🇺</span>
          <span class="badge badge-secondary badge-lg currency-badge" data-currency="CAD">CAD 🇨🇦</span>
          <span class="badge badge-secondary badge-lg currency-badge" data-currency="CNY">CNY 🇨🇳</span>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
(function() {
  'use strict';

  let currencies = {};
  let currentRates = null;

  // Initialize on page load
  $(document).ready(function() {
    loadCurrencies();
    setupEventHandlers();
  });

  // Load available currencies
  function loadCurrencies() {
    $.ajax({
      url: '<?=cn("converter/get_currencies")?>',
      type: 'POST',
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          currencies = response.data;
          populateCurrencySelects();
        }
      },
      error: function() {
        showNotification('error', 'Failed to load currencies');
      }
    });
  }

  // Populate currency select dropdowns
  function populateCurrencySelects() {
    const fromSelect = $('#from_currency');
    const toSelect = $('#to_currency');
    
    fromSelect.empty();
    toSelect.empty();

    $.each(currencies, function(code, name) {
      fromSelect.append($('<option>', {
        value: code,
        text: code + ' - ' + name
      }));
      toSelect.append($('<option>', {
        value: code,
        text: code + ' - ' + name
      }));
    });

    // Set defaults
    fromSelect.val('USD');
    toSelect.val('EUR');
  }

  // Setup event handlers
  function setupEventHandlers() {
    // Convert button
    $('#convert_button').on('click', performConversion);

    // Swap currencies button
    $('#swap_currencies').on('click', swapCurrencies);

    // Quick select currency badges
    $('.currency-badge').on('click', function() {
      const currency = $(this).data('currency');
      $('#from_currency').val(currency);
    });

    // Convert on input change (debounced)
    let debounceTimer;
    $('#amount, #from_currency, #to_currency').on('change input', function() {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(performConversion, 500);
    });

    // Convert on Enter key
    $('#amount').on('keypress', function(e) {
      if (e.which === 13) {
        performConversion();
      }
    });
  }

  // Perform currency conversion
  function performConversion() {
    const amount = $('#amount').val();
    const from = $('#from_currency').val();
    const to = $('#to_currency').val();

    if (!amount || amount <= 0) {
      $('#result').val('');
      $('#exchange_rate_info').hide();
      return;
    }

    // Show loading
    $('.loading-spinner').addClass('active');
    $('#convert_button').prop('disabled', true);

    $.ajax({
      url: '<?=cn("converter/convert")?>',
      type: 'POST',
      data: {
        amount: amount,
        from: from,
        to: to
      },
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          const data = response.data;
          $('#result').val(formatNumber(data.result));
          
          // Update exchange rate info
          const rateText = '1 ' + from + ' = ' + formatNumber(data.rate) + ' ' + to;
          $('#rate_display').text(rateText);
          $('#last_updated').text(new Date().toLocaleString());
          $('#exchange_rate_info').slideDown();
        } else {
          showNotification('error', response.message || 'Conversion failed');
        }
      },
      error: function() {
        showNotification('error', 'Failed to convert currency. Please try again.');
      },
      complete: function() {
        $('.loading-spinner').removeClass('active');
        $('#convert_button').prop('disabled', false);
      }
    });
  }

  // Swap from and to currencies
  function swapCurrencies() {
    const from = $('#from_currency').val();
    const to = $('#to_currency').val();
    
    $('#from_currency').val(to);
    $('#to_currency').val(from);
    
    performConversion();
  }

  // Format number with decimals
  function formatNumber(num) {
    return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  // Show notification (using existing notification system if available)
  function showNotification(type, message) {
    // Try to use existing notification system
    if (typeof show_message === 'function') {
      show_message(message, type);
    } else if (typeof toastr !== 'undefined') {
      toastr[type](message);
    } else {
      alert(message);
    }
  }

})();
</script>
