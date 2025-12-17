#!/bin/bash
# Smart Farm Website Startup Script

echo "🚀 Starting Smart Farm Website..."
echo ""

# Navigate to project directory
cd "$(dirname "$0")" || exit

# Check PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP not installed!"
    exit 1
fi
echo "✅ PHP $(php -v | head -n 1 | cut -d' ' -f2)"

# Check MySQL
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL not installed!"
    exit 1
fi
echo "✅ MySQL installed"

# Setup database if needed
echo ""
echo "📊 Setting up database..."
mysql -u root -p'@Ger4One%jesko' < database.sql 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ Database ready"
else
    echo "⚠️  Database setup had issues (may already exist)"
fi

# Create Images folder
mkdir -p Assets/Images
chmod 755 Assets/Images
echo "✅ Images folder ready"

# Start PHP server
echo ""
echo "🌐 Starting PHP server..."
echo "📍 Access website at: http://localhost:8000/SourceCode/index/index.php"
echo "⏹️  Press Ctrl+C to stop server"
echo ""

php -S localhost:8000
