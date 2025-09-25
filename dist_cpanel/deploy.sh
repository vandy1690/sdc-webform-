#!/bin/bash

# SDC Webform cPanel Deployment Script
# Usage: ./deploy.sh "commit message"

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "sdc_bidrequest.html" ] || [ ! -d "api" ]; then
    print_error "This script must be run from the dist_cpanel directory"
    exit 1
fi

# Check if git is initialized
if [ ! -d ".git" ]; then
    print_error "Git repository not initialized. Run 'git init' first."
    exit 1
fi

# Get commit message from argument or prompt
if [ -z "$1" ]; then
    echo -n "Enter commit message: "
    read -r COMMIT_MSG
else
    COMMIT_MSG="$1"
fi

if [ -z "$COMMIT_MSG" ]; then
    print_error "Commit message cannot be empty"
    exit 1
fi

print_status "Starting deployment process..."

# Check for uncommitted changes
if ! git diff-index --quiet HEAD --; then
    print_status "Uncommitted changes detected. Adding files..."

    # Show what will be committed
    print_status "Files to be committed:"
    git diff --name-only HEAD

    # Add all changes
    git add .

    # Show status
    print_status "Git status:"
    git status --short

    # Commit changes
    print_status "Committing changes..."
    git commit -m "$COMMIT_MSG

🤖 Generated with Claude Code

Co-Authored-By: Claude <noreply@anthropic.com>"

    print_success "Changes committed successfully"
else
    print_warning "No changes to commit"
fi

# Push to remote if remote exists
if git remote get-url origin > /dev/null 2>&1; then
    print_status "Pushing to GitHub..."
    git push origin main
    print_success "Pushed to GitHub successfully"

    print_status "================================================"
    print_success "DEPLOYMENT COMPLETE!"
    print_status "================================================"
    print_status "Next steps:"
    print_status "1. Go to your cPanel Git Version Control"
    print_status "2. Click 'Manage' on your sdc-webform-cpanel repository"
    print_status "3. Click 'Pull or Deploy' tab"
    print_status "4. Click 'Deploy HEAD Commit'"
    print_status ""
    print_status "Or if using SSH:"
    print_status "ssh to your cPanel account and run:"
    print_status "cd public_html/sdc_form && git pull origin main"
    print_status "================================================"
else
    print_warning "No remote repository configured"
    print_status "To set up remote repository:"
    print_status "1. Create a repository on GitHub"
    print_status "2. Run: git remote add origin https://github.com/USERNAME/sdc-webform-cpanel.git"
    print_status "3. Run: git push -u origin main"
fi

print_success "Deployment script completed!"