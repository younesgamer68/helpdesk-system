import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';
import html2canvas from 'html2canvas';
import { jsPDF } from 'jspdf';
window.html2canvas = html2canvas;
window.jspdf = { jsPDF };

document.addEventListener('alpine:init', () => {
    Alpine.data("reportsCharts", (config) => ({
        charts: {},
        exportingPdf: false,

        init() {
            this.destroyCharts();
            this.$nextTick(() => setTimeout(() => this.initCharts(config), 80));
        },

        destroyCharts() {
            Object.values(this.charts).forEach((ch) => {
                try {
                    ch?.destroy?.();
                } catch (e) {}
            });
            this.charts = {};
        },

        initCharts(cfg) {
            if (typeof window.Chart === "undefined") return;
            const Chart = window.Chart;
            const gridColor = "rgba(113, 113, 122, 0.4)";
            const textColor = "#a1a1aa";
            const tooltipBg = "#18181b";
            const tooltipBorder = "#3f3f46";

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 600, easing: "easeOutQuart" },
                plugins: {
                    tooltip: {
                        backgroundColor: tooltipBg,
                        borderColor: tooltipBorder,
                        borderWidth: 1,
                        titleColor: "#fff",
                        bodyColor: "#d4d4d8",
                        padding: 10,
                        cornerRadius: 8,
                    },
                    legend: { display: false },
                },
            };

            if (cfg.activeTab === "overview") {
                const vol = cfg.ticketVolume || {};
                if (vol.labels?.length) {
                    const el = document.getElementById("chart-volume");
                    if (el) {
                        this.charts.volume = new Chart(el, {
                            type: "line",
                            data: {
                                labels: vol.labels,
                                datasets: [
                                    {
                                        label: "Created",
                                        data: vol.created,
                                        borderColor: "#14b8a6",
                                        backgroundColor:
                                            "rgba(20,184,166,0.08)",
                                        fill: true,
                                        tension: 0.4,
                                        pointRadius: 2,
                                        borderWidth: 2,
                                    },
                                    {
                                        label: "Resolved",
                                        data: vol.resolved,
                                        borderColor: "#22c55e",
                                        backgroundColor: "rgba(34,197,94,0.08)",
                                        fill: true,
                                        tension: 0.4,
                                        pointRadius: 2,
                                        borderWidth: 2,
                                    },
                                ],
                            },
                            options: {
                                ...commonOptions,
                                plugins: {
                                    ...commonOptions.plugins,
                                    legend: {
                                        display: true,
                                        position: "top",
                                        labels: {
                                            color: textColor,
                                            usePointStyle: true,
                                            pointStyle: "circle",
                                            padding: 16,
                                        },
                                    },
                                },
                                scales: {
                                    x: {
                                        grid: { color: gridColor },
                                        ticks: {
                                            color: textColor,
                                            font: { size: 11 },
                                        },
                                    },
                                    y: {
                                        grid: { color: gridColor },
                                        ticks: {
                                            color: textColor,
                                            precision: 0,
                                        },
                                        beginAtZero: true,
                                    },
                                },
                            },
                        });
                    }
                }

                const st = cfg.statusBreakdown || {};
                if (st.labels?.length) {
                    const el = document.getElementById("chart-status");
                    if (el) {
                        this.charts.status = new Chart(el, {
                            type: "doughnut",
                            data: {
                                labels: st.labels,
                                datasets: [
                                    {
                                        data: st.values,
                                        backgroundColor: st.colors,
                                        borderWidth: 0,
                                        hoverOffset: 6,
                                    },
                                ],
                            },
                            options: {
                                ...commonOptions,
                                cutout: "68%",
                                plugins: {
                                    ...commonOptions.plugins,
                                    tooltip: {
                                        ...commonOptions.plugins.tooltip,
                                        callbacks: {
                                            label: (ctx) =>
                                                `${ctx.label}: ${ctx.raw} tickets`,
                                        },
                                    },
                                },
                                onClick: (evt, elems) => {
                                    if (elems[0]) {
                                        this.$wire.applyChartFilter(
                                            "status",
                                            st.keys[elems[0].index],
                                        );
                                    }
                                },
                            },
                        });
                    }
                }

                const pr = cfg.priorityBreakdown || {};
                if (pr.labels?.length) {
                    const el = document.getElementById("chart-priority");
                    if (el) {
                        this.charts.priority = new Chart(el, {
                            type: "bar",
                            data: {
                                labels: pr.labels,
                                datasets: [
                                    {
                                        data: pr.values,
                                        backgroundColor: pr.colors,
                                        borderRadius: 4,
                                        barPercentage: 0.6,
                                    },
                                ],
                            },
                            options: {
                                ...commonOptions,
                                indexAxis: "y",
                                scales: {
                                    x: {
                                        grid: { color: gridColor },
                                        ticks: {
                                            color: textColor,
                                            precision: 0,
                                        },
                                        beginAtZero: true,
                                    },
                                    y: {
                                        grid: { display: false },
                                        ticks: { color: textColor },
                                    },
                                },
                                onClick: (evt, elems) => {
                                    if (elems[0]) {
                                        this.$wire.applyChartFilter(
                                            "priority",
                                            pr.keys[elems[0].index],
                                        );
                                    }
                                },
                            },
                        });
                    }
                }

                const cv = cfg.categoryVolume || {};
                if (cv.labels?.length) {
                    const el = document.getElementById("chart-category-vol");
                    if (el) {
                        this.charts.catVol = new Chart(el, {
                            type: "bar",
                            data: {
                                labels: cv.labels,
                                datasets: [
                                    {
                                        data: cv.values,
                                        backgroundColor: "rgba(20,184,166,0.6)",
                                        borderRadius: 4,
                                        barPercentage: 0.6,
                                    },
                                ],
                            },
                            options: {
                                ...commonOptions,
                                indexAxis: "y",
                                scales: {
                                    x: {
                                        grid: { color: gridColor },
                                        ticks: {
                                            color: textColor,
                                            precision: 0,
                                        },
                                        beginAtZero: true,
                                    },
                                    y: {
                                        grid: { display: false },
                                        ticks: {
                                            color: textColor,
                                            font: { size: 11 },
                                        },
                                    },
                                },
                                onClick: (evt, elems) => {
                                    if (elems[0]) {
                                        this.$wire.applyChartFilter(
                                            "category",
                                            cv.keys[elems[0].index],
                                        );
                                    }
                                },
                            },
                        });
                    }
                }
            }

            if (cfg.activeTab === "agents" && cfg.selectedAgentData) {
                const ad = cfg.selectedAgentData;
                if (ad.daily_labels?.length) {
                    const el = document.getElementById("chart-agent-daily");
                    if (el) {
                        this.charts.agentDaily = new Chart(el, {
                            type: "line",
                            data: {
                                labels: ad.daily_labels,
                                datasets: [
                                    {
                                        label: "Resolved",
                                        data: ad.daily_resolved,
                                        borderColor: "#14b8a6",
                                        backgroundColor: "rgba(20,184,166,0.1)",
                                        fill: true,
                                        tension: 0.4,
                                        pointRadius: 2,
                                        borderWidth: 2,
                                    },
                                ],
                            },
                            options: {
                                ...commonOptions,
                                scales: {
                                    x: {
                                        grid: { color: gridColor },
                                        ticks: {
                                            color: textColor,
                                            font: { size: 11 },
                                        },
                                    },
                                    y: {
                                        grid: { color: gridColor },
                                        ticks: {
                                            color: textColor,
                                            precision: 0,
                                        },
                                        beginAtZero: true,
                                    },
                                },
                            },
                        });
                    }
                }
                if (ad.category_labels?.length) {
                    const el = document.getElementById("chart-agent-cats");
                    if (el) {
                        this.charts.agentCats = new Chart(el, {
                            type: "bar",
                            data: {
                                labels: ad.category_labels,
                                datasets: [
                                    {
                                        data: ad.category_values,
                                        backgroundColor: "rgba(20,184,166,0.6)",
                                        borderRadius: 4,
                                        barPercentage: 0.6,
                                    },
                                ],
                            },
                            options: {
                                ...commonOptions,
                                indexAxis: "y",
                                scales: {
                                    x: {
                                        grid: { color: gridColor },
                                        ticks: {
                                            color: textColor,
                                            precision: 0,
                                        },
                                        beginAtZero: true,
                                    },
                                    y: {
                                        grid: { display: false },
                                        ticks: { color: textColor },
                                    },
                                },
                            },
                        });
                    }
                }
            }
        },

            exportPdf() {
                this.exportingPdf = true;
                setTimeout(() => {
                    window.print();
                    this.exportingPdf = false;
                }, 300);
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
