# Quick Start Guide

## 1. Install Dependencies

```bash
cd psn-api-service
npm install
```

## 2. Start the Service

```bash
npm start
```

The service will run on `http://localhost:3001`

## 3. Test the Service

```bash
curl -X POST http://localhost:3001/api/exchange-npsso \
  -H "Content-Type: application/json" \
  -d '{"npsso": "YOUR_NPSSO_TOKEN_HERE"}'
```

## 4. Configure Laravel

Add to your `.env` file:

```
PSN_API_SERVICE_URL=http://localhost:3001
```

## 5. Run Laravel Service

Now your Laravel application will use the Node.js service automatically!

## Development Mode

For auto-reload during development:

```bash
npm run dev
```

## Troubleshooting

### Service not starting?
- Make sure Node.js is installed: `node --version`
- Check if port 3001 is available

### Laravel can't connect?
- Make sure Node.js service is running
- Check `PSN_API_SERVICE_URL` in `.env`
- Verify service is accessible: `curl http://localhost:3001/health`
