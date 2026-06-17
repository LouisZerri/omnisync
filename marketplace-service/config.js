'use strict';

// Configuration du canal via variables d'environnement : c'est elle qui donne
// sa "personnalité" à chaque instance (Voltura fiable, Cartelio lente, Zelmark exigeante).
module.exports = {
    port: Number(process.env.PORT || 3000),
    channelName: process.env.CHANNEL_NAME || 'marketplace',
    apiKey: process.env.API_KEY || '',
    latencyMs: Number(process.env.LATENCY_MS || 0),
    errorRate: Number(process.env.ERROR_RATE || 0), // 0..1 : probabilité d'un 503 aléatoire
    strictValidation: process.env.STRICT_VALIDATION === 'true',
    rateLimit: Number(process.env.RATE_LIMIT || 0), // 0 = illimité
    rateWindowMs: Number(process.env.RATE_WINDOW_MS || 60000),
};
