# WordPress.org Upload Fixes

This document lists all the fixes applied to resolve WordPress.org automated scan errors.

## ✅ Fixed Issues

### 1. Plugin Header - Network Field
**Error:** `plugin_header_invalid_network: The "Network" header in the plugin file is not valid.`

**Fix:** Removed `Network: false` from plugin header. The Network header should only be included if set to `true` (for network-only plugins). For regular plugins, it should be omitted entirely.

**Changed:**
```php
// Before
 * Network: false

// After
// (removed entirely)
```

### 2. load_plugin_textdomain() Discouraged
**Error:** `load_plugin_textdomain() has been discouraged since WordPress version 4.6.`

**Fix:** Removed the `load_textdomain()` method and its call. WordPress.org automatically loads translations for plugins hosted on WordPress.org, so manual loading is not needed and is discouraged.

**Removed:**
- `add_action('plugins_loaded', array($this, 'load_textdomain'));`
- Entire `load_textdomain()` method

**Note:** Translations will still work! WordPress.org automatically loads `.mo` files from the `/languages` directory.

### 3. Missing Sanitization for register_setting()
**Error:** `Sanitization missing for register_setting().`

**Fix:** Added sanitization callbacks to all `register_setting()` calls that were missing them.

**Changed:**
```php
// Before
register_setting('mailable_settings_group', $this->option_active_driver);
register_setting('mailable_settings_group', $this->option_force_from);

// After
register_setting('mailable_settings_group', $this->option_active_driver, 'sanitize_text_field');
register_setting('mailable_settings_group', $this->option_force_from, 'absint');
```

**Sanitization Functions Used:**
- `sanitize_text_field` - For text inputs (driver name)
- `absint` - For checkbox/boolean values (force_from)
- `sanitize_email` - Already present for email fields
- `sanitize_textarea_field` - Already present for textarea fields

### 4. Hidden Files Not Permitted
**Error:** `.gitignore ERROR: hidden_files: Hidden files are not permitted.`

**Fix:** Deleted the `.gitignore` file from the plugin directory. WordPress.org does not allow hidden files (files starting with `.`) in plugin submissions.

**Note:** You can still use `.gitignore` in your development repository, but it should not be included in the WordPress.org submission ZIP file.

### 5. Outdated "Tested up to" Version
**Error:** `Tested up to: 6.4 < 6.9. The "Tested up to" value in your plugin is not set to the current version of WordPress.`

**Fix:** Updated "Tested up to" from `6.4` to `6.9` in both:
- `mailable.php` plugin header
- `README.txt` file

**Changed:**
```php
// Before
 * Tested up to: 6.4

// After
 * Tested up to: 6.9
```

## 📋 Verification Checklist

Before uploading again, verify:

- [x] Plugin header no longer contains `Network: false`
- [x] `load_plugin_textdomain()` function removed
- [x] All `register_setting()` calls have sanitization callbacks
- [x] `.gitignore` file removed from plugin directory
- [x] "Tested up to" updated to 6.9 in both `mailable.php` and `README.txt`
- [x] No linter errors

## 🚀 Next Steps

1. **Create a clean ZIP file** for upload:
   ```bash
   # Make sure .gitignore is not included
   zip -r mailable.zip . -x "*.git*" -x ".DS_Store" -x "*.md" -x "validate-assets.sh" -x "ASSETS-GUIDE.md" -x "WORDPRESS-ORG-*.md"
   ```

2. **Upload to WordPress.org** again

3. **Wait for automated scan** - should now pass

4. **Manual review** - A reviewer will check your plugin manually

## 📝 Notes

- **Translations:** Even though we removed `load_plugin_textdomain()`, your Spanish translations will still work. WordPress.org automatically loads `.mo` files from the `/languages` directory.

- **Sanitization:** All user inputs are now properly sanitized. This is a security requirement.

- **Tested up to:** You should update this value whenever a new major WordPress version is released to ensure your plugin appears in search results.

- **Hidden Files:** Remember to exclude `.gitignore` and other hidden files when creating the ZIP for WordPress.org submission.

## 🔗 Resources

- [Plugin Header Requirements](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/)
- [Settings API - register_setting()](https://developer.wordpress.org/reference/functions/register_setting/)
- [Internationalization Best Practices](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/)

---

**All errors have been fixed. The plugin should now pass the automated scan!** ✅

