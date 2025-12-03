# WordPress.org Plugin Assets Guide

This guide explains how to ensure your plugin assets (banner, icon, and screenshots) are correctly used on WordPress.org.

## 📁 Important: Asset Location in SVN

**CRITICAL:** When you upload to WordPress.org SVN, the `assets` folder must be at the **root level** of your SVN repository, **NOT** inside the `trunk` directory.

```
your-plugin-svn-repo/
├── assets/              ← Assets folder here (at root level)
│   ├── banner-772x250.jpg
│   ├── banner-1544x500.jpg (optional, for retina)
│   ├── icon-128x128.png
│   ├── icon-256x256.png (optional, for retina)
│   ├── screenshot-1.png
│   ├── screenshot-2.png
│   └── ...
├── trunk/               ← Your plugin code here
│   ├── mailable.php
│   ├── includes/
│   └── ...
├── tags/
│   └── 2.0.0/
└── ...
```

**For local development**, you can keep assets in `/wp-content/plugins/mailable/assets/`, but when committing to SVN, you'll need to move them to the repository root.

## 🖼️ Asset Requirements

### 1. Banner Image

**Required Files:**
- `banner-772x250.jpg` or `banner-772x250.png`
  - **Size:** Exactly 772 × 250 pixels
  - **Format:** JPG or PNG
  - **Max Size:** 4MB (but keep it under 500KB for performance)
  - **Usage:** Displayed at the top of your plugin page on WordPress.org

**Optional (Recommended):**
- `banner-1544x500.jpg` or `banner-1544x500.png`
  - **Size:** Exactly 1544 × 500 pixels (2x for retina displays)
  - **Format:** JPG or PNG
  - **Max Size:** 4MB

**Best Practices:**
- Use high-quality images
- Include your plugin name/logo
- Keep text readable at small sizes
- Use colors that match your brand
- Avoid too much text (banner is relatively small)

### 2. Icon Image

**Required Files:**
- `icon-128x128.png` or `icon-128x128.jpg`
  - **Size:** Exactly 128 × 128 pixels
  - **Format:** PNG, JPG, or GIF
  - **Max Size:** 1MB (but keep it under 100KB)
  - **Usage:** Plugin icon in search results and plugin directory

**Optional (Recommended):**
- `icon-256x256.png` or `icon-256x256.jpg`
  - **Size:** Exactly 256 × 256 pixels (2x for retina displays)
  - **Format:** PNG, JPG, or GIF
  - **Max Size:** 1MB

**Best Practices:**
- Use a square icon (will be displayed as square)
- Make it recognizable at small sizes
- Use simple, clear designs
- Avoid text (it won't be readable at icon size)
- Use transparent background (PNG) if possible

### 3. Screenshots

**Required Files:**
- `screenshot-1.png`, `screenshot-2.png`, etc.
  - **Size:** Recommended 1200 × 900 pixels (or maintain 4:3 aspect ratio)
  - **Format:** PNG or JPG
  - **Max Size:** 10MB per file (but keep under 500KB each)
  - **Usage:** Displayed in a gallery on your plugin page

**Naming Convention:**
- Must be sequential: `screenshot-1.png`, `screenshot-2.png`, `screenshot-3.png`, etc.
- No gaps in numbering (don't use screenshot-1, screenshot-3, screenshot-5)

**Screenshot Descriptions:**
Each screenshot needs a corresponding description in your `README.txt` file:

```txt
== Screenshots ==

1. Settings page with provider selection
2. SendGrid configuration
3. Mailpit configuration for development
4. Test email interface
```

**Best Practices:**
- Show actual plugin functionality
- Use clear, high-quality images
- Add annotations/arrows if helpful
- Show the most important features first
- Keep file sizes reasonable

## 🌍 Localized Assets (Optional)

You can provide localized versions of banners and screenshots:

**Banners:**
- `banner-772x250-es_ES.png` (Spanish)
- `banner-772x250-fr_FR.png` (French)
- etc.

**Screenshots:**
- `screenshot-1-es.png` (Spanish)
- `screenshot-1-fr.png` (French)
- etc.

WordPress.org will automatically use the appropriate localized version based on the user's language.

## ✅ Validation Checklist

Before submitting to WordPress.org, verify:

### Banner
- [ ] File named exactly `banner-772x250.jpg` or `banner-772x250.png`
- [ ] Image is exactly 772 × 250 pixels
- [ ] File size is under 4MB (preferably under 500KB)
- [ ] Image is clear and readable
- [ ] (Optional) Retina version `banner-1544x500.jpg` created

### Icon
- [ ] File named exactly `icon-128x128.png` or `icon-128x128.jpg`
- [ ] Image is exactly 128 × 128 pixels
- [ ] File size is under 1MB (preferably under 100KB)
- [ ] Icon is recognizable at small sizes
- [ ] (Optional) Retina version `icon-256x256.png` created

### Screenshots
- [ ] Files named sequentially: `screenshot-1.png`, `screenshot-2.png`, etc.
- [ ] Each screenshot has a description in `README.txt`
- [ ] Screenshots are in order (1, 2, 3, no gaps)
- [ ] Each file is under 10MB (preferably under 500KB)
- [ ] Screenshots show actual plugin functionality
- [ ] Images are clear and high-quality

### File Structure
- [ ] Assets folder will be at SVN root (not in trunk)
- [ ] All files use correct naming conventions
- [ ] No extra files in assets folder

## 🔧 How to Verify Your Assets

### 1. Check File Dimensions

**On macOS:**
```bash
# Check image dimensions
sips -g pixelWidth -g pixelHeight assets/banner-772x250.jpg
sips -g pixelWidth -g pixelHeight assets/icon-128x128.png
```

**On Linux:**
```bash
# Check image dimensions
identify assets/banner-772x250.jpg
identify assets/icon-128x128.png
```

**Online Tools:**
- Upload to [TinyPNG](https://tinypng.com/) to check dimensions and optimize
- Use [ImageMagick](https://imagemagick.org/) command line tools

### 2. Check File Sizes

```bash
# Check file sizes
ls -lh assets/
```

### 3. Validate README.txt Screenshots Section

Make sure your `README.txt` has a `== Screenshots ==` section with descriptions matching your screenshot files:

```txt
== Screenshots ==

1. Description of screenshot-1.png
2. Description of screenshot-2.png
3. Description of screenshot-3.png
```

## 📤 Uploading to WordPress.org SVN

### Step 1: Prepare Your Assets

1. Create/optimize all asset files
2. Verify dimensions and file sizes
3. Name files correctly

### Step 2: SVN Structure

When you commit to WordPress.org SVN:

```bash
# Your SVN repository structure should be:
svn-repo/
├── assets/              # ← Create this at root
│   ├── banner-772x250.jpg
│   ├── icon-128x128.png
│   ├── screenshot-1.png
│   └── screenshot-2.png
├── trunk/
│   └── [your plugin files]
└── tags/
    └── 2.0.0/
```

### Step 3: Set MIME Types (Important!)

WordPress.org requires correct MIME types. Set them in SVN:

```bash
# Navigate to your SVN assets folder
cd assets

# Set MIME types
svn propset svn:mime-type image/jpeg *.jpg
svn propset svn:mime-type image/png *.png
svn propset svn:mime-type image/gif *.gif

# Commit the property changes
svn commit -m "Set MIME types for assets"
```

**Or configure globally in `~/.subversion/config`:**
```
[miscellany]
enable-auto-props = yes

[auto-props]
*.png = svn:mime-type=image/png
*.jpg = svn:mime-type=image/jpeg
*.jpeg = svn:mime-type=image/jpeg
*.gif = svn:mime-type=image/gif
```

### Step 4: Commit Assets

```bash
# Add assets folder to SVN
svn add assets/

# Commit
svn commit -m "Add plugin assets (banner, icon, screenshots)"
```

## 🧪 Testing After Upload

1. **Wait for CDN Propagation**
   - WordPress.org uses a CDN that can take 5-30 minutes to update
   - Sometimes up to a few hours

2. **Check Your Plugin Page**
   - Visit: `https://wordpress.org/plugins/mailable/`
   - Verify banner displays correctly
   - Check icon appears in search results
   - View screenshots gallery

3. **Test Different Devices**
   - Check on desktop (standard resolution)
   - Check on retina displays (if you provided 2x versions)
   - Check on mobile devices

## 🐛 Troubleshooting

### Assets Not Showing

1. **Check File Names**
   - Must be exact: `banner-772x250.jpg` (not `banner.jpg` or `banner-772x250.png` if you uploaded JPG)

2. **Check File Dimensions**
   - Banner must be exactly 772 × 250
   - Icon must be exactly 128 × 128

3. **Check MIME Types**
   - Ensure MIME types are set correctly in SVN
   - Re-commit with correct MIME types if needed

4. **Wait for CDN**
   - Assets can take time to propagate
   - Clear browser cache and wait 30 minutes

5. **Check SVN Structure**
   - Assets folder must be at repository root
   - Not inside `trunk/` or `tags/`

### Images Look Blurry

- Provide retina versions (2x size)
- Use PNG format for better quality
- Ensure source images are high resolution

### Screenshots Not Appearing

- Check `README.txt` has `== Screenshots ==` section
- Verify screenshot descriptions match file count
- Ensure files are named sequentially (no gaps)

## 📝 Quick Reference

| Asset Type | File Name | Dimensions | Max Size | Format |
|------------|-----------|------------|----------|--------|
| Banner | `banner-772x250.jpg` | 772 × 250 | 4MB | JPG/PNG |
| Banner (Retina) | `banner-1544x500.jpg` | 1544 × 500 | 4MB | JPG/PNG |
| Icon | `icon-128x128.png` | 128 × 128 | 1MB | PNG/JPG/GIF |
| Icon (Retina) | `icon-256x256.png` | 256 × 256 | 1MB | PNG/JPG/GIF |
| Screenshot | `screenshot-1.png` | 1200 × 900 (recommended) | 10MB | PNG/JPG |

## 🔗 Resources

- [WordPress.org Plugin Assets Documentation](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/)
- [Plugin Directory Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)
- [SVN Guide](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/)

---

**Remember:** The most common mistake is placing the `assets` folder inside `trunk/`. It must be at the SVN repository root level!

