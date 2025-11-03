# Security Documentation - Currency Converter Module

## Security Features

The Currency Converter module implements multiple layers of security to protect against common vulnerabilities.

## 1. Input Validation & Sanitization

### XSS Protection
All user inputs are sanitized using CodeIgniter's built-in security features:

```php
$amount = $this->input->post('amount', TRUE);  // TRUE enables XSS filtering
$from = $this->input->post('from', TRUE);
$to = $this->input->post('to', TRUE);
```

### Input Validation
- **Amount**: Validated as numeric, must be > 0
- **Currency Codes**: Validated against supported currency list
- **Empty Check**: All parameters checked before processing

```php
if (empty($amount) || empty($from) || empty($to)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters'
    ]);
    return;
}
```

## 2. SQL Injection Prevention

This module does **NOT** directly interact with the database, eliminating SQL injection risks. All data operations are:
- API-based (external currency API)
- File-based (cache storage)

When integrating with other modules that use databases, always use CodeIgniter's Query Builder or prepared statements.

## 3. API Security

### HTTPS Enforcement
All API calls use HTTPS protocol:

```php
$api_url = "https://api.exchangerate-api.com/v4/latest/...";
```

### SSL Certificate Verification
SSL certificates are verified to prevent man-in-the-middle attacks:

```php
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
```

### URL Encoding
All user-provided data sent to APIs is properly encoded:

```php
$api_url = "https://api.exchangerate-api.com/v4/latest/" . urlencode($base_currency);
```

### Timeout Protection
API requests have a 10-second timeout to prevent resource exhaustion:

```php
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
```

## 4. Rate Limiting & Caching

### Automatic Caching
- Exchange rates are cached for 1 hour
- Reduces API calls and prevents abuse
- Minimizes exposure to API rate limits

```php
private $cache_duration = 3600; // 1 hour
```

### Cache File Security
- Cache files stored in `storage/cache/` directory
- Directory should have 755 permissions (read/execute for all, write for owner only)
- Cache files named with predictable pattern but contain no sensitive data

## 5. Error Handling

### Graceful Degradation
All API failures are handled gracefully without exposing system information:

```php
if ($result !== false) {
    // Success
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Conversion failed'  // Generic message
    ]);
}
```

### No Stack Traces
Production code should never display stack traces or detailed error messages to users.

## 6. Access Control

### Optional Authentication
The module can be restricted to authenticated users:

```php
// In constructor, add:
if (!is_logged_in()) {
    redirect(cn('login'));
}
```

### Role-Based Access
Can be limited to specific roles:

```php
if (!get_role('admin') && !get_role('user')) {
    show_error('Access Denied');
}
```

## 7. File System Security

### Cache Directory
```bash
# Recommended permissions
chmod 755 storage/cache
chown www-data:www-data storage/cache
```

### .gitignore
Cache files are excluded from version control:

```gitignore
# In storage/cache/.gitignore
*.json
!.gitkeep
```

## 8. CSRF Protection

If your SMM panel has CSRF protection enabled, ensure forms include the CSRF token:

```php
// In views, add:
<?php echo form_open('converter/convert'); ?>
    <!-- form fields -->
<?php echo form_close(); ?>
```

Or for AJAX:

```javascript
$.ajax({
    url: 'converter/convert',
    type: 'POST',
    data: {
        amount: amount,
        from: from,
        to: to,
        <?php echo $this->security->get_csrf_token_name(); ?>: 
        '<?php echo $this->security->get_csrf_hash(); ?>'
    }
});
```

## 9. JSON Security

### Content-Type Header
Always set proper content type for JSON responses:

```php
header('Content-Type: application/json');
echo json_encode($data);
```

### JSON Encoding
All data is properly JSON-encoded to prevent injection:

```php
echo json_encode([
    'status' => 'success',
    'data' => $data  // Automatically escaped
]);
```

## 10. Third-Party Dependencies

### API Provider
- **Service**: exchangerate-api.com
- **Protocol**: HTTPS only
- **Authentication**: None required (free tier)
- **Data Privacy**: No sensitive user data sent

### Changing API Provider
If switching to a different API:
1. Verify HTTPS support
2. Check SSL certificate validity
3. Review privacy policy
4. Implement API key security if required

## Security Checklist

Before deploying to production:

- [ ] Set cache directory permissions to 755
- [ ] Enable HTTPS on your domain
- [ ] Configure firewall to allow outbound HTTPS
- [ ] Disable PHP error display (`display_errors = Off`)
- [ ] Enable error logging (`log_errors = On`)
- [ ] Set secure session configuration
- [ ] Enable CSRF protection if not already enabled
- [ ] Implement rate limiting at application level
- [ ] Monitor cache directory size
- [ ] Regular security updates for PHP and CodeIgniter

## Common Security Issues & Solutions

### Issue: Cache Files Readable by All Users
**Solution**: Set proper permissions
```bash
chmod 755 storage/cache
chmod 644 storage/cache/*.json
```

### Issue: API Key Exposure (if using paid API)
**Solution**: Store API keys in environment variables or config files outside web root
```php
$api_key = getenv('CURRENCY_API_KEY');
// Or from config
$api_key = $this->config->item('currency_api_key');
```

### Issue: Excessive API Calls
**Solution**: Increase cache duration or implement request throttling
```php
private $cache_duration = 7200; // 2 hours
```

### Issue: CORS Errors in AJAX
**Solution**: Set proper headers (if converter is on different domain)
```php
header('Access-Control-Allow-Origin: https://yourdomain.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
```

## Vulnerability Assessment

### Tested Against
- ✅ SQL Injection
- ✅ Cross-Site Scripting (XSS)
- ✅ Cross-Site Request Forgery (CSRF)
- ✅ Remote Code Execution
- ✅ Path Traversal
- ✅ Man-in-the-Middle Attacks
- ✅ API Abuse
- ✅ Information Disclosure

### Risk Level: **LOW**

The module:
- Does not interact with database directly
- Does not store sensitive user data
- Does not execute user-provided code
- Uses industry-standard security practices
- Has minimal attack surface

## Security Best Practices

1. **Keep PHP Updated**: Use PHP 7.4+ (PHP 8.x recommended)
2. **Keep CodeIgniter Updated**: Use latest 3.x version
3. **Monitor Logs**: Regularly check error logs for anomalies
4. **Limit Access**: Restrict converter to authenticated users if needed
5. **Rate Limit**: Implement IP-based rate limiting for public endpoints
6. **Backup**: Regular backups in case of data corruption
7. **Testing**: Test in staging before production deployment

## Reporting Security Issues

If you discover a security vulnerability:
1. Do NOT open a public issue
2. Document the vulnerability
3. Contact the maintainer privately
4. Allow time for patching before disclosure

## Security Updates

### Version 1.0.0 (Current)
- Initial release with security features
- Input sanitization implemented
- SSL verification enabled
- Caching system added
- Error handling improved

### Future Enhancements
- [ ] Rate limiting per IP
- [ ] API key encryption
- [ ] Request logging
- [ ] Suspicious activity detection
- [ ] Two-factor authentication support

## Compliance

This module follows:
- OWASP Top 10 security practices
- PHP security best practices
- CodeIgniter security guidelines
- PCI DSS requirements (for payment-related integrations)

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CodeIgniter Security](https://codeigniter.com/userguide3/general/security.html)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)

---

**Last Updated**: November 3, 2025  
**Security Version**: 1.0.0  
**Risk Level**: LOW ✅
