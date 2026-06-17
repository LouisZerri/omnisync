import { Controller } from '@hotwired/stimulus';

/*
 * Teste la connexion d'un canal en appelant l'endpoint dédié, et affiche le résultat
 * (connecté / injoignable) en ligne, sans recharger la page.
 */
export default class extends Controller {
    static targets = ['status', 'button'];
    static values = { url: String };

    async run() {
        this.buttonTarget.disabled = true;
        this.setStatus('Test…', 'text-gray-400');

        try {
            const response = await fetch(this.urlValue, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            data.ok
                ? this.setStatus('Connecté', 'text-emerald-600 font-medium')
                : this.setStatus('Injoignable', 'text-red-600 font-medium');
        } catch (error) {
            this.setStatus('Erreur', 'text-red-600 font-medium');
        } finally {
            this.buttonTarget.disabled = false;
        }
    }

    setStatus(text, classes) {
        this.statusTarget.textContent = text;
        this.statusTarget.className = `text-xs ${classes}`;
    }
}
