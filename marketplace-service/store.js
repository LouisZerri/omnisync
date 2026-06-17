'use strict';

// Catalogue en mémoire du canal (simulateur : pas de base de données), indexé par SKU.
// Isolé du serveur HTTP pour séparer l'état des données de la couche routes/middleware.
const products = new Map();

module.exports = {
    has(sku) {
        return products.has(sku);
    },

    get(sku) {
        return products.get(sku);
    },

    // Crée ou remplace la fiche d'un SKU ; renvoie true si le produit existait déjà.
    upsert(product) {
        const existed = products.has(product.sku);
        products.set(product.sku, product);

        return existed;
    },

    setStock(sku, stock) {
        products.get(sku).stock = stock;
    },

    setPrice(sku, priceCents) {
        products.get(sku).priceCents = priceCents;
    },
};
