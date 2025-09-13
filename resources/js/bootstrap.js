import axios from 'axios';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Set axios defaults
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Make bootstrap available globally
window.bootstrap = bootstrap;
