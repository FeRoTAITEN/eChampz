#!/bin/bash

echo "🧪 Testing PSN API Service"
echo "=========================="
echo ""

# Test 1: Health Check
echo "1️⃣  Testing Health Check..."
HEALTH_RESPONSE=$(curl -s http://localhost:3001/health)
if [ $? -eq 0 ]; then
    echo "✅ Health Check: OK"
    echo "   Response: $HEALTH_RESPONSE"
else
    echo "❌ Health Check: FAILED"
    echo "   Make sure Node.js service is running: npm start"
    exit 1
fi
echo ""

# Test 2: Exchange NPSSO (if provided)
if [ -z "$1" ]; then
    echo "2️⃣  Skipping Exchange Test (no NPSSO token provided)"
    echo "   Usage: ./test.sh YOUR_NPSSO_TOKEN"
else
    echo "2️⃣  Testing Exchange NPSSO Token..."
    EXCHANGE_RESPONSE=$(curl -s -X POST http://localhost:3001/api/exchange-npsso \
        -H "Content-Type: application/json" \
        -d "{\"npsso\": \"$1\"}")
    
    if echo "$EXCHANGE_RESPONSE" | grep -q "success.*true"; then
        echo "✅ Exchange: SUCCESS"
        echo "   Response: $EXCHANGE_RESPONSE" | head -c 200
        echo "..."
    else
        echo "❌ Exchange: FAILED"
        echo "   Response: $EXCHANGE_RESPONSE"
    fi
fi

echo ""
echo "✨ Test completed"
