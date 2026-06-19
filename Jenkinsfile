// Pipeline de déploiement continu d'OmniSync.
//
// Jenkins tourne sur un VPS distinct (sans Docker) : il ne construit pas l'image lui-même,
// il se connecte en SSH au VPS de production (qui a Docker) et y déclenche le déploiement.
// Déclenché sur la branche master (= mise en production).
//
// Pré-requis côté Jenkins :
//   - plugin "SSH Agent"
//   - un credential SSH (clé privée) d'ID "omnisync-prod-ssh" dont la clé publique est
//     autorisée sur le VPS de prod (~/.ssh/authorized_keys de l'utilisateur de déploiement)

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

        stage('Déploiement') {
            steps {
                sshagent(credentials: ['omnisync-prod-ssh']) {
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
