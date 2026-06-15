import { startStimulusApp } from '@symfony/stimulus-bundle';
import PasswordToggleController from './controllers/password_toggle_controller.js';
import PasswordStrengthController from './controllers/password_strength_controller.js';

// Active la protection CSRF stateless de Symfony (écouteurs globaux sur submit).
import './controllers/csrf_protection_controller.js';

const app = startStimulusApp();
app.register('password-toggle', PasswordToggleController);
app.register('password-strength', PasswordStrengthController);
