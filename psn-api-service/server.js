const express = require('express');
const cors = require('cors');
const { exchangeNpssoForCode, exchangeAccessCodeForAuthTokens } = require('psn-api');

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(express.json());

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ status: 'ok', service: 'psn-api-service' });
});

/**
 * Exchange NPSSO token for access token
 * POST /api/exchange-npsso
 * Body: { npsso: "your_npsso_token" }
 */
app.post('/api/exchange-npsso', async (req, res) => {
    try {
        const { npsso } = req.body;

        if (!npsso) {
            return res.status(400).json({
                success: false,
                error: 'NPSSO token is required',
                message: 'Please provide npsso in request body'
            });
        }

        console.log(`[${new Date().toISOString()}] Exchange request received for NPSSO: ${npsso.substring(0, 20)}...`);

        // Step 1: Exchange NPSSO for Access Code
        console.log('Step 1: Exchanging NPSSO for Access Code...');
        const accessCode = await exchangeNpssoForCode(npsso);
        console.log('✅ Access Code obtained');

        // Step 2: Exchange Access Code for Tokens
        console.log('Step 2: Exchanging Access Code for Access Token...');
        const authorization = await exchangeAccessCodeForAuthTokens(accessCode);
        console.log('✅ Access Token obtained');

        res.json({
            success: true,
            data: {
                access_token: authorization.accessToken,
                refresh_token: authorization.refreshToken || null,
                expires_in: authorization.expiresIn || 3600,
                refresh_token_expires_in: authorization.refreshTokenExpiresIn || null,
                token_type: authorization.tokenType || 'Bearer',
                scope: authorization.scope || null
            }
        });

    } catch (error) {
        console.error(`[${new Date().toISOString()}] Error:`, error.message);
        
        res.status(500).json({
            success: false,
            error: error.message || 'Failed to exchange NPSSO token',
            code: error.code || 'UNKNOWN_ERROR'
        });
    }
});

/**
 * Get user profile using access token
 * POST /api/user-profile
 * Body: { access_token: "your_access_token" }
 */
app.post('/api/user-profile', async (req, res) => {
    try {
        const { access_token } = req.body;

        if (!access_token) {
            return res.status(400).json({
                success: false,
                error: 'Access token is required'
            });
        }

        const { getProfileFromAccountId } = require('psn-api');
        
        // Extract accountId from JWT token
        let accountId = null;
        try {
            const parts = access_token.split('.');
            if (parts.length === 3) {
                const payload = JSON.parse(Buffer.from(parts[1], 'base64').toString());
                if (payload.account_id) {
                    accountId = String(payload.account_id);
                    console.log(`✅ Extracted accountId: ${accountId}`);
                }
            }
        } catch (e) {
            console.log('⚠️  Could not extract accountId:', e.message);
        }
        
        let profile;
        // Try with extracted accountId first
        if (accountId) {
            try {
                console.log(`Trying with accountId: ${accountId}`);
                profile = await getProfileFromAccountId({ accessToken: access_token }, accountId);
            } catch (e) {
                console.log(`Failed with accountId ${accountId}, trying "me":`, e.message);
                profile = await getProfileFromAccountId({ accessToken: access_token }, 'me');
            }
        } else {
            console.log('Using "me" as accountId');
            profile = await getProfileFromAccountId({ accessToken: access_token }, 'me');
        }

        res.json({
            success: true,
            data: profile
        });

    } catch (error) {
        console.error(`[${new Date().toISOString()}] Error:`, error.message);
        
        res.status(500).json({
            success: false,
            error: error.message || 'Failed to get user profile'
        });
    }
});

/**
 * Get user's played games
 * POST /api/user-games
 * Body: { access_token: "your_access_token", accountId: "optional_account_id" }
 */
app.post('/api/user-games', async (req, res) => {
    try {
        const { access_token, accountId } = req.body;

        if (!access_token) {
            return res.status(400).json({
                success: false,
                error: 'Access token is required'
            });
        }

        const { getUserTitles } = require('psn-api');
        
        // Extract accountId from JWT if not provided
        let targetAccountId = accountId || 'me';
        if (!accountId) {
            try {
                const parts = access_token.split('.');
                if (parts.length === 3) {
                    const payload = JSON.parse(Buffer.from(parts[1], 'base64').toString());
                    if (payload.account_id) {
                        targetAccountId = String(payload.account_id);
                        console.log(`✅ Extracted accountId from token: ${targetAccountId}`);
                    }
                }
            } catch (e) {
                console.log('⚠️  Using "me" as accountId');
            }
        }
        
        console.log(`Fetching all games for accountId: ${targetAccountId}`);
        
        // Fetch all games with pagination (max 800 per request)
        let allTitles = [];
        let offset = 0;
        const limit = 800; // Maximum limit per call
        
        while (true) {
            const response = await getUserTitles(
                { accessToken: access_token }, 
                targetAccountId, 
                { limit, offset }
            );
            
            const titles = response.trophyTitles || [];
            allTitles = allTitles.concat(titles);
            
            console.log(`Fetched ${titles.length} games (offset: ${offset}, total so far: ${allTitles.length})`);
            
            // If we got fewer titles than the limit, we've reached the end
            if (titles.length < limit) {
                break;
            }
            
            offset += limit;
        }
        
        console.log(`✅ Total games fetched: ${allTitles.length}`);

        res.json({
            success: true,
            data: {
                trophyTitles: allTitles,
                totalItemCount: allTitles.length
            }
        });

    } catch (error) {
        console.error(`[${new Date().toISOString()}] Error:`, error.message);
        
        res.status(500).json({
            success: false,
            error: error.message || 'Failed to get user games'
        });
    }
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 PSN API Service running on http://localhost:${PORT}`);
    console.log(`📝 Endpoints:`);
    console.log(`   POST /api/exchange-npsso`);
    console.log(`   POST /api/user-profile`);
    console.log(`   POST /api/user-games`);
    console.log(`   GET  /health`);
});
