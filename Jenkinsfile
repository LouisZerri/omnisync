// Pipeline CI/CD d'OmniSync, déclenché sur la branche master (= mise en production).
//
// Étapes : qualité (PHPStan + php-cs-fixer) → tests (PHPUnit) → déploiement.
// La qualité et les tests tournent sur le serveur Jenkins ; le déploiement se fait par SSH
// vers le VPS de prod (qui a Docker), car Jenkins tourne sur un VPS distinct sans Docker.
//
// Pré-requis sur le serveur Jenkins : PHP 8.4 (+ extensions, dont pdo_sqlite pour les tests)
// et Composer. Et la clé SSH de l'utilisateur "jenkins" (~jenkins/.ssh/id_ed25519) doit être
// autorisée sur le VPS de prod (dans ~/.ssh/authorized_keys de l'utilisateur de déploiement).

pipeline {
    agent any

    environment {
        // Cible SSH du VPS de production et dossier de déploiement (à adapter si besoin).
        DEPLOY_HOST = 'louis@lzerri-project.fr'
        DEPLOY_PATH = '/var/www/omnisync'
    }

    options {
        timestamps()
        disableConcurrentBuilds()
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Installation') {
            steps {
                sh 'composer install --no-interaction --no-progress --prefer-dist --no-scripts'
            }
        }

        stage('Qualité') {
            steps {
                sh 'vendor/bin/phpstan analyse --no-progress'
                sh 'vendor/bin/php-cs-fixer fix --dry-run --diff'
            }
        }

        stage('Tests') {
            steps {
                sh 'php bin/phpunit'
            }
        }

        stage('Déploiement') {
            steps {
                sh '''
                    ssh -o StrictHostKeyChecking=no "$DEPLOY_HOST" "
                        set -e
                        cd $DEPLOY_PATH
                        git pull origin master
                        docker compose -f compose.prod.yaml --env-file .env.prod up -d --build
                        docker compose -f compose.prod.yaml --env-file .env.prod run --rm app php bin/console doctrine:migrations:migrate --no-interaction
                    "
                '''
            }
        }
    }

    post {
        success {
            echo '✅ Déploiement en production réussi.'
        }
        failure {
            echo '❌ Échec du déploiement — voir les logs ci-dessus.'
        }
    }
}
