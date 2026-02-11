#!/bin/bash

echo "================================================"
echo "  Data Import Dashboard - Frontend Launcher"
echo "================================================"
echo ""

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "[1/4] Creating .env file..."
    cp .env.example .env
    
    echo "[2/4] Generating application key..."
    php artisan key:generate
else
    echo "[1/4] Environment file exists ✓"
    echo "[2/4] Application key already set ✓"
fi

echo ""
echo "[3/4] Checking dependencies..."

# Check if vendor exists
if [ ! -d "vendor" ]; then
    echo "Installing PHP dependencies..."
    composer install
else
    echo "PHP dependencies installed ✓"
fi

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "Installing Node dependencies..."
    npm install
else
    echo "Node dependencies installed ✓"
fi

echo ""
echo "[4/4] Starting servers..."
echo ""
echo "================================================"
echo "Backend API should be running at:"
echo "http://localhost:8080"
echo ""
echo "Frontend will be available at:"
echo "http://localhost:8000"
echo "================================================"
echo ""
echo "Starting Laravel server..."
php artisan serve &
LARAVEL_PID=$!

sleep 2

echo "Starting Vite dev server..."
npm run dev &
VITE_PID=$!

echo ""
echo "✓ Servers started!"
echo "✓ Laravel PID: $LARAVEL_PID"
echo "✓ Vite PID: $VITE_PID"
echo "✓ Open: http://localhost:8000"
echo ""
echo "Press Ctrl+C to stop all servers"
echo ""

# Wait for user interrupt
trap "kill $LARAVEL_PID $VITE_PID 2>/dev/null; exit" INT TERM

wait
