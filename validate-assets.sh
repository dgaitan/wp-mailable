#!/bin/bash

# WordPress.org Plugin Assets Validation Script
# This script validates that your assets meet WordPress.org requirements

echo "🔍 Validating WordPress.org Plugin Assets..."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ASSETS_DIR="assets"
ERRORS=0
WARNINGS=0

# Check if assets directory exists
if [ ! -d "$ASSETS_DIR" ]; then
    echo -e "${RED}❌ Assets directory not found: $ASSETS_DIR${NC}"
    exit 1
fi

echo "📁 Checking assets in: $ASSETS_DIR"
echo ""

# Function to check image dimensions (macOS)
check_dimensions_macos() {
    local file=$1
    local expected_width=$2
    local expected_height=$3
    
    if command -v sips &> /dev/null; then
        width=$(sips -g pixelWidth "$file" 2>/dev/null | grep pixelWidth | awk '{print $2}')
        height=$(sips -g pixelHeight "$file" 2>/dev/null | grep pixelHeight | awk '{print $2}')
        
        if [ "$width" = "$expected_width" ] && [ "$height" = "$expected_height" ]; then
            echo -e "${GREEN}✓${NC} Dimensions correct: ${width}x${height}"
            return 0
        else
            echo -e "${RED}✗${NC} Dimensions incorrect: ${width}x${height} (expected: ${expected_width}x${expected_height})"
            return 1
        fi
    else
        echo -e "${YELLOW}⚠${NC}  sips not available, skipping dimension check"
        return 0
    fi
}

# Function to check file size
check_file_size() {
    local file=$1
    local max_size_mb=$2
    
    if [ -f "$file" ]; then
        size_bytes=$(stat -f%z "$file" 2>/dev/null || stat -c%s "$file" 2>/dev/null)
        size_mb=$(echo "scale=2; $size_bytes / 1024 / 1024" | bc)
        
        if (( $(echo "$size_mb <= $max_size_mb" | bc -l) )); then
            echo -e "${GREEN}✓${NC} File size: ${size_mb}MB (max: ${max_size_mb}MB)"
            if (( $(echo "$size_mb > 0.5" | bc -l) )); then
                echo -e "${YELLOW}  ⚠ Consider optimizing (recommended: < 500KB)${NC}"
                WARNINGS=$((WARNINGS + 1))
            fi
            return 0
        else
            echo -e "${RED}✗${NC} File size too large: ${size_mb}MB (max: ${max_size_mb}MB)"
            return 1
        fi
    fi
    return 1
}

# Check banner
echo "🖼️  Checking Banner..."
if [ -f "$ASSETS_DIR/banner-772x250.jpg" ] || [ -f "$ASSETS_DIR/banner-772x250.png" ]; then
    if [ -f "$ASSETS_DIR/banner-772x250.jpg" ]; then
        banner_file="$ASSETS_DIR/banner-772x250.jpg"
    else
        banner_file="$ASSETS_DIR/banner-772x250.png"
    fi
    
    echo "  File: $(basename $banner_file)"
    check_dimensions_macos "$banner_file" 772 250 || ERRORS=$((ERRORS + 1))
    check_file_size "$banner_file" 4 || ERRORS=$((ERRORS + 1))
    
    # Check for retina version
    if [ -f "$ASSETS_DIR/banner-1544x500.jpg" ] || [ -f "$ASSETS_DIR/banner-1544x500.png" ]; then
        echo -e "${GREEN}✓${NC} Retina banner found"
    else
        echo -e "${YELLOW}⚠${NC}  Retina banner (banner-1544x500.jpg) not found (optional but recommended)"
        WARNINGS=$((WARNINGS + 1))
    fi
else
    echo -e "${RED}✗${NC} Banner file not found (banner-772x250.jpg or .png)"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# Check icon
echo "🎨 Checking Icon..."
if [ -f "$ASSETS_DIR/icon-128x128.png" ] || [ -f "$ASSETS_DIR/icon-128x128.jpg" ] || [ -f "$ASSETS_DIR/icon-128x128.gif" ]; then
    if [ -f "$ASSETS_DIR/icon-128x128.png" ]; then
        icon_file="$ASSETS_DIR/icon-128x128.png"
    elif [ -f "$ASSETS_DIR/icon-128x128.jpg" ]; then
        icon_file="$ASSETS_DIR/icon-128x128.jpg"
    else
        icon_file="$ASSETS_DIR/icon-128x128.gif"
    fi
    
    echo "  File: $(basename $icon_file)"
    check_dimensions_macos "$icon_file" 128 128 || ERRORS=$((ERRORS + 1))
    check_file_size "$icon_file" 1 || ERRORS=$((ERRORS + 1))
    
    # Check for retina version
    if [ -f "$ASSETS_DIR/icon-256x256.png" ] || [ -f "$ASSETS_DIR/icon-256x256.jpg" ]; then
        echo -e "${GREEN}✓${NC} Retina icon found"
    else
        echo -e "${YELLOW}⚠${NC}  Retina icon (icon-256x256.png) not found (optional but recommended)"
        WARNINGS=$((WARNINGS + 1))
    fi
else
    echo -e "${RED}✗${NC} Icon file not found (icon-128x128.png, .jpg, or .gif)"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# Check screenshots
echo "📸 Checking Screenshots..."
screenshot_count=0
screenshot_gaps=0
last_num=0

for file in "$ASSETS_DIR"/screenshot-*.{png,jpg,jpeg}; do
    if [ -f "$file" ]; then
        screenshot_count=$((screenshot_count + 1))
        filename=$(basename "$file")
        
        # Extract number from filename
        num=$(echo "$filename" | grep -oE '[0-9]+' | head -1)
        
        if [ -n "$num" ]; then
            if [ "$num" != "$((last_num + 1))" ] && [ "$last_num" -gt 0 ]; then
                screenshot_gaps=$((screenshot_gaps + 1))
                echo -e "${YELLOW}⚠${NC}  Gap in numbering: found screenshot-$num after screenshot-$last_num"
            fi
            last_num=$num
            
            echo "  Found: $filename"
            check_file_size "$file" 10 || ERRORS=$((ERRORS + 1))
        fi
    fi
done

if [ $screenshot_count -eq 0 ]; then
    echo -e "${YELLOW}⚠${NC}  No screenshots found (screenshot-1.png, screenshot-2.png, etc.)"
    WARNINGS=$((WARNINGS + 1))
else
    echo -e "${GREEN}✓${NC} Found $screenshot_count screenshot(s)"
    if [ $screenshot_gaps -gt 0 ]; then
        echo -e "${YELLOW}⚠${NC}  Screenshots should be numbered sequentially (1, 2, 3...) with no gaps"
        WARNINGS=$((WARNINGS + 1))
    fi
fi
echo ""

# Check README.txt for screenshot descriptions
echo "📝 Checking README.txt..."
if [ -f "README.txt" ]; then
    if grep -q "== Screenshots ==" README.txt; then
        echo -e "${GREEN}✓${NC} Screenshots section found in README.txt"
        
        # Count screenshot descriptions
        desc_count=$(grep -A 100 "== Screenshots ==" README.txt | grep -E "^[0-9]+\." | wc -l | tr -d ' ')
        
        if [ "$desc_count" -ne "$screenshot_count" ] && [ "$screenshot_count" -gt 0 ]; then
            echo -e "${YELLOW}⚠${NC}  Screenshot count mismatch: $screenshot_count files but $desc_count descriptions in README.txt"
            WARNINGS=$((WARNINGS + 1))
        fi
    else
        if [ $screenshot_count -gt 0 ]; then
            echo -e "${YELLOW}⚠${NC}  Screenshots found but no '== Screenshots ==' section in README.txt"
            WARNINGS=$((WARNINGS + 1))
        fi
    fi
else
    echo -e "${YELLOW}⚠${NC}  README.txt not found"
    WARNINGS=$((WARNINGS + 1))
fi
echo ""

# Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 Validation Summary"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✅ All checks passed! Your assets are ready for WordPress.org.${NC}"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠️  Validation passed with $WARNINGS warning(s).${NC}"
    echo "   Review warnings above and fix if needed."
    exit 0
else
    echo -e "${RED}❌ Validation failed with $ERRORS error(s) and $WARNINGS warning(s).${NC}"
    echo "   Please fix the errors above before submitting to WordPress.org."
    exit 1
fi

