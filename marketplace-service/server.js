'use strict';

const express = require('express');
const config = require('./config');
const store = require('./store');
const createMiddleware = require('./middleware');

const app = express();
app.use(express.json());

const { requireApiKey, rateLimit, simulatePersonality } = createMiddleware(config);

function validateProduct(body) {
    const errors = [];
    if (typeof body.name !== 'string' || body.name.trim() === '') {
        errors.push('name manquant');
    }
    if (!Number.isInteger(body.priceCents) || body.priceCents < 0) {
        errors.push('priceCents invalide');
    }
    if (!Number.isInteger(body.stock) || body.stock < 0) {
        errors.push('stock invalide');
    }
    return errors;
}

// Test de connexion : confirme l'accessibilité et la validité de la clé API,
// sans subir la latence/les erreurs simulées (un ping doit rester fiable).
app.get('/health', requireApiKey, (req, res) => {
    res.json({ status: 'ok', channel: config.channelName });
});

// Lecture d'une fiche (pour vérifier qu'un produit a bien été propagé vers le canal).
app.get('/products/:sku', requireApiKey, (req, res) => {
    const product = store.get(req.params.sku);
    if (!product) {
        return res.status(404).json({ error: 'Produit inconnu sur ce canal' });
    }
    res.json(product);
});

const channelMiddleware = [requireApiKey, rateLimit, simulatePersonality];

// Push d'une fiche produit (création ou mise à jour par SKU).
app.post('/products', channelMiddleware, (req, res) => {
    const body = req.body || {};
    if (typeof body.sku !== 'string' || body.sku.trim() === '') {
        return res.status(400).json({ error: 'sku obligatoire' });
    }

    // Seuls les canaux exigeants (Zelmark) rejettent une fiche incomplète.
    if (config.strictValidation) {
        const errors = validateProduct(body);
        if (errors.length > 0) {
            return res.status(400).json({ error: 'Validation échouée', details: errors });
        }
    }

    const existed = store.upsert({
        sku: body.sku,
        name: body.name ?? null,
        description: body.description ?? null,
        priceCents: body.priceCents ?? null,
        stock: body.stock ?? null,
    });

    res.status(existed ? 200 : 201).json({ sku: body.sku, status: existed ? 'updated' : 'created' });
});

// Mise à jour du stock d'un produit déjà connu du canal.
app.patch('/products/:sku/stock', channelMiddleware, (req, res) => {
    const sku = req.params.sku;
    if (!store.has(sku)) {
        return res.status(404).json({ error: 'Produit inconnu sur ce canal' });
    }
    const stock = req.body?.stock;
    if (!Number.isInteger(stock) || stock < 0) {
        return res.status(400).json({ error: 'stock invalide' });
    }
    store.setStock(sku, stock);
    res.json({ sku, stock });
});

// Mise à jour du prix d'un produit déjà connu du canal.
app.patch('/products/:sku/price', channelMiddleware, (req, res) => {
    const sku = req.params.sku;
    if (!store.has(sku)) {
        return res.status(404).json({ error: 'Produit inconnu sur ce canal' });
    }
    const priceCents = req.body?.priceCents;
    if (!Number.isInteger(priceCents) || priceCents < 0) {
        return res.status(400).json({ error: 'priceCents invalide' });
    }
    store.setPrice(sku, priceCents);
    res.json({ sku, priceCents });
});

app.listen(config.port, () => {
    console.log(`[${config.channelName}] marketplace en écoute sur le port ${config.port}`);
});
