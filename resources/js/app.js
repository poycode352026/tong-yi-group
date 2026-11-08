import '../css/app.css';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({ title: (title) => title && title !== '同一集团' ? `${title} — 同一集团` : '同一集团' });
