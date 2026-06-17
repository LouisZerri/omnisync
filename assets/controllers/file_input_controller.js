import { Controller } from '@hotwired/stimulus';

/*
 * Remplace l'affichage natif d'un input fichier : affiche le nom du fichier
 * sélectionné (ou un texte par défaut) dans une zone stylée.
 */
export default class extends Controller {
    static targets = ['input', 'name'];

    display() {
        const file = this.inputTarget.files && this.inputTarget.files[0];
        if (this.hasNameTarget) {
            this.nameTarget.textContent = file ? file.name : 'Aucun fichier sélectionné';
        }
    }
}
