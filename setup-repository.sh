#!/bin/bash

echo "🚀 Setting up Instavid Shoppable Videos Repository"
echo "=================================================="

# Check if git is installed
if ! command -v git &> /dev/null; then
    echo "❌ Git is not installed. Please install Git first."
    exit 1
fi

# Initialize git repository
echo "📁 Initializing Git repository..."
git init

# Add all files
echo "📝 Adding files to repository..."
git add .

# Initial commit
echo "💾 Creating initial commit..."
git commit -m "Initial commit: Instavid Shoppable Videos v1.0.0"

# Set main branch
echo "🌿 Setting main branch..."
git branch -M main

echo ""
echo "✅ Repository setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Create a new repository on GitHub: https://github.com/new"
echo "2. Name it: instavid-shoppable-videos"
echo "3. Make it public or private (your choice)"
echo "4. Don't initialize with README (we already have one)"
echo "5. Run these commands:"
echo ""
echo "   git remote add origin https://github.com/YOUR_USERNAME/instavid-shoppable-videos.git"
echo "   git push -u origin main"
echo ""
echo "🎯 After pushing to GitHub, customers can install with:"
echo "   composer require instavid/shoppable-videos"
echo ""
echo "🚀 You're ready to distribute your extension professionally!" 