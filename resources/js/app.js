import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';
import html2canvas from 'html2canvas-oklch';
import { jsPDF } from 'jspdf';
window.html2canvas = html2canvas;
window.jspdf = { jsPDF };

document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.morphed', ({ el, component }) => {
        const isReports = el?.id === 'reports-page' ||
            el?.closest?.('#reports-page') ||
            component?.name?.includes?.('reports-analytics');
        if (isReports) {
            window.dispatchEvent(new CustomEvent('reports-charts-refresh'));
        }
    });
});

document.addEventListener('alpine:init', () => {
    Alpine.data('reportsCharts', (config) => ({
        charts: {},
        exportingPdf: false,

        init() {
            this.destroyCharts();
            this.$nextTick(() => setTimeout(() => this.initCharts(config), 80));

            const refreshCharts = () => {
                if (this.$wire) {
                    this.$wire.getChartConfig().then((cfg) => {
                        this.destroyCharts(cfg?.activeTab);
                        this.$nextTick(() => setTimeout(() => this.initCharts(cfg), 50));
                    });
                }
            };

            const handleRefresh = () => refreshCharts();
            window.addEventListener('reports-charts-refresh', handleRefresh);
            if (typeof this.$cleanup === 'function') {
                this.$cleanup(() => window.removeEventListener('reports-charts-refresh', handleRefresh));
            }
        },

        destroyCharts(preserveStatusForTab) {
            const keepStatus = preserveStatusForTab === 'overview' && this.charts.status;
            Object.entries(this.charts).forEach(([key, ch]) => {
                if (key === 'status' && keepStatus) return;
                try { ch?.destroy?.(); } catch (e) {}
            });
            if (!keepStatus) {
                this.charts = {};
            } else {
                const statusChart = this.charts.status;
                this.charts = {};
                this.charts.status = statusChart;
            }
        },

        initCharts(cfg) {
            if (typeof window.Chart === 'undefined') return;
            const Chart = window.Chart;
            const gridColor = 'rgba(113, 113, 122, 0.4)';
            const textColor = '#a1a1aa';
            const tooltipBg = '#18181b';
            const tooltipBorder = '#3f3f46';

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 600, easing: 'easeOutQuart' },
                plugins: {
                    tooltip: { backgroundColor: tooltipBg, borderColor: tooltipBorder, borderWidth: 1, titleColor: '#fff', bodyColor: '#d4d4d8', padding: 10, cornerRadius: 8 },
                    legend: { display: false },
                },
            };

            if (cfg.activeTab === 'overview') {
                const vol = cfg.ticketVolume || {};
                if (vol.labels?.length) {
                    const el = document.getElementById('chart-volume');
                    if (el) {
                        const existing = Chart.getChart?.(el);
                        if (existing) existing.destroy();
                        this.charts.volume = new Chart(el, {
                            type: 'line',
                            data: {
                                labels: vol.labels,
                                datasets: [
                                    { label: 'Created', data: vol.created, borderColor: '#14b8a6', backgroundColor: 'rgba(20,184,166,0.08)', fill: true, tension: 0.4, pointRadius: 2, borderWidth: 2 },
                                    { label: 'Resolved', data: vol.resolved, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.08)', fill: true, tension: 0.4, pointRadius: 2, borderWidth: 2 },
                                ],
                            },
                            options: { ...commonOptions, plugins: { ...commonOptions.plugins, legend: { display: true, position: 'top', labels: { color: textColor, usePointStyle: true, pointStyle: 'circle', padding: 16 } } }, scales: { x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } }, y: { grid: { color: gridColor }, ticks: { color: textColor, precision: 0 }, beginAtZero: true } } },
                        });
                    }
                }

                const st = cfg.statusBreakdown || {};
                if (st.labels?.length) {
                    const el = document.getElementById('chart-status');
                    if (el) {
                        const existing = typeof Chart !== 'undefined' && Chart.getChart ? Chart.getChart(el) : null;
                        if (existing) {
                            existing.data.labels = st.labels;
                            existing.data.datasets[0].data = st.values;
                            existing.data.datasets[0].backgroundColor = st.colors;
                            existing.update();
                            this.charts.status = existing;
                        } else {
                            this.charts.status = new Chart(el, {
                                type: 'doughnut',
                                data: { labels: st.labels, datasets: [{ data: st.values, backgroundColor: st.colors, borderWidth: 0, hoverOffset: 6 }] },
                                options: { ...commonOptions, cutout: '68%', plugins: { ...commonOptions.plugins, tooltip: { ...commonOptions.plugins.tooltip, callbacks: { label: ctx => `${ctx.label}: ${ctx.raw} tickets` } } }, onClick: (evt, elems) => { if (elems[0]) { this.$wire.applyChartFilter('status', st.keys[elems[0].index]); } } },
                            });
                        }
                    }
                }

                const pr = cfg.priorityBreakdown || {};
                if (pr.labels?.length) {
                    const el = document.getElementById('chart-priority');
                    if (el) {
                        const existing = Chart.getChart?.(el);
                        if (existing) existing.destroy();
                        this.charts.priority = new Chart(el, {
                            type: 'bar',
                            data: { labels: pr.labels, datasets: [{ data: pr.values, backgroundColor: pr.colors, borderRadius: 4, barPercentage: 0.6 }] },
                            options: { ...commonOptions, indexAxis: 'y', scales: { x: { grid: { color: gridColor }, ticks: { color: textColor, precision: 0 }, beginAtZero: true }, y: { grid: { display: false }, ticks: { color: textColor } } }, onClick: (evt, elems) => { if (elems[0]) { this.$wire.applyChartFilter('priority', pr.keys[elems[0].index]); } } },
                        });
                    }
                }

                const cv = cfg.categoryVolume || {};
                if (cv.labels?.length) {
                    const el = document.getElementById('chart-category-vol');
                    if (el) {
                        const existing = Chart.getChart?.(el);
                        if (existing) existing.destroy();
                        this.charts.catVol = new Chart(el, {
                            type: 'bar',
                            data: { labels: cv.labels, datasets: [{ data: cv.values, backgroundColor: 'rgba(20,184,166,0.6)', borderRadius: 4, barPercentage: 0.6 }] },
                            options: { ...commonOptions, indexAxis: 'y', scales: { x: { grid: { color: gridColor }, ticks: { color: textColor, precision: 0 }, beginAtZero: true }, y: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } } }, onClick: (evt, elems) => { if (elems[0]) { this.$wire.applyChartFilter('category', cv.keys[elems[0].index]); } } },
                        });
                    }
                }
            }

            if (cfg.activeTab === 'agents' && cfg.selectedAgentData) {
                const ad = cfg.selectedAgentData;
                if (ad.daily_labels?.length) {
                    const el = document.getElementById('chart-agent-daily');
                    if (el) {
                        const existing = Chart.getChart?.(el);
                        if (existing) existing.destroy();
                        this.charts.agentDaily = new Chart(el, {
                            type: 'line',
                            data: { labels: ad.daily_labels, datasets: [{ label: 'Resolved', data: ad.daily_resolved, borderColor: '#14b8a6', backgroundColor: 'rgba(20,184,166,0.1)', fill: true, tension: 0.4, pointRadius: 2, borderWidth: 2 }] },
                            options: { ...commonOptions, scales: { x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } }, y: { grid: { color: gridColor }, ticks: { color: textColor, precision: 0 }, beginAtZero: true } } },
                        });
                    }
                }
                if (ad.category_labels?.length) {
                    const el = document.getElementById('chart-agent-cats');
                    if (el) {
                        const existing = Chart.getChart?.(el);
                        if (existing) existing.destroy();
                        this.charts.agentCats = new Chart(el, {
                            type: 'bar',
                            data: { labels: ad.category_labels, datasets: [{ data: ad.category_values, backgroundColor: 'rgba(20,184,166,0.6)', borderRadius: 4, barPercentage: 0.6 }] },
                            options: { ...commonOptions, indexAxis: 'y', scales: { x: { grid: { color: gridColor }, ticks: { color: textColor, precision: 0 }, beginAtZero: true }, y: { grid: { display: false }, ticks: { color: textColor } } } },
                        });
                    }
                }
            }
        },

        async exportPdf() {
    this.exportingPdf = true;

    const pdfContent = document.getElementById('reports-pdf-content');
    if (!pdfContent) {
        this.exportingPdf = false;
        alert('PDF content not found.');
        return;
    }

    pdfContent.classList.add('pdf-exporting');
    await this.$nextTick();
    pdfContent.scrollIntoView({ behavior: 'instant', block: 'start' });
    await new Promise(r => setTimeout(r, 350));

    try {
        const canvas = await html2canvas(pdfContent, {
            backgroundColor: '#ffffff',
            scale: 1.5,
            useCORS: true,
            logging: false,
            scrollX: -window.scrollX,
            scrollY: -window.scrollY,
            ignoreElements: (el) => el.classList?.contains('pdf-exclude') || el.classList?.contains('export-overlay'),
            onclone: (clonedDoc, clonedNode) => {
                clonedNode.querySelectorAll('canvas').forEach((clonedCanvas) => {
                    const id = clonedCanvas.id;
                    if (id) {
                        const original = document.getElementById(id);
                        if (original && original !== clonedCanvas) {
                            const ctx = clonedCanvas.getContext('2d');
                            if (ctx) {
                                ctx.drawImage(original, 0, 0);
                            }
                        }
                    }
                });
            },
        });

        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

        const pageW = pdf.internal.pageSize.getWidth();
        const pageH = pdf.internal.pageSize.getHeight();
        const imgW = pageW - 20;
        const imgH = (canvas.height * imgW) / canvas.width;

        let remaining = imgH;
        let srcY = 0;
        let firstPage = true;

        while (remaining > 0) {
            if (!firstPage) pdf.addPage();
            firstPage = false;

            const sliceH = Math.min(remaining, pageH - 20);
            const srcSliceH = (sliceH / imgH) * canvas.height;

            const slice = document.createElement('canvas');
            slice.width = canvas.width;
            slice.height = Math.ceil(srcSliceH);
            slice.getContext('2d').drawImage(
                canvas, 0, srcY, canvas.width, srcSliceH,
                0, 0, canvas.width, srcSliceH
            );

            pdf.addImage(slice.toDataURL('image/png'), 'PNG', 10, 10, imgW, sliceH);
            remaining -= sliceH;
            srcY += srcSliceH;
        }

        const tab = this.$wire?.activeTab ?? 'overview';
        const start = this.$wire?.startDate ?? '';
        const end = this.$wire?.endDate ?? '';
        pdf.save(`helpdesk-${tab}-${start}-to-${end}.pdf`);

    } catch (e) {
        console.error('PDF export failed:', e);
        alert('PDF generation failed: ' + e.message);
    } finally {
        pdfContent.classList.remove('pdf-exporting');
        this.exportingPdf = false;
    }
},
    }));

    Alpine.data('tiptapEditor', () => {
        let editor;

        return {
            updatedAt: Date.now(),

            init() {
                editor = new Editor({
                    element: this.$refs.editorEl,
                    extensions: [
                        StarterKit,
                        Underline,
                        Link.configure({ openOnClick: false }),
                    ],
                    content: '',
                    editorProps: {
                        attributes: {
                            class: 'prose prose-sm prose-invert focus:outline-none max-w-none min-h-[120px] px-3 py-2 text-zinc-200',
                        },
                    },
                    onUpdate: ({ editor: e }) => {
                        this.$wire.set('message', e.getHTML());
                        this.updatedAt = Date.now();
                    },
                    onSelectionUpdate: () => {
                        this.updatedAt = Date.now();
                    },
                    onTransaction: () => {
                        this.updatedAt = Date.now();
                    }
                });

                window.addEventListener('resetEditor', () => {
                    editor.commands.setContent('');
                    this.content = '';
                });

                window.addEventListener('loadAiSuggestion', (event) => {
                    const text = event.detail[0]?.content || event.detail?.content || '';
                    editor.commands.setContent(text);
                    editor.commands.focus();
                });
            },

            destroy() {
                if (editor) editor.destroy();
            },

            isActive(type, options = {}) {
                this.updatedAt; // Reactive dependency
                return editor ? editor.isActive(type, options) : false;
            },

            bold() { editor?.chain().focus().toggleBold().run(); },
            italic() { editor?.chain().focus().toggleItalic().run(); },
            underline() { editor?.chain().focus().toggleUnderline().run(); },
            bulletList() { editor?.chain().focus().toggleBulletList().run(); },
            orderedList() { editor?.chain().focus().toggleOrderedList().run(); },
            codeBlock() { editor?.chain().focus().toggleCodeBlock().run(); },
            getLinkUrl() { return editor?.getAttributes('link').href || ''; },
            setLink(url) {
                if (url) {
                    editor?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
                } else {
                    editor?.chain().focus().extendMarkRange('link').unsetLink().run();
                }
            },
        };
    });
});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';