# Currency Converter Integration Guide

## Overview
This guide provides step-by-step instructions for integrating the Currency Converter module into your SMM Panel Script project.

## Module Structure

The currency converter follows the HMVC (Hierarchical Model-View-Controller) pattern used throughout the SMM panel:

```
app/modules/converter/
├── controllers/
│   └── converter.php          # Main controller with API endpoints
├── models/
│   └── converter_model.php    # Business logic and API integration
└── views/
    └── index.php              # User interface
```

## Features

- **Real-time Exchange Rates**: Fetches live exchange rates from exchangerate-api.com
- **40+ Currencies**: Supports major world currencies
- **Caching**: Rates are cached for 1 hour to reduce API calls
- **AJAX-based**: Smooth user experience without page reloads
- **Responsive Design**: Mobile-friendly interface
- **Quick Selection**: Popular currencies quick-select badges
- **Swap Functionality**: Easily swap source and target currencies

## Integration Steps

### Step 1: Copy Module Files

Copy the entire `converter` module directory to your SMM panel script:

```bash
# From currency-converter repository
cp -r app/modules/converter /path/to/smm-panel-script/app/modules/
```

### Step 2: Create Cache Directory

The converter requires a cache directory for storing exchange rates:

```bash
mkdir -p /path/to/smm-panel-script/storage/cache
chmod 755 /path/to/smm-panel-script/storage/cache
```

Make sure the web server has write permissions to this directory.

### Step 3: Add Navigation Menu Item

Add the converter to your navigation menu. The location depends on your panel's structure:

#### Option A: Admin Menu (if using admin panel)
Edit your admin menu configuration file (typically `app/modules/blocks/views/header.php` or similar):

```php
<li class="nav-item">
    <a class="nav-link" href="<?=cn('converter')?>">
        <i class="fe fe-dollar-sign"></i>
        <span class="nav-label"><?=lang("Currency Converter")?></span>
    </a>
</li>
```

#### Option B: Main Navigation
Add to your main navigation:

```php
<li>
    <a href="<?=cn('converter')?>">
        <i class="fe fe-dollar-sign"></i>
        Currency Converter
    </a>
</li>
```

### Step 4: Configure Language Strings (Optional)

If your panel uses language files, add these strings:

```php
// In your language file
$lang['currency_converter'] = 'Currency Converter';
$lang['convert_currency'] = 'Convert Currency';
$lang['amount'] = 'Amount';
$lang['from'] = 'From';
$lang['to'] = 'To';
$lang['result'] = 'Result';
$lang['exchange_rate'] = 'Exchange Rate';
$lang['popular_currencies'] = 'Popular Currencies';
```

### Step 5: Set Up Routes (if needed)

If your panel uses custom routing, add the converter route:

```php
// In routes configuration
$route['converter'] = 'converter/index';
$route['converter/convert'] = 'converter/convert';
$route['converter/get_rates'] = 'converter/get_rates';
$route['converter/get_currencies'] = 'converter/get_currencies';
```

### Step 6: Configure Permissions (Optional)

If you want to restrict access to the converter:

```php
// In the converter controller's __construct method
if (!is_logged_in()) {
    redirect(cn('login'));
}

// Or for specific roles
if (!get_role('admin') && !get_role('user')) {
    show_error('Access Denied');
}
```

## API Endpoints

The converter module provides these AJAX endpoints:

### 1. Get Currencies
```
POST: converter/get_currencies
Response: List of supported currencies
```

### 2. Convert Currency
```
POST: converter/convert
Parameters:
  - amount: Amount to convert
  - from: Source currency code (e.g., USD)
  - to: Target currency code (e.g., EUR)
Response: Converted amount and exchange rate
```

### 3. Get Exchange Rates
```
POST: converter/get_rates
Parameters:
  - base: Base currency code (default: USD)
Response: All exchange rates for the base currency
```

## Customization

### Changing the API Provider

The module uses exchangerate-api.com (free tier). To use a different provider:

1. Edit `app/modules/converter/models/converter_model.php`
2. Modify the `fetch_exchange_rates()` method
3. Update the API URL and response parsing

Example for a different API:

```php
public function fetch_exchange_rates($base_currency = 'USD'){
    // Your custom API URL
    $api_url = "https://your-api.com/rates?base=" . $base_currency;
    
    // Add API key if required
    $api_key = "YOUR_API_KEY";
    
    // Fetch and parse accordingly
    // ...
}
```

### Styling Customization

The converter uses Bootstrap classes consistent with the SMM panel. To customize:

1. Edit CSS in `app/modules/converter/views/index.php`
2. Modify the color scheme, fonts, or layout
3. Update card structure to match your theme

### Adding More Currencies

To add more currencies:

1. Edit `get_supported_currencies()` in the model
2. Add currency codes and names to the array:

```php
'BTC' => 'Bitcoin',
'ETH' => 'Ethereum',
// etc.
```

### Cache Duration

To change how long rates are cached:

```php
// In converter_model.php
private $cache_duration = 7200; // 2 hours in seconds
```

## Security Considerations

1. **API Rate Limiting**: The module includes caching to prevent excessive API calls
2. **Input Validation**: All inputs are sanitized using CodeIgniter's input library
3. **CSRF Protection**: Use CodeIgniter's CSRF protection if enabled
4. **SSL/TLS**: Ensure API calls use HTTPS

## Troubleshooting

### Cache Directory Permissions
If you get errors about writing cache files:
```bash
chmod -R 755 storage/cache
chown -R www-data:www-data storage/cache  # or your web server user
```

### API Connection Issues
- Check firewall rules allow outbound HTTPS connections
- Verify curl extension is installed: `php -m | grep curl`
- Check API status at exchangerate-api.com

### Currency Not Converting
- Verify the currency code exists in the supported list
- Check browser console for JavaScript errors
- Ensure AJAX endpoints are accessible

### Styling Issues
- Clear browser cache
- Check that Bootstrap CSS is loaded
- Verify no CSS conflicts with existing styles

## Dependencies

- **PHP**: 5.6+ (7.0+ recommended)
- **CodeIgniter**: 3.x
- **PHP Extensions**: curl, json
- **Frontend**: jQuery, Bootstrap (already in SMM panel)

## Testing

To test the integration:

1. Navigate to `/converter` in your browser
2. Try converting between different currencies
3. Check the browser console for any errors
4. Verify cache files are created in `storage/cache/`
5. Test with different amounts and currency pairs

## Advanced Features (Future Enhancements)

Consider adding these features:

1. **Historical Rates**: Show exchange rate trends over time
2. **Favorite Pairs**: Save frequently used currency pairs
3. **Multiple Conversions**: Convert to multiple currencies at once
4. **Email Alerts**: Notify when rates hit certain thresholds
5. **API Integration**: Allow other modules to use conversion functions

## Code Examples

### Using Converter in Other Modules

```php
// Load the converter model
$this->load->model('converter/converter_model');

// Convert currency
$amount_in_eur = $this->converter_model->convert_currency(100, 'USD', 'EUR');

// Get all rates
$rates = $this->converter_model->fetch_exchange_rates('USD');
```

### JavaScript Usage

```javascript
// Convert currency via AJAX
$.ajax({
    url: base_url + 'converter/convert',
    type: 'POST',
    data: {
        amount: 100,
        from: 'USD',
        to: 'EUR'
    },
    success: function(response) {
        if (response.status === 'success') {
            console.log('Converted:', response.data.result);
        }
    }
});
```

## Support & Contribution

For issues or enhancements:
1. Check this documentation first
2. Review the code comments in each file
3. Test in a development environment before production

## License

This module is part of the SMM Panel Script project and follows the same license terms.

---

**Version**: 1.0.0  
**Last Updated**: 2025-11-03  
**Author**: Currency Converter Module
