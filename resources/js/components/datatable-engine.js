/**
 * DataTable Nativo con Tailwind CSS
 * Sin dependencias de DataTables ni CDNs
 */
export function initTableEngine() {
    window.TableEngine = function(tableId, options = {}) {
        return {
            tableId: tableId,
            search: '',
            perPage: options.perPage || 15,
            serverPaginated: options.serverPaginated || false,
            currentPage: 1,
            sortCol: options.defaultSortCol !== undefined ? options.defaultSortCol : 0,
            sortAsc: options.defaultSortAsc !== undefined ? options.defaultSortAsc : true,
            totalRows: 0,
            filteredRowsCount: 0,
            rows: [],

            init() {
                this.$nextTick(() => {
                    this.extractRows();
                });
                this.$watch('perPage', () => {
                    this.currentPage = 1;
                    this.applyFilterAndPagination();
                });
                this.$watch('search', () => {
                    this.currentPage = 1;
                    this.applyFilterAndPagination();
                });
            },

            onPerPageChange(event) {
                if (this.serverPaginated) {
                    const val = event ? event.target.value : this.perPage;
                    const url = new URL(window.location.href);
                    if (val === 'all') {
                        url.searchParams.delete('per_page');
                    } else {
                        url.searchParams.set('per_page', val);
                    }
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                    return;
                }
                this.currentPage = 1;
                this.applyFilterAndPagination();
            },

            onSearchChange() {
                this.currentPage = 1;
                this.applyFilterAndPagination();
            },

            extractRows() {
                const table = document.getElementById(this.tableId);
                if (!table) return;

                const tbody = table.querySelector('tbody');
                if (!tbody) return;

                const trs = Array.from(tbody.querySelectorAll('tr[data-table-row]'));
                this.rows = trs.map((tr, index) => {
                    const cells = Array.from(tr.children).map(td => ({
                        text: td.innerText.trim().toLowerCase(),
                        raw: td.innerText.trim(),
                        html: td.innerHTML
                    }));
                    return {
                        id: index,
                        el: tr,
                        cells: cells,
                        searchText: cells.map(c => c.text).join(' ')
                    };
                });

                this.totalRows = this.rows.length;
                this.applyFilterAndPagination();
            },

            get filteredRows() {
                let result = this.rows;

                // Filtrar por búsqueda
                if (this.search.trim() !== '') {
                    const query = this.search.toLowerCase().trim();
                    result = result.filter(r => r.searchText.includes(query));
                }

                // Ordenar
                if (this.sortCol !== null) {
                    result = [...result].sort((a, b) => {
                        const cellA = a.cells[this.sortCol]?.raw || '';
                        const cellB = b.cells[this.sortCol]?.raw || '';

                        // Intentar ordenar numéricamente
                        const numA = parseFloat(cellA.replace(/[^0-9.-]+/g, ''));
                        const numB = parseFloat(cellB.replace(/[^0-9.-]+/g, ''));

                        if (!isNaN(numA) && !isNaN(numB) && cellA.match(/^[0-9.,$-]+$/)) {
                            return this.sortAsc ? numA - numB : numB - numA;
                        }

                        return this.sortAsc 
                            ? cellA.localeCompare(cellB, 'es', { numeric: true, sensitivity: 'base' })
                            : cellB.localeCompare(cellA, 'es', { numeric: true, sensitivity: 'base' });
                    });
                }

                return result;
            },

            get paginatedRows() {
                const filtered = this.filteredRows;
                this.filteredRowsCount = filtered.length;

                if (this.perPage === 'all' || parseInt(this.perPage) >= filtered.length) {
                    return filtered;
                }

                const perPageNum = parseInt(this.perPage);
                const start = (this.currentPage - 1) * perPageNum;
                return filtered.slice(start, start + perPageNum);
            },

            get totalPages() {
                if (this.perPage === 'all') return 1;
                return Math.ceil(this.filteredRowsCount / parseInt(this.perPage)) || 1;
            },

            get pagesArray() {
                const total = this.totalPages;
                const current = this.currentPage;
                const pages = [];

                if (total <= 7) {
                    for (let i = 1; i <= total; i++) pages.push(i);
                } else {
                    if (current <= 4) {
                        pages.push(1, 2, 3, 4, 5, '...', total);
                    } else if (current >= total - 3) {
                        pages.push(1, '...', total - 4, total - 3, total - 2, total - 1, total);
                    } else {
                        pages.push(1, '...', current - 1, current, current + 1, '...', total);
                    }
                }
                return pages;
            },

            get fromIndex() {
                if (this.filteredRowsCount === 0) return 0;
                if (this.perPage === 'all') return 1;
                return (this.currentPage - 1) * parseInt(this.perPage) + 1;
            },

            get toIndex() {
                if (this.filteredRowsCount === 0) return 0;
                if (this.perPage === 'all') return this.filteredRowsCount;
                return Math.min(this.currentPage * parseInt(this.perPage), this.filteredRowsCount);
            },

            applyFilterAndPagination() {
                const visibleRows = this.paginatedRows;
                const visibleIds = new Set(visibleRows.map(r => r.id));

                const table = document.getElementById(this.tableId);
                if (!table) return;

                const tbody = table.querySelector('tbody');
                if (!tbody) return;

                visibleRows.forEach(row => {
                    row.el.style.display = '';
                    tbody.appendChild(row.el);
                });

                this.rows.forEach(row => {
                    if (!visibleIds.has(row.id)) {
                        row.el.style.display = 'none';
                    }
                });

                const emptyRow = tbody.querySelector('.table-empty-row');
                if (emptyRow) {
                    emptyRow.style.display = this.filteredRowsCount === 0 ? '' : 'none';
                }
            },

            sortBy(colIndex) {
                if (this.sortCol === colIndex) {
                    this.sortAsc = !this.sortAsc;
                } else {
                    this.sortCol = colIndex;
                    this.sortAsc = true;
                }
                this.currentPage = 1;
                this.$nextTick(() => this.applyFilterAndPagination());
            },

            setPage(page) {
                if (page === '...' || page < 1 || page > this.totalPages) return;
                this.currentPage = page;
                this.applyFilterAndPagination();
            },

            getTableData(excludeNoExport = true) {
                const table = document.getElementById(this.tableId);
                if (!table) return { headers: [], rows: [] };

                const thead = table.querySelector('thead');
                const headers = [];
                const colIndexes = [];

                if (thead) {
                    const ths = thead.querySelectorAll('th');
                    ths.forEach((th, idx) => {
                        if (excludeNoExport && (th.classList.contains('no-export') || th.dataset.noExport)) {
                            return;
                        }
                        headers.push(th.innerText.replace(/[\n\r]+/g, ' ').trim());
                        colIndexes.push(idx);
                    });
                }

                const dataRows = this.filteredRows.map(row => {
                    return colIndexes.map(idx => {
                        return row.cells[idx]?.raw || '';
                    });
                });

                return { headers, rows: dataRows };
            },

            exportCSV(filename = 'exportacion') {
                const { headers, rows } = this.getTableData(true);
                let csvContent = '\uFEFF';

                csvContent += headers.map(h => `"${h.replace(/"/g, '""')}"`).join(',') + '\r\n';

                rows.forEach(row => {
                    const rowContent = row.map(val => `"${val.replace(/"/g, '""')}"`).join(',');
                    csvContent += rowContent + '\r\n';
                });

                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${filename}_${new Date().toISOString().slice(0, 10)}.csv`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            },

            exportExcel(filename = 'exportacion') {
                const { headers, rows } = this.getTableData(true);
                
                let excelTemplate = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
                <head>
                    <meta charset="utf-8">
                    <!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
                    <x:Name>${filename}</x:Name>
                    <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                    </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
                    <style>
                        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px; }
                        th { background-color: #1E40AF; color: #FFFFFF; font-weight: bold; border: 1px solid #D1D5DB; padding: 8px; text-align: left; }
                        td { border: 1px solid #E5E7EB; padding: 6px; }
                        tr:nth-child(even) td { background-color: #F9FAFB; }
                    </style>
                </head>
                <body>
                    <table>
                        <thead>
                            <tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr>
                        </thead>
                        <tbody>
                            ${rows.map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}
                        </tbody>
                    </table>
                </body>
                </html>`;

                const blob = new Blob(['\uFEFF' + excelTemplate], { type: 'application/vnd.ms-excel;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${filename}_${new Date().toISOString().slice(0, 10)}.xls`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            },

            copyToClipboard() {
                const { headers, rows } = this.getTableData(true);
                let text = headers.join('\t') + '\n';
                rows.forEach(row => {
                    text += row.join('\t') + '\n';
                });

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(() => {
                        alert('¡Datos copiados al portapapeles!');
                    });
                } else {
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    alert('¡Datos copiados al portapapeles!');
                }
            },

            printTable() {
                const { headers, rows } = this.getTableData(true);
                const printWindow = window.open('', '', 'height=700,width=900');
                
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Impresión de Registros</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; color: #1f2937; }
                            h2 { text-align: center; margin-bottom: 20px; color: #111827; }
                            table { width: 100%; border-collapse: collapse; font-size: 11px; }
                            th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
                            th { background-color: #f3f4f6; font-weight: bold; }
                            tr:nth-child(even) { background-color: #f9fafb; }
                            .footer { margin-top: 15px; font-size: 10px; text-align: right; color: #6b7280; }
                            @media print {
                                body { margin: 0; }
                                @page { size: landscape; margin: 10mm; }
                            }
                        </style>
                    </head>
                    <body>
                        <h2>Reporte de Registros</h2>
                        <table>
                            <thead>
                                <tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr>
                            </thead>
                            <tbody>
                                ${rows.map(row => `<tr>${row.map(c => `<td>${c}</td>`).join('')}</tr>`).join('')}
                            </tbody>
                        </table>
                        <div class="footer">Total registros: ${rows.length} | Generado: ${new Date().toLocaleString()}</div>
                    </body>
                    </html>
                `);

                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 400);
            }
        };
    };
}
