# WordPress.org Submission Checklist

This document outlines what has been completed to make the Mailable plugin ready for WordPress.org submission.

## ✅ Completed Requirements

### 1. Plugin Header Information
- ✅ Plugin Name
- ✅ Plugin URI (set to WordPress.org)
- ✅ Description
- ✅ Version
- ✅ Author
- ✅ Author URI (WordPress.org profile)
- ✅ License (GPL v2 or later)
- ✅ License URI
- ✅ Text Domain (`mailable`)
- ✅ Domain Path (`/languages`)
- ✅ Requires at least: 6.0
- ✅ Tested up to: 6.4
- ✅ Requires PHP: 7.4
- ✅ Network: false

### 2. Internationalization (i18n)
- ✅ Text domain defined in plugin header
- ✅ Text domain loaded via `load_plugin_textdomain()`
- ✅ All user-facing strings wrapped with translation functions:
  - `__()` for strings that are returned
  - `esc_html__()` for strings that are echoed with HTML escaping
  - `esc_html_e()` for strings that are echoed directly
- ✅ Translation-ready strings in:
  - Main plugin file (`mailable.php`)
  - All template files
  - Error messages
  - Success messages
  - Form labels and descriptions

### 3. README.txt File
- ✅ Created in WordPress.org format
- ✅ Includes all required sections:
  - Plugin description
  - Installation instructions
  - Frequently Asked Questions
  - Screenshots section (placeholder)
  - Changelog
  - Upgrade notice
  - Developer information

### 4. Uninstall.php
- ✅ Created clean uninstall script
- ✅ Removes all plugin options
- ✅ Removes all driver-specific options
- ✅ Properly checks for `WP_UNINSTALL_PLUGIN` constant

### 5. LICENSE.txt
- ✅ GPL v2 license file included
- ✅ Full license text provided

### 6. Security
- ✅ All user inputs sanitized
- ✅ All outputs escaped
- ✅ Nonces used for all form submissions
- ✅ Capability checks (`manage_options`)
- ✅ Direct file access prevention (`ABSPATH` checks)

### 7. File Structure
- ✅ Assets folder created (`/assets`)
- ✅ Proper directory structure
- ✅ No hardcoded paths

## 📋 Next Steps (Manual Tasks)

### 1. Create Plugin Assets
You'll need to create the following image files for WordPress.org:

**Banner Image:**
- Location: `/assets/banner-772x250.png` or `.jpg`
- Size: 772x250 pixels
- Format: PNG or JPG
- Description: Main banner shown on plugin page

**Icon Image:**
- Location: `/assets/icon-256x256.png` or `.jpg`
- Size: 256x256 pixels
- Format: PNG or JPG
- Description: Plugin icon

**Screenshots:**
- Location: `/assets/screenshot-1.png`, `/assets/screenshot-2.png`, etc.
- Size: 1200x900 pixels (recommended)
- Format: PNG
- Description: Screenshots of the plugin in action
- Update `README.txt` with screenshot descriptions

### 2. Create POT File (Optional but Recommended)
Generate a `.pot` file for translators:
```bash
# Using WP-CLI (if available)
wp i18n make-pot . languages/mailable.pot --domain=mailable
```

Or use tools like:
- Poedit
- WPML String Translation
- Loco Translate

### 3. Test the Plugin
Before submission, thoroughly test:
- ✅ Plugin activation/deactivation
- ✅ Settings save/load
- ✅ All drivers work correctly
- ✅ Test email functionality
- ✅ Connection testing
- ✅ Uninstall process
- ✅ Multisite compatibility (if applicable)
- ✅ PHP 7.4+ compatibility
- ✅ WordPress 6.0+ compatibility

### 4. Code Review Checklist
- ✅ Follow WordPress Coding Standards
- ✅ No PHP errors or warnings
- ✅ No JavaScript console errors
- ✅ All functions properly documented
- ✅ No deprecated WordPress functions

### 5. WordPress.org Submission
1. Create account on [WordPress.org](https://wordpress.org)
2. Submit plugin via [Plugin Directory](https://wordpress.org/plugins/developers/add/)
3. Upload ZIP file (exclude `.git`, `node_modules`, etc.)
4. Wait for review (can take 1-2 weeks)
5. Respond to any feedback from reviewers

## 📝 Notes

- The plugin URI in the header is set to `https://wordpress.org/plugins/mailable/` - update this after your plugin is approved
- The Author URI is set to a WordPress.org profile - make sure you have a profile created
- All strings are translation-ready, but you may want to create initial translations for common languages
- The `.gitignore` file is already in place to exclude unnecessary files from version control

## 🔍 Pre-Submission Checklist

Before submitting, verify:
- [ ] All files are properly formatted
- [ ] No debug code or console.log statements
- [ ] No hardcoded credentials or API keys
- [ ] All external links use `https://`
- [ ] Plugin works on fresh WordPress installation
- [ ] No conflicts with other popular plugins
- [ ] Documentation is complete and accurate
- [ ] Screenshots are clear and helpful
- [ ] Banner and icon images are created
- [ ] ZIP file is clean (no hidden files, proper structure)

## 📚 Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Plugin Submission Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)
- [Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [Internationalization](https://developer.wordpress.org/plugins/internationalization/)

---

**Plugin Status:** ✅ Ready for WordPress.org submission (pending manual asset creation)

