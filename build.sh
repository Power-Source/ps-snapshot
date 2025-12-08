#!/bin/bash

# PS Snapshot Build Script
# This script minifies JavaScript files and compiles SASS

set -e

echo "📦 PS Snapshot Build Process"
echo "================================"

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install Node.js and npm."
    exit 1
fi

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PLUGIN_DIR"

# Install dependencies if needed
if [ ! -d "node_modules" ]; then
    echo -e "${BLUE}📥 Installing npm dependencies...${NC}"
    npm install
fi

# Parse arguments
case "${1:-build}" in
    build)
        echo -e "${BLUE}🔨 Building JavaScript and SASS...${NC}"
        npm run build
        echo -e "${GREEN}✅ Build complete!${NC}"
        ;;
    js)
        echo -e "${BLUE}🔨 Building JavaScript...${NC}"
        npm run build:js
        echo -e "${GREEN}✅ JavaScript build complete!${NC}"
        ;;
    sass)
        echo -e "${BLUE}🔨 Compiling SASS...${NC}"
        npm run build:sass
        echo -e "${GREEN}✅ SASS compilation complete!${NC}"
        ;;
    watch)
        echo -e "${BLUE}👀 Watching for changes...${NC}"
        npm run watch
        ;;
    dev)
        echo -e "${BLUE}🚀 Starting development mode (build + watch)...${NC}"
        npm run dev
        ;;
    clean)
        echo -e "${BLUE}🗑️  Cleaning build artifacts...${NC}"
        rm -f assets/js/admin.min.js assets/js/admin.min.js.map
        rm -f css/snapshots-admin-styles.css css/snapshots-menu-icon.css
        echo -e "${GREEN}✅ Cleanup complete!${NC}"
        ;;
    *)
        echo "Usage: ./build.sh [build|js|sass|watch|dev|clean]"
        echo ""
        echo "Commands:"
        echo "  build  - Build all (default)"
        echo "  js     - Build JavaScript only"
        echo "  sass   - Compile SASS only"
        echo "  watch  - Watch for changes and rebuild automatically"
        echo "  dev    - Build + watch (recommended for development)"
        echo "  clean  - Remove build artifacts"
        exit 1
        ;;
esac
