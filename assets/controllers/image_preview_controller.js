import { Controller } from '@hotwired/stimulus';

/*
 * Affiche un aperçu immédiat de l'image sélectionnée dans le champ fichier,
 * avant même l'envoi du formulaire.
 */
export default class extends Controller {
    static targets = ['input', 'image', 'placeholder'];

    preview() {
        const file = this.inputTarget.files && this.inputTarget.files[0];
        if (!file) {
            return;
        }

        const url = URL.createObjectURL(file);
        this.imageTarget.src = url;
        this.imageTarget.classList.remove('hidden');

        if (this.hasPlaceholderTarget) {
            this.placeholderTarget.classList.add('hidden');
        }
    }
}
