import './bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Swal = Swal;

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import {
    createIcons,
    Search, X, FileSpreadsheet, FileText, Printer, Copy, Inbox,
    ChevronLeft, ChevronRight, Landmark, CirclePlus, School, Filter,
    Barcode, MapPin, Layers, Eraser, Pencil, Trash2,
    ArrowUpNarrowWide, ArrowDownWideNarrow,
    Users, UserPlus, IdCard, Briefcase, Shield, Check,
    UserCheck, UserX, Megaphone, Calendar, User, Radio, LayoutGrid
} from 'lucide';
import { initTableEngine } from './components/datatable-engine';

window.Alpine = Alpine;
Alpine.plugin(focus);

// Inicializar motor de tabla nativo
initTableEngine();

// Inicializar iconos Lucide (genera SVGs para los elementos data-lucide)
window.lucideRefresh = () => createIcons({
    icons: {
        Search, X, FileSpreadsheet, FileText, Printer, Copy, Inbox,
        ChevronLeft, ChevronRight, Landmark, CirclePlus, School, Filter,
        Barcode, MapPin, Layers, Eraser, Pencil, Trash2,
        ArrowUpNarrowWide, ArrowDownWideNarrow,
        Users, UserPlus, IdCard, Briefcase, Shield, Check,
        UserCheck, UserX, Megaphone, Calendar, User, Radio, LayoutGrid
    }
});
window.lucideRefresh();

// Re-generar iconos Lucide cuando Alpine inyecta <i data-lucide> sin convertir.
// Se ignora el <svg> generado (que también lleva data-lucide) para evitar bucles.
let lucideTimer = null;
new MutationObserver(() => {
    if (lucideTimer) return;
    lucideTimer = setTimeout(() => {
        lucideTimer = null;
        if (document.querySelector('i[data-lucide]')) {
            window.lucideRefresh();
        }
    }, 50);
}).observe(document.body, { childList: true, subtree: true });

Alpine.start();
