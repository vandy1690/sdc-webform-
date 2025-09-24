#!/bin/bash

# SDC Webform Backend Setup Script
echo "🚀 Setting up SDC Webform Backend..."

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js first."
    echo "   Visit: https://nodejs.org/"
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install npm first."
    exit 1
fi

echo "✅ Node.js and npm are installed"

# Navigate to backend directory
cd backend

# Install dependencies
echo "📦 Installing dependencies..."
npm install

if [ $? -ne 0 ]; then
    echo "❌ Failed to install dependencies"
    exit 1
fi

echo "✅ Dependencies installed successfully"

# Create database directory
echo "🗄️  Setting up database..."
mkdir -p database

# Copy environment file if it doesn't exist
if [ ! -f .env ]; then
    cp config.env .env
    echo "📝 Created .env file from template"
    echo "⚠️  Please edit backend/.env with your email configuration"
else
    echo "✅ .env file already exists"
fi

echo ""
echo "🎉 Setup completed successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Edit backend/.env with your email configuration"
echo "2. Start the backend server: cd backend && npm start"
echo "3. Access the form at: http://localhost:3000/sdc_bidrequest.html"
echo "4. Access admin dashboard at: http://localhost:3000/admin-dashboard.html"
echo ""
echo "📧 Email Setup (Gmail):"
echo "1. Go to https://myaccount.google.com/security"
echo "2. Enable 2-Factor Authentication"
echo "3. Generate an App-Specific Password"
echo "4. Update EMAIL_USER and EMAIL_PASS in backend/.env"
echo ""
echo "🔧 DDEV Integration:"
echo "The backend server will run on port 3000 and serve your static files"
echo "Your DDEV site will continue to work at https://sdc-webform.ddev.site"
