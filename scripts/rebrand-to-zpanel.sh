#!/bin/bash
set -euo pipefail

# Zpanel Rebranding Script
# Systematically rebrand Coolify to Zpanel across the codebase
# Preserves attribution and licensing

echo "🎨 Zpanel Rebranding Script"
echo "================================"
echo ""

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
cd "$PROJECT_ROOT"

# Counter for tracking changes
TOTAL_FILES=0
TOTAL_CHANGES=0

# Files to preserve (don't rebrand)
PRESERVE_FILES=(
    "CHANGELOG.md"
    "LICENSE"
    ".git"
    "vendor"
    "node_modules"
)

# Function to check if file should be preserved
should_preserve() {
    local file=$1
    for preserve in "${PRESERVE_FILES[@]}"; do
        if [[ "$file" == *"$preserve"* ]]; then
            return 0
        fi
    done
    return 1
}

# Function to rebrand a single file
rebrand_file() {
    local file=$1
    
    if should_preserve "$file"; then
        return
    fi
    
    # Check if file contains "Coolify"
    if grep -q "Coolify\|coolify\|COOLIFY\|coollabs" "$file" 2>/dev/null; then
        echo "  📝 Rebranding: $file"
        
        # Create backup
        cp "$file" "${file}.bak"
        
        # Perform replacements (preserve "Based on Coolify" and "Forked from Coolify")
        sed -i.tmp \
            -e '/Based on Coolify/!s/Coolify/Zpanel/g' \
            -e '/Forked from Coolify/!s/Coolify/Zpanel/g' \
            -e '/Original.*Coolify/!s/Coolify/Zpanel/g' \
            -e 's/coolify\([^.io]\)/zpanel\1/g' \
            -e 's/COOLIFY/ZPANEL/g' \
            -e 's/coollabsio/freqkflag/g' \
            -e 's/coollabs\.io/zpanel.io/g' \
            -e 's/@coolifyio/@zpanel_io/g' \
            -e 's/coolify\.io/zpanel.io/g' \
            "$file"
        
        # Remove temp file
        rm "${file}.tmp" 2>/dev/null || true
        
        ((TOTAL_FILES++))
    fi
}

# Rebrand Phase 1: User-facing UI files
echo "Phase 1: Rebranding UI Components..."
echo "-----------------------------------"

# Layouts
for file in resources/views/layouts/*.blade.php; do
    [ -f "$file" ] && rebrand_file "$file"
done

# Navigation components
for file in resources/views/components/*.blade.php; do
    [ -f "$file" ] && rebrand_file "$file"
done

# Recently created files (API Gateway, MCP, IDE)
rebrand_file "resources/views/api-gateway/index.blade.php"
rebrand_file "resources/views/api-gateway/create.blade.php"
rebrand_file "resources/views/api-gateway/edit.blade.php"
rebrand_file "resources/views/mcp/index.blade.php"
rebrand_file "resources/views/mcp/create.blade.php"
rebrand_file "resources/views/mcp/edit.blade.php"
rebrand_file "resources/views/ide/index.blade.php"

echo ""
echo "✅ Phase 1 Complete: $TOTAL_FILES files rebranded"
echo ""

# Cleanup backup files
find . -name "*.bak" -type f -delete 2>/dev/null || true

echo "🎉 Rebranding Complete!"
echo "Files processed: $TOTAL_FILES"
echo ""
echo "Next steps:"
echo "  1. Review changes: git diff"
echo "  2. Test UI rendering"
echo "  3. Commit changes: git add -A && git commit -m 'rebrand: Update UI from Coolify to Zpanel'"

