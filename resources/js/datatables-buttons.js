import 'datatables.net-bs5';
import 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.html5.js';
import 'datatables.net-buttons/js/buttons.print.js';
import JSZip from 'jszip';
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';

window.JSZip = JSZip;
window.pdfMake = pdfMake;
pdfMake.vfs = pdfFonts;
