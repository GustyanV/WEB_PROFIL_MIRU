import './bootstrap';
import Alpine from 'alpinejs';
import responsiveMenu from './responsive-menu';
Alpine.data('responsiveMenu', responsiveMenu);
window.Alpine = Alpine;
Alpine.start();

import { setupPdfLogoForms } from './pdf-logo';
setupPdfLogoForms();
