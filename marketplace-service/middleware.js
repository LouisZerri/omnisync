'use strict';

const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Construit les middlewares du canal à partir de sa configuration (clé API, latence,
// taux d'erreur, rate limit). Isolés de server.js pour séparer les responsabilités.
module.exports = function createMiddleware(config) {
    // Authentification par clé API (header X-Api-Key).
    function requireApiKey(req, res, next) {
        if (!config.apiKey || req.get('X-Api-Key') !== config.apiKey) {
            return res.status(401).json({ error: 'Clé API invalide ou manquante' });
        }
        next();
    }

    // Rate limiting fenêtre fixe, actif seulement si rateLimit > 0 (cas Zelmark).
    let windowStart = Date.now();
    let windowCount = 0;
    function rateLimit(req, res, next) {
        if (config.rateLimit <= 0) {
            return next();
        }
        const now = Date.now();
        if (now - windowStart > config.rateWindowMs) {
            windowStart = now;
            windowCount = 0;
        }
        if (windowCount >= config.rateLimit) {
            return res.status(429).json({ error: 'Trop de requêtes, réessayez plus tard' });
        }
        windowCount++;
        next();
    }

    // Simule la latence et les erreurs réseau propres au canal.
    async function simulatePersonality(req, res, next) {
        if (config.latencyMs > 0) {
            await delay(config.latencyMs);
        }
        if (config.errorRate > 0 && Math.random() < config.errorRate) {
            return res.status(503).json({ error: 'Canal temporairement indisponible' });
        }
        next();
    }

    return { requireApiKey, rateLimit, simulatePersonality };
};
