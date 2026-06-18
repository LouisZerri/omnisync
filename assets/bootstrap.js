import { startStimulusApp } from '@symfony/stimulus-bundle';
import PasswordToggleController from './controllers/password_toggle_controller.js';
import PasswordStrengthController from './controllers/password_strength_controller.js';
import ImagePreviewController from './controllers/image_preview_controller.js';
import ConfirmController from './controllers/confirm_controller.js';
import LightboxController from './controllers/lightbox_controller.js';
import FileInputController from './controllers/file_input_controller.js';

// Active la protection CSRF stateless de Symfony (écouteurs globaux sur submit).
import './controllers/csrf_protection_controller.js';

// Enregistre l'élément <turbo-mercure-stream-source> (abonnement Mercure des Turbo Streams temps réel).
// L'enregistrement manuel des contrôleurs court-circuite l'autoimport de controllers.json : on l'importe ici.
import '@symfony/ux-turbo/dist/mercure_stream_source_element.js';

const app = startStimulusApp();
app.register('password-toggle', PasswordToggleController);
app.register('password-strength', PasswordStrengthController);
app.register('image-preview', ImagePreviewController);
app.register('confirm', ConfirmController);
app.register('lightbox', LightboxController);
app.register('file-input', FileInputController);
