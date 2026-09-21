import './bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Swal = Swal;

import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

// ── MODO SPA: Si la página tiene el contenedor de Inertia (#app) ──
const appElement = document.getElementById('app');

if (appElement) {
    const pinia = createPinia();

    createInertiaApp({
        title: (title) => title ? `${title} - SIADPRO` : 'SIADPRO',
        resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(pinia)
                .mount(el);
        },
        progress: {
            color: '#6366F1',
            showSpinner: false,
        },
    });
} else {
    // ── MODO TRADICIONAL (BLADE / ADMINLTE) ──
    import('alpinejs').then(({ default: Alpine }) => {
        import('@alpinejs/focus').then(({ default: focus }) => {
            window.Alpine = Alpine;
            Alpine.plugin(focus);

            import('lucide').then((lucideModule) => {
                window.lucideRefresh = () => lucideModule.createIcons({
                    icons: {
                        Search: lucideModule.Search,
                        X: lucideModule.X,
                        FileSpreadsheet: lucideModule.FileSpreadsheet,
                        FileText: lucideModule.FileText,
                        Printer: lucideModule.Printer,
                        Copy: lucideModule.Copy,
                        Inbox: lucideModule.Inbox,
                        ChevronLeft: lucideModule.ChevronLeft,
                        ChevronRight: lucideModule.ChevronRight,
                        ChevronsLeft: lucideModule.ChevronsLeft,
                        ChevronsRight: lucideModule.ChevronsRight,
                        ChevronUp: lucideModule.ChevronUp,
                        ChevronDown: lucideModule.ChevronDown,
                        Landmark: lucideModule.Landmark,
                        CirclePlus: lucideModule.CirclePlus,
                        School: lucideModule.School,
                        Filter: lucideModule.Filter,
                        Barcode: lucideModule.Barcode,
                        MapPin: lucideModule.MapPin,
                        Layers: lucideModule.Layers,
                        Eraser: lucideModule.Eraser,
                        Pencil: lucideModule.Pencil,
                        Trash2: lucideModule.Trash2,
                        ArrowUpNarrowWide: lucideModule.ArrowUpNarrowWide,
                        ArrowDownWideNarrow: lucideModule.ArrowDownWideNarrow,
                        Users: lucideModule.Users,
                        UserPlus: lucideModule.UserPlus,
                        IdCard: lucideModule.IdCard,
                        Briefcase: lucideModule.Briefcase,
                        Shield: lucideModule.Shield,
                        Check: lucideModule.Check,
                        UserCheck: lucideModule.UserCheck,
                        UserX: lucideModule.UserX,
                        Megaphone: lucideModule.Megaphone,
                        Calendar: lucideModule.Calendar,
                        User: lucideModule.User,
                        Radio: lucideModule.Radio,
                        LayoutGrid: lucideModule.LayoutGrid,
                        BookHeart: lucideModule.BookHeart,
                        BookOpen: lucideModule.BookOpen,
                        CalendarCheck: lucideModule.CalendarCheck,
                        NotebookPen: lucideModule.NotebookPen,
                        House: lucideModule.House,
                        ZoomIn: lucideModule.ZoomIn,
                        ZoomOut: lucideModule.ZoomOut,
                        Download: lucideModule.Download,
                        Maximize2: lucideModule.Maximize2,
                        FileQuestion: lucideModule.FileQuestion,
                        AlertTriangle: lucideModule.AlertTriangle
                    }
                });
                window.lucideRefresh();
            });

            import('./components/datatable-engine').then(({ initTableEngine }) => {
                initTableEngine();
            });

            import('./viewer').then(({ initFileViewer }) => {
                initFileViewer();
            });

            Alpine.start();
        });
    });
}
