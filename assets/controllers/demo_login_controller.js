import { Controller } from '@hotwired/stimulus';

/*
 * Remplit le formulaire de connexion avec un compte de démonstration au clic
 * (pratique pour les recruteurs qui veulent tester l'application rapidement).
 */
export default class extends Controller {
    static targets = ['email', 'password'];

    fill(event) {
        const { email, password } = event.params;
        this.emailTarget.value = email;
        this.passwordTarget.value = password;
        this.passwordTarget.focus();
    }
}
