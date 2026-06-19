# syntax=docker/dockerfile:1

# ─────────── Étape 1 : dépendances PHP (composer, sans dev) ───────────
# Produit vendor/ — réutilisé par le build front (packages Symfony UX liés en
# file:vendor/...) ET par l'image finale.
FROM dunglas/frankenphp:1-php8.4-bookworm AS vendor

ENV APP_ENV=prod
WORKDIR /app
RUN install-php-extensions @composer
COPY composer.json composer.lock symfony.lock ./
# Étape légère : on télécharge juste les paquets (les extensions réelles sont vérifiées
# plus bas, dans l'image finale qui les embarque).
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress --no-interaction --ignore-platform-reqs

# ─────────── Étape 2 : build des assets front (Webpack Encore + Tailwind) ───────────
FROM node:22-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
# Les packages Symfony UX sont liés en file:vendor/... → vendor/ doit être présent
COPY --from=vendor /app/vendor ./vendor
RUN npm ci
# Encore scanne templates/ (classes Tailwind) et assets/ pour produire public/build
COPY webpack.config.js postcss.config.mjs ./
COPY assets ./assets
COPY templates ./templates
RUN npm run build

# ─────────── Étape 3 : image applicative (FrankenPHP) ───────────
FROM dunglas/frankenphp:1-php8.4-bookworm AS app

ENV APP_ENV=prod
WORKDIR /app

# Extensions PHP requises par OmniSync
RUN install-php-extensions \
	@composer \
	apcu \
	intl \
	opcache \
	zip \
	gd \
	pdo_mysql \
	amqp

# Réglages PHP de prod + config FrankenPHP/Caddy (app Symfony + hub Mercure)
COPY frankenphp/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/app.prod.ini
COPY frankenphp/Caddyfile /etc/frankenphp/Caddyfile

# Code applicatif, puis dépendances PHP (étape vendor) et assets compilés (étape assets)
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Garde-fou : vérifie que l'image embarque bien toutes les extensions requises,
# puis autoload optimisé + scripts post-install (cache:clear prod, assets:install)
RUN composer check-platform-reqs --no-dev \
	&& composer dump-autoload --no-dev --classmap-authoritative --no-interaction \
	&& composer run-script --no-dev post-install-cmd

# FrankenPHP sert en HTTP sur 8080 (Apache termine le TLS en amont)
ENV SERVER_NAME=:8080
EXPOSE 8080
