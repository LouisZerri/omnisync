import { Controller } from '@hotwired/stimulus';

/*
 * Vérifie en temps réel les critères du mot de passe et coche les indicateurs :
 * 8 caractères minimum, une majuscule, une minuscule, un caractère spécial.
 */
export default class extends Controller {
    static targets = ['password', 'length', 'upper', 'lower', 'special'];

    check() {
        const value = this.passwordTarget.value;

        this.mark(this.hasLengthTarget ? this.lengthTarget : null, value.length >= 8);
        this.mark(this.hasUpperTarget ? this.upperTarget : null, /[A-Z]/.test(value));
        this.mark(this.hasLowerTarget ? this.lowerTarget : null, /[a-z]/.test(value));
        this.mark(this.hasSpecialTarget ? this.specialTarget : null, /[^A-Za-z0-9]/.test(value));
    }

    mark(el, ok) {
        if (!el) {
            return;
        }
        el.classList.toggle('text-emerald-600', ok);
        el.classList.toggle('text-gray-400', !ok);

        const dot = el.querySelector('[data-dot]');
        if (dot) {
            dot.classList.toggle('bg-emerald-500', ok);
            dot.classList.toggle('bg-gray-300', !ok);
        }
    }
}
