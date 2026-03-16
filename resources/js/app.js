import { Editor } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import Underline from "@tiptap/extension-underline";
import Link from "@tiptap/extension-link";
import Image from "@tiptap/extension-image";
import html2canvas from "html2canvas-oklch";
import { jsPDF } from "jspdf";
window.html2canvas = html2canvas;
window.jspdf = { jsPDF };

document.addEventListener("livewire:init", () => {
    Livewire.hook("morph.morphed", ({ el, component }) => {
        const isReports =
            el?.id === "reports-page" ||
            el?.closest?.("#reports-page") ||
            component?.name?.includes?.("reports-analytics");
        if (isReports) {
            window.dispatchEvent(new CustomEvent("reports-charts-refresh"));
        }
    });
});

document.addEventListener("alpine:init", () => {
    Alpine.data("reportsCharts", (config) => ({
        charts: {},
        exportingPdf: false,

        init() {
            this.destroyCharts();
            this.$nextTick(() => setTimeout(() => this.initCharts(config), 80));

            const refreshCharts = () => {
                if (this.$wire) {
                    this.$wire.getChartConfig().then((cfg) => {
                        this.destroyCharts(cfg?.activeTab);
                        this.$nextTick(() =>
                            setTimeout(() => this.initCharts(cfg), 50),
                        );
                    });
                }
            };

            const handleRefresh = () => refreshCharts();
            window.addEventListener("reports-charts-refresh", handleRefresh);
            if (typeof this.$cleanup === "function") {
                this.$cleanup(() =>
                    window.removeEventListener(
                        "reports-charts-refresh",
                        handleRefresh,
                    ),
                );
            }
        },

        destroyCharts(preserveStatusForTab) {
            const keepStatus =
                preserveStatusForTab === "overview" && this.charts.status;
            Object.entries(this.charts).forEach(([key, ch]) => {
                if (key === "status" && keepStatus) return;
                try {
                    ch?.destroy?.();
                } catch (e) {}
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
                        const existing = Chart.getChart?.(el);
                        if (existing) existing.destroy();
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
                        const existing =
                            typeof Chart !== "undefined" && Chart.getChart
                                ? Chart.getChart(el)
                                : null;
                        if (existing) {
                            existing.data.labels = st.labels;
                            existing.data.datasets[0].data = st.values;
                            existing.data.datasets[0].backgroundColor =
                                st.colors;
                            existing.update();
                            this.charts.status = existing;
                        } else {
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
                }

                const pr = cfg.priorityBreakdown || {};
                if (pr.labels?.length) {
                    const el = document.getElementById("chart-priority");
                    if (el) {
                        const existing = Chart.getChart?.(el);
                        if (existing) existing.destroy();
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
                        const existing = Chart.getChart?.(el);
                        if (existing) existing.destroy();
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
                        const existing = Chart.getChart?.(el);
                        if (existing) existing.destroy();
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
                        const existing = Chart.getChart?.(el);
                        if (existing) existing.destroy();
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

        async exportPdf() {
            this.exportingPdf = true;

            const pdfContent = document.getElementById("reports-pdf-content");
            if (!pdfContent) {
                this.exportingPdf = false;
                return;
            }

            window.scrollTo({ top: 0, behavior: "instant" });
            await new Promise((r) => setTimeout(r, 300));

            const wrapper = document.createElement("div");
            wrapper.style.cssText = `position:fixed;top:0;left:-99999px;width:${pdfContent.offsetWidth}px;z-index:-1;pointer-events:none;`;

            const removedFluxEls = [];
            const fluxTags = [
                "ui-dropdown",
                "ui-select",
                "ui-options",
                "ui-option",
                "ui-checkbox",
                "ui-radio",
                "ui-button",
            ];

            const colorCache = new Map();
            const resolveCanvas = document.createElement("canvas");
            resolveCanvas.width = resolveCanvas.height = 1;
            const resolveCtx = resolveCanvas.getContext("2d");

            function toRgb(cssColor) {
                if (!cssColor) return null;
                if (
                    !cssColor.includes("oklab") &&
                    !cssColor.includes("oklch") &&
                    !cssColor.includes("color-mix")
                )
                    return null;
                if (colorCache.has(cssColor)) return colorCache.get(cssColor);
                try {
                    resolveCtx.clearRect(0, 0, 1, 1);
                    resolveCtx.fillStyle = "#000";
                    resolveCtx.fillStyle = cssColor;
                    resolveCtx.fillRect(0, 0, 1, 1);
                    const [r, g, b, a] = resolveCtx.getImageData(
                        0,
                        0,
                        1,
                        1,
                    ).data;
                    const result =
                        a < 255
                            ? `rgba(${r},${g},${b},${(a / 255).toFixed(3)})`
                            : `rgb(${r},${g},${b})`;
                    colorCache.set(cssColor, result);
                    return result;
                } catch (e) {
                    return null;
                }
            }

            function stampColors(liveEl, cloneEl) {
                if (liveEl.nodeType !== 1 || cloneEl.nodeType !== 1) return;
                const cs = window.getComputedStyle(liveEl);
                [
                    "color",
                    "background-color",
                    "border-top-color",
                    "border-right-color",
                    "border-bottom-color",
                    "border-left-color",
                ].forEach((prop) => {
                    const val = cs.getPropertyValue(prop);
                    const rgb = toRgb(val);
                    if (rgb) cloneEl.style.setProperty(prop, rgb, "important");
                });
                const liveKids = liveEl.children;
                const cloneKids = cloneEl.children;
                for (let i = 0; i < liveKids.length; i++) {
                    if (cloneKids[i]) stampColors(liveKids[i], cloneKids[i]);
                }
            }

            try {
                const clone = pdfContent.cloneNode(true);

                fluxTags.forEach((tag) =>
                    clone.querySelectorAll(tag).forEach((el) => el.remove()),
                );
                clone
                    .querySelectorAll(
                        "[data-flux-dropdown],[data-flux-select],[data-flux-field]",
                    )
                    .forEach((el) => el.remove());
                clone.querySelectorAll("[x-data],[x-init]").forEach((el) => {
                    el.removeAttribute("x-data");
                    el.removeAttribute("x-init");
                });

                const liveCanvases = Array.from(
                    pdfContent.querySelectorAll("canvas"),
                );
                const cloneCanvases = Array.from(
                    clone.querySelectorAll("canvas"),
                );
                liveCanvases.forEach((live, i) => {
                    const cloned = cloneCanvases[i];
                    if (!cloned || live.width === 0 || live.height === 0)
                        return;
                    cloned.width = live.width;
                    cloned.height = live.height;
                    cloned.style.width = live.offsetWidth + "px";
                    cloned.style.height = live.offsetHeight + "px";
                    const ctx = cloned.getContext("2d");
                    if (ctx) ctx.drawImage(live, 0, 0);
                });

                wrapper.appendChild(clone);
                document.body.appendChild(wrapper);
                await new Promise((r) => setTimeout(r, 100));

                stampColors(pdfContent, clone);

                clone.style.cssText = `background:#ffffff;color:#171717;overflow:visible;width:${pdfContent.offsetWidth}px;`;

                fluxTags.forEach((tag) => {
                    document.querySelectorAll(tag).forEach((el) => {
                        if (!el.parentNode) return;
                        const marker = document.createComment("__flux_temp__");
                        el.parentNode.replaceChild(marker, el);
                        removedFluxEls.push({ el, marker });
                    });
                });

                const canvas = await html2canvas(clone, {
                    backgroundColor: "#ffffff",
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    scrollX: 0,
                    scrollY: 0,
                    windowWidth: pdfContent.offsetWidth,
                });

                removedFluxEls.forEach(({ el, marker }) => {
                    if (marker.parentNode)
                        marker.parentNode.replaceChild(el, marker);
                });
                removedFluxEls.length = 0;

                document.body.removeChild(wrapper);

                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF({
                    orientation: "portrait",
                    unit: "mm",
                    format: "a4",
                });
                const pageW = pdf.internal.pageSize.getWidth();
                const pageH = pdf.internal.pageSize.getHeight();
                const margin = 10;
                const imgW = pageW - margin * 2;
                const imgH = (canvas.height * imgW) / canvas.width;

                let remaining = imgH;
                let srcY = 0;
                let firstPage = true;

                while (remaining > 0) {
                    if (!firstPage) pdf.addPage();
                    firstPage = false;
                    const sliceH = Math.min(remaining, pageH - margin * 2);
                    const srcSliceH = (sliceH / imgH) * canvas.height;
                    const slice = document.createElement("canvas");
                    slice.width = canvas.width;
                    slice.height = Math.ceil(srcSliceH);
                    const sliceCtx = slice.getContext("2d");
                    sliceCtx.fillStyle = "#ffffff";
                    sliceCtx.fillRect(0, 0, slice.width, slice.height);
                    sliceCtx.drawImage(
                        canvas,
                        0,
                        srcY,
                        canvas.width,
                        srcSliceH,
                        0,
                        0,
                        canvas.width,
                        srcSliceH,
                    );
                    pdf.addImage(
                        slice.toDataURL("image/png"),
                        "PNG",
                        margin,
                        margin,
                        imgW,
                        sliceH,
                    );
                    remaining -= sliceH;
                    srcY += srcSliceH;
                }

                const tab = this.$wire?.activeTab ?? "overview";
                const start = this.$wire?.startDate ?? "";
                const end = this.$wire?.endDate ?? "";
                pdf.save(`helpdesk-${tab}-${start}-to-${end}.pdf`);
            } catch (e) {
                removedFluxEls.forEach(({ el, marker }) => {
                    if (marker.parentNode)
                        marker.parentNode.replaceChild(el, marker);
                });
                if (document.body.contains(wrapper))
                    document.body.removeChild(wrapper);
                console.error("PDF export failed:", e);
                alert("PDF generation failed: " + e.message);
            } finally {
                this.exportingPdf = false;
            }
        },
    }));

    Alpine.data("tiptapEditor", (config = {}) => {
        let editor;
        const model = config.model || "message";

        return {
            updatedAt: Date.now(),
            _pendingSync: 0, // ← counter to block $watch from resetting editor

            init() {
                const initialContent = this.$wire?.get
                    ? this.$wire.get(model)
                    : "";

                editor = new Editor({
                    element: this.$refs.editorEl,
                    extensions: [
                        StarterKit,
                        Underline,
                        Link.configure({ openOnClick: false }),
                        Image.configure({ inline: true, allowBase64: true }),
                    ],
                    content: initialContent || "",
                    editorProps: {
                        attributes: {
                            class: "prose prose-sm prose-invert focus:outline-none max-w-none min-h-[120px] px-3 py-2 text-zinc-200",
                        },
                    },
                    onUpdate: ({ editor: e }) => {
                        if (this.$wire?.set) {
                            this._pendingSync++;
                            this.$wire.set(model, e.getHTML());
                        }
                        this.updatedAt = Date.now();
                    },
                    onSelectionUpdate: () => {
                        this.updatedAt = Date.now();
                    },
                    onTransaction: () => {
                        this.updatedAt = Date.now();
                    },
                });

                if (this.$wire?.get) {
                    this.$watch(
                        () => this.$wire.get(model),
                        (value) => {
                            if (this._pendingSync > 0) {
                                this._pendingSync--;
                                return;
                            }
                            const html = value ?? "";
                            if (!editor || editor.getHTML() === html) return;
                            editor.commands.setContent(html, false);
                        },
                    );
                }

                window.addEventListener("resetEditor", () => {
                    editor.commands.setContent("");
                    if (this.$wire?.set) {
                        this.$wire.set(model, "");
                    }
                });

                window.addEventListener("loadAiSuggestion", (event) => {
                    const text =
                        event.detail[0]?.content || event.detail?.content || "";
                    editor.commands.setContent(text);
                    editor.commands.focus();
                });

                window.addEventListener("media-selected", (event) => {
                    const payload = event.detail?.[0] || event.detail || {};
                    const imageUrls = Array.isArray(payload.urls)
                        ? payload.urls
                        : payload.url
                          ? [payload.url]
                          : [];

                    if (imageUrls.length === 0 || !editor) {
                        return;
                    }

                    const markup = imageUrls
                        .map((imageUrl) => {
                            const safeUrl = String(imageUrl).replace(
                                /"/g,
                                "&quot;",
                            );
                            return `<img src="${safeUrl}" alt="" />`;
                        })
                        .join("");

                    editor.chain().focus().insertContent(markup).run();

                    if (this.$wire?.set) {
                        this._pendingSync++;
                        this.$wire.set(model, editor.getHTML());
                    }
                });
            },

            destroy() {
                if (editor) editor.destroy();
            },

            removeImage() {
                editor?.chain().focus().deleteSelection().run();
            },

            isActive(type, options = {}) {
                this.updatedAt;
                return editor ? editor.isActive(type, options) : false;
            },

            bold() {
                editor?.chain().focus().toggleBold().run();
            },
            italic() {
                editor?.chain().focus().toggleItalic().run();
            },
            underline() {
                editor?.chain().focus().toggleUnderline().run();
            },
            bulletList() {
                editor?.chain().focus().toggleBulletList().run();
            },
            orderedList() {
                editor?.chain().focus().toggleOrderedList().run();
            },
            codeBlock() {
                editor?.chain().focus().toggleCodeBlock().run();
            },
            getLinkUrl() {
                return editor?.getAttributes("link").href || "";
            },
            setLink(url) {
                if (url) {
                    editor
                        ?.chain()
                        .focus()
                        .extendMarkRange("link")
                        .setLink({ href: url })
                        .run();
                } else {
                    editor
                        ?.chain()
                        .focus()
                        .extendMarkRange("link")
                        .unsetLink()
                        .run();
                }
            },
        };
    });
});

import "./echo";
