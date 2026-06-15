import { Controller } from '@hotwired/stimulus';

/*
 * Affiche / masque le mot de passe du champ ciblé et bascule l'icône œil.
 */
export default class extends Controller {
    static targets = ['input', 'iconShow', 'iconHide'];

    toggle() {
        const isHidden = this.inputTarget.type === 'password';
        this.inputTarget.type = isHidden ? 'text' : 'password';

        if (this.hasIconShowTarget) {
            this.iconShowTarget.classList.toggle('hidden', isHidden);
        }
        if (this.hasIconHideTarget) {
            this.iconHideTarget.classList.toggle('hidden', !isHidden);
        }
    }
}
