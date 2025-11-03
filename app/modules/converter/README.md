# Currency Converter Module

A professional, real-time currency converter module for SMM Panel applications built with CodeIgniter HMVC.

## Features

✅ **Real-time Exchange Rates** - Fetches live rates from reliable API  
✅ **40+ Major Currencies** - Support for all major world currencies  
✅ **Smart Caching** - Reduces API calls with 1-hour cache  
✅ **AJAX-Powered** - No page reloads, smooth user experience  
✅ **Mobile Responsive** - Works perfectly on all devices  
✅ **Quick Selection** - Popular currency badges for fast access  
✅ **Swap Functionality** - Easily swap between currencies  
✅ **Clean UI** - Matches SMM panel design aesthetics  

## Quick Start

### Installation

1. Copy the `converter` module to your SMM panel:
   ```bash
   cp -r app/modules/converter /path/to/your/smm-panel/app/modules/
   ```

2. Create cache directory:
   ```bash
   mkdir -p storage/cache && chmod 755 storage/cache
   ```

3. Add to navigation menu (example):
   ```php
   <li>
       <a href="<?=cn('converter')?>">
           <i class="fe fe-dollar-sign"></i>
           Currency Converter
       </a>
   </li>
   ```

4. Access at: `yoursite.com/converter`

## Supported Currencies

USD, EUR, GBP, JPY, AUD, CAD, CHF, CNY, INR, MXN, BRL, ZAR, RUB, KRW, SGD, HKD, NOK, SEK, DKK, PLN, THB, IDR, HUF, CZK, ILS, CLP, PHP, AED, SAR, MYR, TRY, PKR, NGN, EGP, VND, BDT, ARS, TWD, NZD, and more...

## API Reference

### Convert Currency
```php
POST: converter/convert
Parameters:
  - amount: numeric (amount to convert)
  - from: string (source currency code, e.g., 'USD')
  - to: string (target currency code, e.g., 'EUR')

Response:
{
    "status": "success",
    "data": {
        "amount": 100,
        "from": "USD",
        "to": "EUR",
        "result": 92.50,
        "rate": 0.925
    }
}
```

### Get Supported Currencies
```php
POST: converter/get_currencies

Response:
{
    "status": "success",
    "data": {
        "USD": "US Dollar",
        "EUR": "Euro",
        ...
    }
}
```

### Get Exchange Rates
```php
POST: converter/get_rates
Parameters:
  - base: string (base currency, default: 'USD')

Response:
{
    "status": "success",
    "data": {
        "base": "USD",
        "rates": {
            "EUR": 0.925,
            "GBP": 0.815,
            ...
        }
    }
}
```

## Usage in Code

### In Controllers/Models

```php
// Load the model
$this->load->model('converter/converter_model');

// Convert currency
$euros = $this->converter_model->convert_currency(100, 'USD', 'EUR');
echo "100 USD = " . $euros . " EUR";

// Get exchange rates
$rates = $this->converter_model->fetch_exchange_rates('USD');
print_r($rates['rates']);

// Get supported currencies
$currencies = $this->converter_model->get_supported_currencies();
```

### In JavaScript/AJAX

```javascript
// Convert currency
$.post('converter/convert', {
    amount: 100,
    from: 'USD',
    to: 'EUR'
}, function(response) {
    if (response.status === 'success') {
        console.log('Result:', response.data.result);
    }
});
```

## File Structure

```
app/modules/converter/
│
├── controllers/
│   └── converter.php           # Main controller with AJAX endpoints
│
├── models/
│   └── converter_model.php     # API integration & business logic
│
└── views/
    └── index.php               # User interface with JavaScript
```

## Configuration

### Change API Provider

Edit `converter_model.php`:

```php
public function fetch_exchange_rates($base_currency = 'USD'){
    $api_url = "https://your-api-provider.com/rates?base=" . $base_currency;
    // Update parsing logic as needed
}
```

### Modify Cache Duration

Edit `converter_model.php`:

```php
private $cache_duration = 3600; // Change to desired seconds
```

### Add Custom Currencies

Edit `get_supported_currencies()` in `converter_model.php`:

```php
return [
    'USD' => 'US Dollar',
    'BTC' => 'Bitcoin',  // Add custom entry
    // ...
];
```

## Requirements

- PHP 5.6+ (7.0+ recommended)
- CodeIgniter 3.x
- PHP curl extension
- PHP json extension
- jQuery (included in SMM panel)
- Bootstrap (included in SMM panel)

## Security Features

- Input sanitization using CodeIgniter security
- HTTPS API communication
- Rate-limited API calls via caching
- SQL injection protection
- XSS protection

## Troubleshooting

**Issue**: Permission denied on cache directory
```bash
# Solution:
chmod -R 755 storage/cache
chown -R www-data:www-data storage/cache
```

**Issue**: API not responding
- Check internet connectivity
- Verify firewall allows outbound HTTPS
- Confirm curl extension: `php -m | grep curl`

**Issue**: JavaScript errors
- Check jQuery is loaded
- Verify base_url is configured
- Clear browser cache

## Performance

- **Caching**: Rates cached for 1 hour
- **API Calls**: Minimized via smart caching
- **Response Time**: < 100ms (cached), < 2s (API fetch)
- **Browser**: AJAX prevents page reloads

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Screenshots

The converter features:
- Clean input fields for amount entry
- Dropdown selects for currency selection
- Large, prominent convert button
- Exchange rate display with timestamp
- Popular currency quick-select badges
- Swap button for reversing conversion

## Changelog

### Version 1.0.0 (2025-11-03)
- Initial release
- 40+ supported currencies
- Real-time API integration
- Caching system
- AJAX functionality
- Responsive design

## Future Enhancements

- [ ] Historical rate charts
- [ ] Favorite currency pairs
- [ ] Multi-currency conversion
- [ ] Rate change notifications
- [ ] Offline mode with last known rates
- [ ] Cryptocurrency support
- [ ] Import/Export conversions

## Credits

- Exchange rates provided by [exchangerate-api.com](https://www.exchangerate-api.com/)
- Built for SMM Panel Script
- Uses CodeIgniter HMVC framework

## License

This module is part of the SMM Panel Script project.

## Support

For detailed integration instructions, see [INTEGRATION_GUIDE.md](../INTEGRATION_GUIDE.md)

---

**Module Version**: 1.0.0  
**Compatible With**: CodeIgniter 3.x HMVC  
**Last Updated**: November 3, 2025
