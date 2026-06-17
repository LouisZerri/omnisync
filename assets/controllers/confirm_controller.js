import { Controller } from '@hotwired/stimulus';

/*
 * Demande une confirmation avant de soumettre le formulaire associé.
 * Utilisé pour les actions destructrices (suppression).
 */
export default class extends Controller {
    static values = { message: String };

    connect() {
        this.element.addEventListener('submit', this.confirm.bind(this));
    }

    disconnect() {
        this.element.removeEventListener('submit', this.confirm.bind(this));
    }

    confirm(event) {
        if (!window.confirm(this.messageValue)) {
            event.preventDefault();
        }
    }
}
