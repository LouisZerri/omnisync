import { Controller } from '@hotwired/stimulus';

/*
 * Agrandit une image au clic dans une surcouche plein écran.
 * Fermeture au clic sur le fond ou via la touche Échap.
 */
export default class extends Controller {
    open(event) {
        const src = event.currentTarget.dataset.lightboxSrc || event.currentTarget.src;
        if (!src) {
            return;
        }

        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-50 flex cursor-zoom-out items-center justify-center bg-black/80 p-6';

        const img = document.createElement('img');
        img.src = src;
        img.alt = '';
        img.className = 'max-h-full max-w-full rounded-lg shadow-2xl';
        overlay.appendChild(img);

        const close = () => {
            overlay.remove();
            document.removeEventListener('keydown', onKey);
        };
        const onKey = (event) => {
            if (event.key === 'Escape') {
                close();
            }
        };

        overlay.addEventListener('click', close);
        document.addEventListener('keydown', onKey);
        document.body.appendChild(overlay);
    }
}
