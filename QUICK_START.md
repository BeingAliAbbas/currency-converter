# Quick Integration Example

This file shows exactly how to add the Currency Converter to your SMM Panel Script.

## Step 1: Copy Files

Copy the converter module from this repository to your smm-panel-script:

```bash
# Navigate to your smm-panel-script directory
cd /path/to/smm-panel-script

# Copy the converter module
cp -r /path/to/currency-converter/app/modules/converter app/modules/

# Create cache directory
mkdir -p storage/cache
chmod 755 storage/cache
```

## Step 2: Add Menu Link

### If your menu is in `app/modules/blocks/views/header.php`:

Find the navigation section and add this item:

```php
<!-- Example: Add after existing menu items -->
<li class="nav-item">
    <a class="nav-link" href="<?=cn('converter')?>">
        <i class="fe fe-dollar-sign"></i>
        <span class="nav-label">Currency Converter</span>
    </a>
</li>
```

### If using a different menu structure:

Look for where menu items are defined (usually in header/nav files) and add:

```php
<a href="<?=cn('converter')?>">
    <i class="fe fe-dollar-sign"></i>
    Currency Converter
</a>
```

## Step 3: Test

1. Navigate to: `http://yoursite.com/converter`
2. Enter an amount (e.g., 100)
3. Select currencies (e.g., USD to EUR)
4. Click Convert
5. See the result!

## Example Menu Integration Locations

### Admin Panel Sidebar

```php
<!-- In app/modules/blocks/views/header.php or similar -->
<ul class="nav nav-tabs">
    <li class="nav-item">
        <a href="<?=cn('statistics')?>" class="nav-link">
            <i class="fe fe-trending-up"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=cn('order')?>" class="nav-link">
            <i class="fe fe-shopping-cart"></i> Orders
        </a>
    </li>
    
    <!-- ADD THIS -->
    <li class="nav-item">
        <a href="<?=cn('converter')?>" class="nav-link">
            <i class="fe fe-dollar-sign"></i> Currency Converter
        </a>
    </li>
    <!-- END ADD -->
    
    <li class="nav-item">
        <a href="<?=cn('services')?>" class="nav-link">
            <i class="fe fe-list"></i> Services
        </a>
    </li>
</ul>
```

### Dropdown Menu

```php
<!-- Add to dropdown -->
<div class="dropdown-menu">
    <a class="dropdown-item" href="<?=cn('statistics')?>">Dashboard</a>
    <a class="dropdown-item" href="<?=cn('order')?>">Orders</a>
    
    <!-- ADD THIS -->
    <a class="dropdown-item" href="<?=cn('converter')?>">
        <i class="fe fe-dollar-sign"></i> Currency Converter
    </a>
    <!-- END ADD -->
    
    <a class="dropdown-item" href="<?=cn('services')?>">Services</a>
</div>
```

### Top Navigation

```php
<!-- Horizontal navigation -->
<nav class="navbar">
    <ul class="navbar-nav">
        <li><a href="<?=cn('statistics')?>">Home</a></li>
        <li><a href="<?=cn('order')?>">Orders</a></li>
        
        <!-- ADD THIS -->
        <li><a href="<?=cn('converter')?>">Converter</a></li>
        <!-- END ADD -->
        
        <li><a href="<?=cn('services')?>">Services</a></li>
    </ul>
</nav>
```

## Example: Adding Access Control

If you want only logged-in users to access:

Edit `app/modules/converter/controllers/converter.php`, add to `__construct()`:

```php
public function __construct(){
    parent::__construct();
    
    // Require login
    if (!is_logged_in()) {
        redirect(cn('login'));
    }
    
    $this->load->model(get_class($this).'_model', 'model');
}
```

For admin-only access:

```php
public function __construct(){
    parent::__construct();
    
    // Admin only
    if (!get_role('admin')) {
        show_error('Access Denied - Admin Only');
    }
    
    $this->load->model(get_class($this).'_model', 'model');
}
```

## Example: Using in Other Modules

Want to use the converter in your own code? Here's how:

### In a Controller

```php
class my_controller extends MX_Controller {
    
    public function calculate_price() {
        // Load converter model
        $this->load->model('converter/converter_model');
        
        // Convert 100 USD to EUR
        $price_in_eur = $this->converter_model->convert_currency(100, 'USD', 'EUR');
        
        echo "Price: " . $price_in_eur . " EUR";
    }
}
```

### In a Model

```php
class my_model extends MY_Model {
    
    public function get_price_in_currency($amount, $target_currency) {
        // Load converter model
        $CI =& get_instance();
        $CI->load->model('converter/converter_model');
        
        // Convert from default currency (USD) to target
        $converted = $CI->converter_model->convert_currency(
            $amount, 
            'USD', 
            $target_currency
        );
        
        return $converted;
    }
}
```

### In a View (AJAX)

```javascript
<script>
// Convert when button clicked
$('#my_convert_btn').click(function() {
    var amount = $('#my_amount').val();
    
    $.ajax({
        url: '<?=cn("converter/convert")?>',
        type: 'POST',
        data: {
            amount: amount,
            from: 'USD',
            to: 'EUR'
        },
        success: function(response) {
            if (response.status === 'success') {
                $('#result').text(response.data.result + ' EUR');
            }
        }
    });
});
</script>
```

## Verification Checklist

After integration, verify:

- [ ] Module files copied to `app/modules/converter/`
- [ ] Cache directory created: `storage/cache/`
- [ ] Cache directory has write permissions (755)
- [ ] Menu link added and visible
- [ ] Can access `/converter` URL
- [ ] Currencies load in dropdowns
- [ ] Conversion works
- [ ] Result displays correctly
- [ ] No JavaScript errors in browser console
- [ ] Cache files created in `storage/cache/currency_rates_*.json`

## Common Issues & Solutions

### "404 Not Found" when accessing /converter

**Solution**: Check CodeIgniter routes configuration. The HMVC should auto-route, but if using custom routes:

```php
// In config/routes.php
$route['converter'] = 'converter/index';
$route['converter/(:any)'] = 'converter/$1';
```

### Menu link not showing

**Solution**: Clear any caching systems:
- Browser cache
- CodeIgniter cache (delete `app/cache/*`)
- Server-side cache (opcache, APCu, etc.)

### Permission errors on cache

**Solution**:
```bash
sudo chown -R www-data:www-data storage/cache
sudo chmod -R 755 storage/cache
```

Replace `www-data` with your web server user (might be `apache`, `nginx`, etc.)

### Currencies not loading

**Solution**: Check that:
1. jQuery is loaded before the converter view
2. Internet connection is working
3. `cn()` function is defined (CodeIgniter helper)
4. No JavaScript errors in console

### Conversion returns 0 or false

**Solution**: 
1. Check API is accessible: `curl https://api.exchangerate-api.com/v4/latest/USD`
2. Verify PHP curl extension: `php -m | grep curl`
3. Check firewall allows outbound HTTPS
4. Review cache directory permissions

## Complete Integration Example

Here's a complete before/after example for a typical SMM panel header:

### Before (Original header.php):

```php
<ul class="nav nav-tabs">
    <li class="nav-item">
        <a href="<?=cn('statistics')?>" class="nav-link">Dashboard</a>
    </li>
    <li class="nav-item">
        <a href="<?=cn('order')?>" class="nav-link">Orders</a>
    </li>
    <li class="nav-item">
        <a href="<?=cn('services')?>" class="nav-link">Services</a>
    </li>
</ul>
```

### After (With Converter):

```php
<ul class="nav nav-tabs">
    <li class="nav-item">
        <a href="<?=cn('statistics')?>" class="nav-link">Dashboard</a>
    </li>
    <li class="nav-item">
        <a href="<?=cn('order')?>" class="nav-link">Orders</a>
    </li>
    <li class="nav-item">
        <a href="<?=cn('converter')?>" class="nav-link">
            <i class="fe fe-dollar-sign"></i> Converter
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=cn('services')?>" class="nav-link">Services</a>
    </li>
</ul>
```

That's it! Your currency converter is now fully integrated.

---

**Need help?** Check the [INTEGRATION_GUIDE.md](../INTEGRATION_GUIDE.md) for detailed documentation.
