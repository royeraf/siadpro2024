import './bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Swal = Swal;

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import {
    createIcons,
    Search, X, FileSpreadsheet, FileText, Printer, Copy, Inbox,
    ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, ChevronUp, ChevronDown, Landmark, CirclePlus, School, Filter,
    Barcode, MapPin, Layers, Eraser, Pencil, Trash2,
    ArrowUpNarrowWide, ArrowDownWideNarrow,
    Users, UserPlus, IdCard, Briefcase, Shield, Check,
    UserCheck, UserX, Megaphone, Calendar, User, Radio, LayoutGrid, BookHeart,
    BookOpen, CalendarCheck, NotebookPen,
    ZoomIn, ZoomOut, Download, Maximize2,
    FileQuestion, AlertTriangle
} from 'lucide';
import { initTableEngine } from './components/datatable-engine';
import { initFileViewer } from './viewer';

window.Alpine = Alpine;
Alpine.plugin(focus);

// Inicializar motor de tabla nativo
initTableEngine();

// Inicializar visualizador de archivos
initFileViewer();

// Inicializar iconos Lucide (genera SVGs para los elementos data-lucide)
window.lucideRefresh = () => createIcons({
    icons: {
        Search, X, FileSpreadsheet, FileText, Printer, Copy, Inbox,
        ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, ChevronUp, ChevronDown, Landmark, CirclePlus, School, Filter,
        Barcode, MapPin, Layers, Eraser, Pencil, Trash2,
        ArrowUpNarrowWide, ArrowDownWideNarrow,
        Users, UserPlus, IdCard, Briefcase, Shield, Check,
        UserCheck, UserX, Megaphone, Calendar, User, Radio, LayoutGrid, BookHeart,
        BookOpen, CalendarCheck, NotebookPen,
        ZoomIn, ZoomOut, Download, Maximize2,
        FileQuestion, AlertTriangle
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
