/**
 * ============================================================================
 * 🎨 CANVA-GRADE CUSTOM PHOTO CARD & AI BG REMOVER EDITOR ENGINE
 * Built with Fabric.js, Sortable.js, Canvas HTML5, and PhotoRoom Backend API
 * ============================================================================
 */

(function (window) {
    'use strict';

    class CustomPhotoCardEngine {
        constructor(config) {
            this.config = Object.assign({
                canvasId: 'customCardCanvas',
                workspaceContainerId: 'workspace-container',
                canvasWrapperId: 'canvas-wrapper',
                removeBgUrl: '/studio/custom-photo-card/remove-bg',
                uploadFrameUrl: '/studio/custom-photo-card/upload-frame',
                saveCardUrl: '/studio/custom-photo-card/save',
                csrfToken: '',
                initialWidth: 1080,
                initialHeight: 1080,
                newsData: null,
            }, config);

            this.canvas = null;
            this.activeFrame = null;
            this.zoomLevel = 1;
            this.history = [];
            this.historyIndex = -1;
            this.isHistoryProcessing = false;
            this.maxHistory = 30;
            this.snappingDistance = 8;
            this.guidelines = { x: null, y: null };
            this.extractedColors = [];

            this.init();
        }

        /**
         * Initialize the Fabric Canvas and setup event listeners
         */
        init() {
            const canvasEl = document.getElementById(this.config.canvasId);
            if (!canvasEl) {
                console.error("Canvas element not found: " + this.config.canvasId);
                return;
            }

            // Initialize Fabric Canvas with responsive HD rendering
            this.canvas = new fabric.Canvas(this.config.canvasId, {
                width: this.config.initialWidth,
                height: this.config.initialHeight,
                backgroundColor: '#ffffff',
                preserveObjectStacking: true,
                selectionColor: 'rgba(99, 102, 241, 0.15)',
                selectionBorderColor: '#6366f1',
                selectionLineWidth: 1.5,
                stopContextMenu: true,
                fireRightClick: true,
            });

            // Enhance Fabric Controls (Canva style pill handles)
            this.setupCustomControls();

            // Event Listeners
            this.setupCanvasEvents();
            this.setupSnappingGuides();
            this.setupKeyboardShortcuts();
            this.setupContextMenu();

            // Initial fit
            this.fitToScreen();
            window.addEventListener('resize', () => this.fitToScreen());

            // Save initial state
            this.saveState();

            // If news data provided, load headline & image
            if (this.config.newsData) {
                this.loadNewsData(this.config.newsData);
            }

            console.log("✅ Custom Photo Card Studio Engine Initialized.");
        }

        /**
         * Custom Canva-style circular handles and styling
         */
        setupCustomControls() {
            fabric.Object.prototype.transparentCorners = false;
            fabric.Object.prototype.cornerColor = '#ffffff';
            fabric.Object.prototype.cornerStrokeColor = '#6366f1';
            fabric.Object.prototype.borderColor = '#6366f1';
            fabric.Object.prototype.cornerSize = 10;
            fabric.Object.prototype.cornerStyle = 'circle';
            fabric.Object.prototype.borderScaleFactor = 2;
            fabric.Object.prototype.borderDashArray = [4, 4];
        }

        /**
         * ====================================================================
         * 📐 DYNAMIC FRAME NATURAL SIZING (NO 1:1 FORCING)
         * ====================================================================
         */
        applyFrame(frameUrl, isCustomUpload = false) {
            if (!frameUrl) return;
            this.showLoader("ফ্রেম লোড হচ্ছে...");

            const img = new Image();
            img.crossOrigin = "anonymous";
            img.onload = () => {
                const naturalW = img.naturalWidth || img.width;
                const naturalH = img.naturalHeight || img.height;

                console.log(`🖼️ Applying Frame: ${naturalW}x${naturalH}`);

                // Update canvas internal dimensions dynamically
                this.setCanvasDimensions(naturalW, naturalH);

                // Set as Top Overlay Image
                fabric.Image.fromURL(frameUrl, (fabricImg) => {
                    fabricImg.set({
                        originX: 'left',
                        originY: 'top',
                        left: 0,
                        top: 0,
                        scaleX: naturalW / fabricImg.width,
                        scaleY: naturalH / fabricImg.height,
                        selectable: false,
                        evented: false,
                        excludeFromExport: false,
                    });

                    this.canvas.setOverlayImage(fabricImg, () => {
                        this.activeFrame = frameUrl;
                        this.canvas.renderAll();
                        this.hideLoader();
                        this.fitToScreen();
                        this.saveState();
                        this.updateDimensionBadges(naturalW, naturalH);
                        this.showNotification("success", `ফ্রেম সফলভাবে অ্যাপ্লাই হয়েছে (${naturalW}×${naturalH}px)`);
                    });
                }, { crossOrigin: 'anonymous' });
            };

            img.onerror = () => {
                this.hideLoader();
                this.showNotification("error", "ফ্রেম লোড করতে ব্যর্থ হয়েছে।");
            };

            img.src = frameUrl;
        }

        /**
         * Remove Overlay Frame
         */
        removeFrame() {
            this.canvas.setOverlayImage(null, () => {
                this.activeFrame = null;
                this.canvas.renderAll();
                this.saveState();
                this.showNotification("info", "ফ্রেম রিমুভ করা হয়েছে।");
            });
        }

        /**
         * Change Canvas Resolution
         */
        setCanvasDimensions(width, height) {
            this.canvas.setWidth(width);
            this.canvas.setHeight(height);
            this.fitToScreen();
            this.updateDimensionBadges(width, height);
        }

        /**
         * Responsive Viewport Fit to Screen (Visual Scale without lowering internal resolution)
         */
        fitToScreen() {
            const container = document.getElementById(this.config.workspaceContainerId);
            const wrapper = document.getElementById(this.config.canvasWrapperId);
            if (!container || !wrapper) return;

            const padding = 32;
            const containerW = container.clientWidth - padding;
            const containerH = container.clientHeight - padding;

            const canvasW = this.canvas.getWidth();
            const canvasH = this.canvas.getHeight();

            const scaleX = containerW / canvasW;
            const scaleY = containerH / canvasH;
            const scale = Math.min(scaleX, scaleY, 1); // Fit without overflowing

            this.zoomLevel = scale;
            wrapper.style.transform = `scale(${scale})`;
            wrapper.style.transformOrigin = 'center center';

            const zoomBadge = document.getElementById('zoom-level-badge');
            if (zoomBadge) {
                zoomBadge.innerText = Math.round(scale * 100) + '%';
            }
        }

        setZoom(delta) {
            this.zoomLevel = Math.max(0.1, Math.min(3.0, this.zoomLevel + delta));
            const wrapper = document.getElementById(this.config.canvasWrapperId);
            if (wrapper) {
                wrapper.style.transform = `scale(${this.zoomLevel})`;
            }
            const zoomBadge = document.getElementById('zoom-level-badge');
            if (zoomBadge) {
                zoomBadge.innerText = Math.round(this.zoomLevel * 100) + '%';
            }
        }

        updateDimensionBadges(w, h) {
            const badge = document.getElementById('canvas-dimensions-badge');
            if (badge) {
                badge.innerText = `${w} × ${h} px`;
            }
            const wInput = document.getElementById('custom-width-input');
            const hInput = document.getElementById('custom-height-input');
            if (wInput) wInput.value = w;
            if (hInput) hInput.value = h;
        }

        /**
         * ====================================================================
         * 🪄 PHOTOROOM AI BACKGROUND REMOVAL (WHITE-LABEL PROXY)
         * ====================================================================
         */
        async removeBackgroundActive() {
            const activeObj = this.canvas.getActiveObject();
            if (!activeObj || activeObj.type !== 'image') {
                this.showNotification("warning", "দয়া করে একটি ছবি সিলেক্ট করুন যার ব্যাকগ্রাউন্ড রিমুভ করতে চান।");
                return;
            }

            this.showLoader("AI ব্যাকগ্রাউন্ড রিমুভ হচ্ছে... অনুগ্রহ করে অপেক্ষা করুন");

            try {
                // 1. Export the active image as full-quality Base64 Data URL
                const tempCanvas = document.createElement('canvas');
                const element = activeObj.getElement();
                tempCanvas.width = element.naturalWidth || element.width;
                tempCanvas.height = element.naturalHeight || element.height;
                const ctx = tempCanvas.getContext('2d');
                ctx.drawImage(element, 0, 0);
                const imageDataUrl = tempCanvas.toDataURL('image/png', 1.0);

                // 2. Prepare payload for Laravel Proxy
                const formData = new FormData();
                formData.append('image', imageDataUrl);
                formData.append('_token', this.config.csrfToken);

                // 3. Post to backend
                const response = await fetch(this.config.removeBgUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'ব্যাকগ্রাউন্ড রিমুভ ব্যর্থ হয়েছে।');
                }

                // 4. In-place seamless image replacement (Retain X, Y, Scale, Angle, Z-Index)
                const cutoutUrl = data.output_url;
                const prevProps = {
                    left: activeObj.left,
                    top: activeObj.top,
                    scaleX: activeObj.scaleX,
                    scaleY: activeObj.scaleY,
                    angle: activeObj.angle,
                    flipX: activeObj.flipX,
                    flipY: activeObj.flipY,
                    originX: activeObj.originX,
                    originY: activeObj.originY,
                };
                const zIndex = this.canvas.getObjects().indexOf(activeObj);

                fabric.Image.fromURL(cutoutUrl, (newImg) => {
                    newImg.set(prevProps);
                    this.canvas.remove(activeObj);
                    this.canvas.insertAt(newImg, zIndex);
                    this.canvas.setActiveObject(newImg);
                    this.canvas.renderAll();

                    this.hideLoader();
                    this.saveState();
                    this.renderLayersList();
                    this.showNotification("success", "ব্যাকগ্রাউন্ড সফলভাবে রিমুভ হয়েছে!");

                    // Update UI credit balance
                    if (data.remaining_credits !== undefined) {
                        this.updateCreditBadge(data.remaining_credits, data.daily_used, data.daily_limit);
                    }
                }, { crossOrigin: 'anonymous' });

            } catch (err) {
                this.hideLoader();
                console.error("BG Remove Error:", err);
                this.showNotification("error", err.message || "ব্যাকগ্রাউন্ড রিমুভ করতে সমস্যা হয়েছে।");
            }
        }

        updateCreditBadge(credits, dailyUsed, dailyLimit) {
            const creditEl = document.getElementById('user-credits-display');
            if (creditEl) creditEl.innerText = credits;
            const limitEl = document.getElementById('user-daily-bg-display');
            if (limitEl && dailyLimit) limitEl.innerText = `${dailyUsed}/${dailyLimit}`;
        }

        /**
         * ====================================================================
         * 🖼️ PHOTO COLOR PALETTE EXTRACTOR
         * ====================================================================
         */
        extractColorsFromImage(imgElement) {
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = 50;
                canvas.height = 50;
                ctx.drawImage(imgElement, 0, 0, 50, 50);

                const data = ctx.getImageData(0, 0, 50, 50).data;
                const colorMap = {};

                for (let i = 0; i < data.length; i += 16) {
                    const r = data[i];
                    const g = data[i + 1];
                    const b = data[i + 2];
                    const a = data[i + 3];
                    if (a < 128) continue; // Skip transparent

                    // Group colors to find dominant tones
                    const roundedR = Math.round(r / 32) * 32;
                    const roundedG = Math.round(g / 32) * 32;
                    const roundedB = Math.round(b / 32) * 32;
                    const hex = this.rgbToHex(roundedR, roundedG, roundedB);
                    colorMap[hex] = (colorMap[hex] || 0) + 1;
                }

                // Sort top 5 colors
                const topColors = Object.entries(colorMap)
                    .sort((a, b) => b[1] - a[1])
                    .slice(0, 5)
                    .map(item => item[0]);

                this.extractedColors = topColors;
                this.renderPhotoColorPalette(topColors);
            } catch (e) {
                console.warn("Could not extract colors:", e);
            }
        }

        rgbToHex(r, g, b) {
            return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
        }

        renderPhotoColorPalette(colors) {
            const container = document.getElementById('photo-color-palette');
            if (!container || !colors.length) return;

            container.innerHTML = `
                <div class="mb-2">
                    <span class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                        🎨 Photo Colors (স্বয়ংক্রিয় কালার):
                    </span>
                    <div class="flex gap-1.5 mt-1">
                        ${colors.map(c => `
                            <button type="button" onclick="window.customStudio.applyColor('${c}')" 
                                class="w-6 h-6 rounded-md border border-slate-300 shadow-sm hover:scale-110 transition-transform" 
                                style="background-color: ${c};" title="${c}"></button>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        applyColor(colorHex) {
            const active = this.canvas.getActiveObject();
            if (!active) {
                this.canvas.setBackgroundColor(colorHex, () => this.canvas.renderAll());
                this.saveState();
                return;
            }

            if (active.type === 'i-text' || active.type === 'textbox' || active.type === 'text') {
                active.set('fill', colorHex);
            } else if (active.type === 'rect' || active.type === 'circle' || active.type === 'path') {
                active.set('fill', colorHex);
            }
            this.canvas.renderAll();
            this.saveState();
        }

        /**
         * ====================================================================
         * 🗂️ DRAG & DROP LAYER MANAGEMENT
         * ====================================================================
         */
        renderLayersList() {
            const container = document.getElementById('layers-sortable-list');
            if (!container) return;

            const objects = this.canvas.getObjects();
            if (objects.length === 0) {
                container.innerHTML = '<p class="text-xs text-slate-400 text-center py-4">কোনো উপাদান নেই</p>';
                return;
            }

            const active = this.canvas.getActiveObject();

            // Reverse order so top layer is at the top of the UI list
            let html = '';
            for (let i = objects.length - 1; i >= 0; i--) {
                const obj = objects[i];
                const isActive = (obj === active);
                const isLocked = obj.lockMovementX;
                const isVisible = obj.visible !== false;
                const name = obj.customName || this.getObjectName(obj);
                const icon = this.getObjectIcon(obj);

                html += `
                    <div class="layer-item flex items-center justify-between p-2 rounded-lg border text-xs transition-all ${isActive ? 'bg-indigo-50 border-indigo-300 font-bold' : 'bg-white border-slate-200 hover:bg-slate-50'}" data-index="${i}">
                        <div class="flex items-center gap-2 cursor-pointer flex-1 truncate" onclick="window.customStudio.selectLayer(${i})">
                            <span class="text-slate-400 cursor-grab layer-drag-handle">☰</span>
                            <span>${icon}</span>
                            <span class="truncate text-slate-700">${name}</span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" onclick="window.customStudio.toggleLayerVisibility(${i})" class="text-slate-400 hover:text-slate-700 p-1" title="${isVisible ? 'হাইড করুন' : 'দেখান'}">
                                ${isVisible ? '👁️' : '🙈'}
                            </button>
                            <button type="button" onclick="window.customStudio.toggleLayerLock(${i})" class="text-slate-400 hover:text-slate-700 p-1" title="${isLocked ? 'আনলক করুন' : 'লক করুন'}">
                                ${isLocked ? '🔒' : '🔓'}
                            </button>
                            <button type="button" onclick="window.customStudio.deleteLayer(${i})" class="text-red-400 hover:text-red-600 p-1" title="ডিলিট">
                                🗑️
                            </button>
                        </div>
                    </div>
                `;
            }

            container.innerHTML = html;
            this.initSortableLayers();
        }

        initSortableLayers() {
            const container = document.getElementById('layers-sortable-list');
            if (!container || !window.Sortable) return;

            if (this.sortableInstance) {
                this.sortableInstance.destroy();
            }

            this.sortableInstance = new window.Sortable(container, {
                handle: '.layer-drag-handle',
                animation: 150,
                onEnd: (evt) => {
                    const total = this.canvas.getObjects().length;
                    const oldIndex = total - 1 - evt.oldIndex;
                    const newIndex = total - 1 - evt.newIndex;

                    const obj = this.canvas.getObjects()[oldIndex];
                    if (obj) {
                        this.canvas.moveTo(obj, newIndex);
                        this.canvas.renderAll();
                        this.saveState();
                        this.renderLayersList();
                    }
                }
            });
        }

        selectLayer(index) {
            const objects = this.canvas.getObjects();
            const obj = objects[index];
            if (obj && obj.selectable !== false) {
                this.canvas.setActiveObject(obj);
                this.canvas.renderAll();
                this.renderLayersList();
            }
        }

        toggleLayerVisibility(index) {
            const obj = this.canvas.getObjects()[index];
            if (obj) {
                obj.set('visible', !obj.visible);
                this.canvas.renderAll();
                this.saveState();
                this.renderLayersList();
            }
        }

        toggleLayerLock(index) {
            const obj = this.canvas.getObjects()[index];
            if (obj) {
                const locked = !obj.lockMovementX;
                obj.set({
                    lockMovementX: locked,
                    lockMovementY: locked,
                    lockScalingX: locked,
                    lockScalingY: locked,
                    lockRotation: locked,
                    hasControls: !locked,
                });
                this.canvas.renderAll();
                this.saveState();
                this.renderLayersList();
            }
        }

        deleteLayer(index) {
            const obj = this.canvas.getObjects()[index];
            if (obj) {
                this.canvas.remove(obj);
                this.canvas.renderAll();
                this.saveState();
                this.renderLayersList();
            }
        }

        getObjectName(obj) {
            if (obj.type === 'i-text' || obj.type === 'textbox') {
                return obj.text ? (obj.text.substring(0, 15) + '...') : 'টেক্সট';
            }
            if (obj.type === 'image') return 'ইমেজ / ফটো';
            if (obj.type === 'rect') return 'রেকটেঙ্গেল শেপ';
            if (obj.type === 'circle') return 'সার্কেল শেপ';
            return 'এলিমেন্ট';
        }

        getObjectIcon(obj) {
            if (obj.type === 'i-text' || obj.type === 'textbox') return '🅰️';
            if (obj.type === 'image') return '🖼️';
            if (obj.type === 'rect') return '⏹️';
            if (obj.type === 'circle') return '⚪';
            return '🔹';
        }

        /**
         * ====================================================================
         * 🧲 MAGNETIC SMART SNAPPING GUIDELINES
         * ====================================================================
         */
        setupSnappingGuides() {
            const ctx = this.canvas.getSelectionContext();

            this.canvas.on('object:moving', (e) => {
                const obj = e.target;
                if (!obj) return;

                const canvasW = this.canvas.getWidth();
                const canvasH = this.canvas.getHeight();
                const objCenter = obj.getCenterPoint();

                // Snap to horizontal center
                if (Math.abs(objCenter.x - canvasW / 2) < this.snappingDistance) {
                    obj.set('left', canvasW / 2 - (obj.getScaledWidth() / 2));
                    this.guidelines.x = canvasW / 2;
                } else {
                    this.guidelines.x = null;
                }

                // Snap to vertical center
                if (Math.abs(objCenter.y - canvasH / 2) < this.snappingDistance) {
                    obj.set('top', canvasH / 2 - (obj.getScaledHeight() / 2));
                    this.guidelines.y = canvasH / 2;
                } else {
                    this.guidelines.y = null;
                }
            });

            this.canvas.on('before:render', () => {
                this.canvas.clearContext(this.canvas.contextTop);
            });

            this.canvas.on('after:render', () => {
                const topCtx = this.canvas.contextTop;
                if (this.guidelines.x !== null) {
                    this.drawGuideline(topCtx, this.guidelines.x, 0, this.guidelines.x, this.canvas.getHeight());
                }
                if (this.guidelines.y !== null) {
                    this.drawGuideline(topCtx, 0, this.guidelines.y, this.canvas.getWidth(), this.guidelines.y);
                }
            });

            this.canvas.on('mouse:up', () => {
                this.guidelines.x = null;
                this.guidelines.y = null;
                this.canvas.clearContext(this.canvas.contextTop);
            });
        }

        drawGuideline(ctx, x1, y1, x2, y2) {
            ctx.save();
            ctx.strokeStyle = '#ec4899'; // Pink snapping line
            ctx.lineWidth = 1.5;
            ctx.setLineDash([4, 4]);
            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);
            ctx.stroke();
            ctx.restore();
        }

        /**
         * ====================================================================
         * 🅰️ TYPOGRAPHY & ELEMENTS ADDITION
         * ====================================================================
         */
        addText(text = 'এখানে আপনার হেডলাইন লিখুন', options = {}) {
            const defaultOptions = {
                left: this.canvas.getWidth() / 2,
                top: this.canvas.getHeight() * 0.7,
                originX: 'center',
                originY: 'center',
                fontFamily: 'SolaimanLipi',
                fontSize: 48,
                fill: '#1e293b',
                textAlign: 'center',
                editable: true,
                width: Math.min(800, this.canvas.getWidth() * 0.85),
                breakWords: true,
            };

            const textbox = new fabric.Textbox(text, Object.assign(defaultOptions, options));
            this.canvas.add(textbox);
            this.canvas.setActiveObject(textbox);
            this.canvas.renderAll();
            this.saveState();
            this.renderLayersList();
        }

        addBadge(badgeText = 'ব্রেকিং নিউজ', bgHex = '#dc2626', textHex = '#ffffff') {
            const groupW = 220;
            const groupH = 46;

            const rect = new fabric.Rect({
                width: groupW,
                height: groupH,
                fill: bgHex,
                rx: 8,
                ry: 8,
                originX: 'center',
                originY: 'center',
            });

            const text = new fabric.Text(badgeText, {
                fontFamily: 'SolaimanLipi',
                fontSize: 22,
                fontWeight: 'bold',
                fill: textHex,
                originX: 'center',
                originY: 'center',
            });

            const badgeGroup = new fabric.Group([rect, text], {
                left: this.canvas.getWidth() / 2,
                top: this.canvas.getHeight() * 0.2,
                originX: 'center',
                originY: 'center',
                customName: '🏷️ ' + badgeText,
            });

            this.canvas.add(badgeGroup);
            this.canvas.setActiveObject(badgeGroup);
            this.canvas.renderAll();
            this.saveState();
            this.renderLayersList();
        }

        addShape(type = 'rect', options = {}) {
            let shape = null;
            const w = this.canvas.getWidth();
            const h = this.canvas.getHeight();

            if (type === 'rect') {
                shape = new fabric.Rect(Object.assign({
                    left: w / 2,
                    top: h / 2,
                    width: 300,
                    height: 200,
                    fill: '#3b82f6',
                    rx: 12,
                    ry: 12,
                    originX: 'center',
                    originY: 'center',
                }, options));
            } else if (type === 'circle') {
                shape = new fabric.Circle(Object.assign({
                    left: w / 2,
                    top: h / 2,
                    radius: 120,
                    fill: '#8b5cf6',
                    originX: 'center',
                    originY: 'center',
                }, options));
            } else if (type === 'darkGradientOverlay') {
                // Gradient dark shadow at the bottom for readability without frame
                shape = new fabric.Rect({
                    left: 0,
                    top: h * 0.45,
                    width: w,
                    height: h * 0.55,
                    originX: 'left',
                    originY: 'top',
                    selectable: true,
                    customName: 'ডার্ক গ্রেডিয়েন্ট শ্যাডো',
                });
                shape.set('fill', new fabric.Gradient({
                    type: 'linear',
                    gradientUnits: 'pixels',
                    coords: { x1: 0, y1: 0, x2: 0, y2: h * 0.55 },
                    colorStops: [
                        { offset: 0, color: 'rgba(0, 0, 0, 0)' },
                        { offset: 0.6, color: 'rgba(0, 0, 0, 0.7)' },
                        { offset: 1, color: 'rgba(0, 0, 0, 0.95)' }
                    ]
                }));
            }

            if (shape) {
                this.canvas.add(shape);
                this.canvas.setActiveObject(shape);
                this.canvas.renderAll();
                this.saveState();
                this.renderLayersList();
            }
        }

        addImageFromUrl(url) {
            if (!url) return;
            this.showLoader("ছবি যুক্ত হচ্ছে...");

            fabric.Image.fromURL(url, (img) => {
                const maxDim = Math.min(this.canvas.getWidth(), this.canvas.getHeight()) * 0.7;
                const scale = Math.min(maxDim / img.width, maxDim / img.height, 1);

                img.set({
                    left: this.canvas.getWidth() / 2,
                    top: this.canvas.getHeight() / 2,
                    originX: 'center',
                    originY: 'center',
                    scaleX: scale,
                    scaleY: scale,
                });

                this.canvas.add(img);
                this.canvas.setActiveObject(img);
                this.canvas.renderAll();
                this.hideLoader();
                this.saveState();
                this.renderLayersList();

                // Extract colors
                const el = img.getElement();
                if (el) this.extractColorsFromImage(el);
            }, { crossOrigin: 'anonymous' });
        }

        addImageFromFile(fileInput) {
            if (!fileInput.files || !fileInput.files[0]) return;
            const file = fileInput.files[0];
            const reader = new FileReader();

            reader.onload = (e) => {
                this.addImageFromUrl(e.target.result);
                fileInput.value = '';
            };

            reader.readAsDataURL(file);
        }

        loadNewsData(news) {
            if (!news) return;
            if (news.title) {
                this.addText(news.title, {
                    fontSize: 44,
                    fontWeight: 'bold',
                    top: this.canvas.getHeight() * 0.75,
                });
            }
            if (news.image_url) {
                this.addImageFromUrl(news.image_url);
            }
        }

        /**
         * ====================================================================
         * ⚡ FLOATING DYNAMIC TOOLBAR & CONTEXT MENU
         * ====================================================================
         */
        setupCanvasEvents() {
            this.canvas.on('selection:created', () => {
                this.updateFloatingToolbar();
                this.renderLayersList();
                this.syncSidebarWithActiveObject();
            });

            this.canvas.on('selection:updated', () => {
                this.updateFloatingToolbar();
                this.renderLayersList();
                this.syncSidebarWithActiveObject();
            });

            this.canvas.on('selection:cleared', () => {
                this.hideFloatingToolbar();
                this.renderLayersList();
            });

            this.canvas.on('object:modified', () => {
                this.updateFloatingToolbar();
                this.syncSidebarWithActiveObject();
                this.saveState();
            });

            this.canvas.on('object:added', () => {
                this.renderLayersList();
            });

            this.canvas.on('object:removed', () => {
                this.renderLayersList();
            });
        }

        syncSidebarWithActiveObject() {
            const active = this.canvas.getActiveObject();
            if (!active) return;

            // 1. Text Properties Sync
            if (active.type === 'i-text' || active.type === 'textbox' || active.type === 'text') {
                const fontSelect = document.getElementById('studio-font-select');
                if (fontSelect && active.fontFamily) fontSelect.value = active.fontFamily;

                const sizeSlider = document.getElementById('text-font-size-slider');
                const sizeInput = document.getElementById('text-font-size-input');
                const sizeVal = document.getElementById('font-size-val');
                if (sizeSlider && active.fontSize) sizeSlider.value = active.fontSize;
                if (sizeInput && active.fontSize) sizeInput.value = active.fontSize;
                if (sizeVal && active.fontSize) sizeVal.innerText = active.fontSize + 'px';

                const textColorInput = document.getElementById('text-color-picker');
                if (textColorInput && active.fill && typeof active.fill === 'string' && active.fill.startsWith('#')) {
                    textColorInput.value = active.fill;
                }

                const textBgInput = document.getElementById('text-bg-color-picker');
                if (textBgInput && active.backgroundColor && typeof active.backgroundColor === 'string' && active.backgroundColor.startsWith('#')) {
                    textBgInput.value = active.backgroundColor;
                }

                const strokeColorInput = document.getElementById('text-stroke-color-picker');
                const strokeWidthSlider = document.getElementById('text-stroke-width-slider');
                const strokeWidthVal = document.getElementById('text-stroke-width-val');
                if (strokeColorInput && active.stroke && typeof active.stroke === 'string' && active.stroke.startsWith('#')) {
                    strokeColorInput.value = active.stroke;
                }
                if (strokeWidthSlider) strokeWidthSlider.value = active.strokeWidth || 0;
                if (strokeWidthVal) strokeWidthVal.innerText = (active.strokeWidth || 0) + 'px';

                // Shadow
                const shadow = active.shadow;
                const shadowColor = document.getElementById('text-shadow-color-picker');
                const shadowBlur = document.getElementById('text-shadow-blur-slider');
                const shadowBlurVal = document.getElementById('text-shadow-blur-val');
                const shadowX = document.getElementById('text-shadow-x-slider');
                const shadowXVal = document.getElementById('text-shadow-x-val');
                const shadowY = document.getElementById('text-shadow-y-slider');
                const shadowYVal = document.getElementById('text-shadow-y-val');

                if (shadow) {
                    if (shadowColor && shadow.color) shadowColor.value = shadow.color.startsWith('#') ? shadow.color : '#000000';
                    if (shadowBlur) shadowBlur.value = shadow.blur || 0;
                    if (shadowBlurVal) shadowBlurVal.innerText = (shadow.blur || 0) + 'px';
                    if (shadowX) shadowX.value = shadow.offsetX || 0;
                    if (shadowXVal) shadowXVal.innerText = (shadow.offsetX || 0) + 'px';
                    if (shadowY) shadowY.value = shadow.offsetY || 0;
                    if (shadowYVal) shadowYVal.innerText = (shadow.offsetY || 0) + 'px';
                }
            }

            // 2. Shape Properties Sync
            if (active.type === 'rect' || active.type === 'circle' || active.type === 'path') {
                const shapeFillInput = document.getElementById('shape-fill-color-picker');
                if (shapeFillInput && active.fill && typeof active.fill === 'string' && active.fill.startsWith('#')) {
                    shapeFillInput.value = active.fill;
                }

                const shapeStrokeColor = document.getElementById('shape-stroke-color-picker');
                const shapeStrokeWidth = document.getElementById('shape-stroke-width-slider');
                const shapeStrokeVal = document.getElementById('shape-stroke-width-val');
                if (shapeStrokeColor && active.stroke && typeof active.stroke === 'string' && active.stroke.startsWith('#')) {
                    shapeStrokeColor.value = active.stroke;
                }
                if (shapeStrokeWidth) shapeStrokeWidth.value = active.strokeWidth || 0;
                if (shapeStrokeVal) shapeStrokeVal.innerText = (active.strokeWidth || 0) + 'px';

                if (active.type === 'rect') {
                    const radiusSlider = document.getElementById('shape-corner-radius-slider');
                    const radiusVal = document.getElementById('shape-corner-radius-val');
                    if (radiusSlider) radiusSlider.value = active.rx || 0;
                    if (radiusVal) radiusVal.innerText = (active.rx || 0) + 'px';
                }
            }

            // 3. Image Properties Sync
            if (active.type === 'image') {
                const opacitySlider = document.getElementById('image-opacity-slider');
                const opacityVal = document.getElementById('opacity-val');
                if (opacitySlider) opacitySlider.value = active.opacity !== undefined ? active.opacity : 1;
                if (opacityVal) opacityVal.innerText = Math.round((active.opacity !== undefined ? active.opacity : 1) * 100) + '%';
            }
        }

        updateFloatingToolbar() {
            const toolbar = document.getElementById('floating-context-toolbar');
            const active = this.canvas.getActiveObject();
            if (!toolbar || !active) return;

            const bound = active.getBoundingRect();
            const wrapper = document.getElementById(this.config.canvasWrapperId);
            const container = document.getElementById(this.config.workspaceContainerId);
            if (!wrapper || !container) return;

            const wrapperRect = wrapper.getBoundingClientRect();
            const containerRect = container.getBoundingClientRect();

            const topPos = wrapperRect.top - containerRect.top + (bound.top * this.zoomLevel) - 48;
            const leftPos = wrapperRect.left - containerRect.left + (bound.left * this.zoomLevel) + ((bound.width * this.zoomLevel) / 2);

            toolbar.style.top = Math.max(10, topPos) + 'px';
            toolbar.style.left = leftPos + 'px';
            toolbar.style.transform = 'translateX(-50%)';
            toolbar.classList.remove('hidden');

            // Toggle specific tools based on type
            const isImage = (active.type === 'image');
            const isText = (active.type === 'i-text' || active.type === 'textbox');

            const bgRemoveBtn = document.getElementById('floating-bg-remove-btn');
            if (bgRemoveBtn) {
                bgRemoveBtn.style.display = isImage ? 'inline-flex' : 'none';
            }

            const fontSelect = document.getElementById('floating-font-select');
            if (fontSelect) {
                fontSelect.style.display = isText ? 'inline-flex' : 'none';
            }
        }

        hideFloatingToolbar() {
            const toolbar = document.getElementById('floating-context-toolbar');
            if (toolbar) toolbar.classList.add('hidden');
        }

        setupContextMenu() {
            const contextMenu = document.getElementById('canvas-context-menu');
            if (!contextMenu) return;

            this.canvas.on('mouse:down', (opt) => {
                if (opt.button === 3) { // Right click
                    const active = this.canvas.getActiveObject();
                    if (active) {
                        opt.e.preventDefault();
                        contextMenu.style.top = opt.e.clientY + 'px';
                        contextMenu.style.left = opt.e.clientX + 'px';
                        contextMenu.classList.remove('hidden');
                    }
                } else {
                    contextMenu.classList.add('hidden');
                }
            });

            document.addEventListener('click', () => {
                if (contextMenu) contextMenu.classList.add('hidden');
            });
        }

        /**
         * ====================================================================
         * ⌨️ KEYBOARD SHORTCUTS & HISTORY (UNDO/REDO)
         * ====================================================================
         */
        setupKeyboardShortcuts() {
            window.addEventListener('keydown', (e) => {
                // If editing text in input, don't trigger global shortcuts
                if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;
                const active = this.canvas.getActiveObject();
                if (active && active.isEditing) return;

                // Undo (Ctrl + Z)
                if (e.ctrlKey && e.key.toLowerCase() === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    this.undo();
                }
                // Redo (Ctrl + Y or Ctrl + Shift + Z)
                else if ((e.ctrlKey && e.key.toLowerCase() === 'y') || (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'z')) {
                    e.preventDefault();
                    this.redo();
                }
                // Duplicate (Ctrl + D)
                else if (e.ctrlKey && e.key.toLowerCase() === 'd') {
                    e.preventDefault();
                    this.duplicateActive();
                }
                // Delete (Del or Backspace)
                else if (e.key === 'Delete' || e.key === 'Backspace') {
                    e.preventDefault();
                    this.deleteActive();
                }
                // Nudge (Arrow Keys)
                else if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                    if (active) {
                        e.preventDefault();
                        const step = e.shiftKey ? 10 : 1;
                        if (e.key === 'ArrowUp') active.top -= step;
                        if (e.key === 'ArrowDown') active.top += step;
                        if (e.key === 'ArrowLeft') active.left -= step;
                        if (e.key === 'ArrowRight') active.left += step;
                        active.setCoords();
                        this.canvas.renderAll();
                        this.updateFloatingToolbar();
                    }
                }
            });
        }

        saveState() {
            if (this.isHistoryProcessing) return;

            const json = JSON.stringify(this.canvas.toJSON(['customName', 'selectable', 'lockMovementX', 'lockMovementY']));

            // Remove future states if we are in the middle of history
            if (this.historyIndex < this.history.length - 1) {
                this.history = this.history.slice(0, this.historyIndex + 1);
            }

            this.history.push(json);
            if (this.history.length > this.maxHistory) {
                this.history.shift();
            } else {
                this.historyIndex++;
            }

            this.updateUndoRedoButtons();
        }

        undo() {
            if (this.historyIndex > 0) {
                this.isHistoryProcessing = true;
                this.historyIndex--;
                this.loadHistoryState();
            }
        }

        redo() {
            if (this.historyIndex < this.history.length - 1) {
                this.isHistoryProcessing = true;
                this.historyIndex++;
                this.loadHistoryState();
            }
        }

        loadHistoryState() {
            const state = this.history[this.historyIndex];
            if (!state) return;

            this.canvas.loadFromJSON(state, () => {
                this.canvas.renderAll();
                this.isHistoryProcessing = false;
                this.updateUndoRedoButtons();
                this.renderLayersList();
                this.fitToScreen();
            });
        }

        updateUndoRedoButtons() {
            const undoBtn = document.getElementById('btn-undo');
            const redoBtn = document.getElementById('btn-redo');
            if (undoBtn) undoBtn.disabled = (this.historyIndex <= 0);
            if (redoBtn) redoBtn.disabled = (this.historyIndex >= this.history.length - 1);
        }

        duplicateActive() {
            const active = this.canvas.getActiveObject();
            if (!active) return;

            active.clone((cloned) => {
                cloned.set({
                    left: active.left + 20,
                    top: active.top + 20,
                    evented: true,
                });
                this.canvas.add(cloned);
                this.canvas.setActiveObject(cloned);
                this.canvas.renderAll();
                this.saveState();
                this.renderLayersList();
            });
        }

        deleteActive() {
            const active = this.canvas.getActiveObject();
            if (active) {
                this.canvas.remove(active);
                this.canvas.discardActiveObject();
                this.canvas.renderAll();
                this.saveState();
                this.renderLayersList();
                this.hideFloatingToolbar();
            }
        }

        /**
         * ====================================================================
         * 📥 EXPORT & CLIPBOARD
         * ====================================================================
         */
        downloadCard(format = 'png', multiplier = 1) {
            const dataUrl = this.canvas.toDataURL({
                format: format,
                multiplier: multiplier,
                quality: 1.0,
                enableRetinaScaling: true,
            });

            const link = document.createElement('a');
            link.download = `photocard_${Date.now()}.${format}`;
            link.href = dataUrl;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            this.showNotification("success", "কার্ড ডাউনলোড সফল হয়েছে!");
        }

        async copyToClipboard() {
            try {
                const dataUrl = this.canvas.toDataURL({ format: 'png', multiplier: 1.5 });
                const res = await fetch(dataUrl);
                const blob = await res.blob();

                if (navigator.clipboard && window.ClipboardItem) {
                    await navigator.clipboard.write([
                        new ClipboardItem({ 'image/png': blob })
                    ]);
                    this.showNotification("success", "ক্লিপবোর্ডে কপি হয়েছে! সরাসরি চ্যাটে বা পোস্টে Paste (Ctrl+V) করুন।");
                } else {
                    this.showNotification("warning", "আপনার ব্রাউজার সরাসরি ইমেজ কপি সাপোর্ট করে না।");
                }
            } catch (err) {
                console.error("Clipboard error:", err);
                this.showNotification("error", "ক্লিপবোর্ডে কপি করা যায়নি।");
            }
        }

        /**
         * ====================================================================
         * 🎨 PRO STYLING, STROKE, SHADOW & RESET CONTROLS
         * ====================================================================
         */
        setTextBackground(color) {
            const active = this.canvas.getActiveObject();
            if (active && (active.type === 'i-text' || active.type === 'textbox' || active.type === 'text')) {
                active.set('backgroundColor', color || '');
                this.canvas.renderAll();
                this.saveState();
            }
        }

        removeTextBackground() {
            const active = this.canvas.getActiveObject();
            if (active && (active.type === 'i-text' || active.type === 'textbox' || active.type === 'text')) {
                active.set('backgroundColor', '');
                this.canvas.renderAll();
                this.saveState();
                const textBgInput = document.getElementById('text-bg-color-picker');
                if (textBgInput) textBgInput.value = '#ffffff';
                this.showNotification("info", "টেক্সট ব্যাকগ্রাউন্ড রিমুভ করা হয়েছে।");
            }
        }

        setTextColor(color) {
            const active = this.canvas.getActiveObject();
            if (active && (active.type === 'i-text' || active.type === 'textbox' || active.type === 'text')) {
                active.set('fill', color);
                this.canvas.renderAll();
                this.saveState();
            }
        }

        setTextStroke(color, width) {
            const active = this.canvas.getActiveObject();
            if (active && (active.type === 'i-text' || active.type === 'textbox' || active.type === 'text')) {
                const w = parseFloat(width) || 0;
                active.set({
                    stroke: w > 0 ? (color || '#000000') : null,
                    strokeWidth: w
                });
                this.canvas.renderAll();
                this.saveState();
            }
        }

        removeTextStroke() {
            const active = this.canvas.getActiveObject();
            if (active && (active.type === 'i-text' || active.type === 'textbox' || active.type === 'text')) {
                active.set({ stroke: null, strokeWidth: 0 });
                this.canvas.renderAll();
                this.saveState();
                const strokeSlider = document.getElementById('text-stroke-width-slider');
                const strokeVal = document.getElementById('text-stroke-width-val');
                if (strokeSlider) strokeSlider.value = 0;
                if (strokeVal) strokeVal.innerText = '0px';
            }
        }

        setTextShadow(color, blur, offsetX, offsetY) {
            const active = this.canvas.getActiveObject();
            if (active && (active.type === 'i-text' || active.type === 'textbox' || active.type === 'text')) {
                const b = parseFloat(blur) || 0;
                const ox = parseFloat(offsetX) || 0;
                const oy = parseFloat(offsetY) || 0;

                if (b === 0 && ox === 0 && oy === 0) {
                    active.set('shadow', null);
                } else {
                    active.set('shadow', new fabric.Shadow({
                        color: color || '#000000',
                        blur: b,
                        offsetX: ox,
                        offsetY: oy
                    }));
                }
                this.canvas.renderAll();
                this.saveState();
            }
        }

        removeTextShadow() {
            const active = this.canvas.getActiveObject();
            if (active) {
                active.set('shadow', null);
                this.canvas.renderAll();
                this.saveState();

                const blurSlider = document.getElementById('text-shadow-blur-slider');
                const xSlider = document.getElementById('text-shadow-x-slider');
                const ySlider = document.getElementById('text-shadow-y-slider');
                if (blurSlider) blurSlider.value = 0;
                if (xSlider) xSlider.value = 0;
                if (ySlider) ySlider.value = 0;

                const blurVal = document.getElementById('text-shadow-blur-val');
                const xVal = document.getElementById('text-shadow-x-val');
                const yVal = document.getElementById('text-shadow-y-val');
                if (blurVal) blurVal.innerText = '0px';
                if (xVal) xVal.innerText = '0px';
                if (yVal) yVal.innerText = '0px';

                this.showNotification("info", "শ্যাডো রিমুভ করা হয়েছে।");
            }
        }

        setTextFontSize(size) {
            const active = this.canvas.getActiveObject();
            if (active && (active.type === 'i-text' || active.type === 'textbox' || active.type === 'text')) {
                const s = Math.max(8, parseInt(size) || 48);
                active.set('fontSize', s);
                this.canvas.renderAll();
                this.saveState();

                const sizeSlider = document.getElementById('text-font-size-slider');
                const sizeInput = document.getElementById('text-font-size-input');
                const sizeVal = document.getElementById('font-size-val');
                if (sizeSlider) sizeSlider.value = s;
                if (sizeInput) sizeInput.value = s;
                if (sizeVal) sizeVal.innerText = s + 'px';
            }
        }

        // Shape Controls
        setShapeStroke(color, width) {
            const active = this.canvas.getActiveObject();
            if (active) {
                const w = parseFloat(width) || 0;
                active.set({
                    stroke: w > 0 ? (color || '#000000') : null,
                    strokeWidth: w
                });
                this.canvas.renderAll();
                this.saveState();
            }
        }

        setShapeRadius(radius) {
            const active = this.canvas.getActiveObject();
            if (active && active.type === 'rect') {
                const r = Math.max(0, parseFloat(radius) || 0);
                active.set({ rx: r, ry: r });
                this.canvas.renderAll();
                this.saveState();
            }
        }

        setShapeShadow(color, blur, offsetX, offsetY) {
            const active = this.canvas.getActiveObject();
            if (active) {
                const b = parseFloat(blur) || 0;
                const ox = parseFloat(offsetX) || 0;
                const oy = parseFloat(offsetY) || 0;

                if (b === 0 && ox === 0 && oy === 0) {
                    active.set('shadow', null);
                } else {
                    active.set('shadow', new fabric.Shadow({
                        color: color || '#000000',
                        blur: b,
                        offsetX: ox,
                        offsetY: oy
                    }));
                }
                this.canvas.renderAll();
                this.saveState();
            }
        }

        removeShapeShadow() {
            const active = this.canvas.getActiveObject();
            if (active) {
                active.set('shadow', null);
                this.canvas.renderAll();
                this.saveState();
            }
        }

        setShapeFill(color) {
            const active = this.canvas.getActiveObject();
            if (active) {
                active.set('fill', color);
                this.canvas.renderAll();
                this.saveState();
            }
        }

        toggleShapeNoFill(isTransparent) {
            const active = this.canvas.getActiveObject();
            if (active) {
                if (isTransparent) {
                    active.set('fill', 'transparent');
                    if (!active.stroke || active.strokeWidth === 0) {
                        active.set({ stroke: '#3b82f6', strokeWidth: 3 });
                    }
                } else {
                    active.set('fill', '#3b82f6');
                }
                this.canvas.renderAll();
                this.saveState();
            }
        }

        resetCanvasBackground() {
            this.canvas.setBackgroundColor('#ffffff', () => this.canvas.renderAll());
            this.saveState();
            this.showNotification("info", "ক্যানভাস ব্যাকগ্রাউন্ড সাদা করা হয়েছে।");
        }

        resetActiveColors() {
            const active = this.canvas.getActiveObject();
            if (active) {
                if (active.type === 'i-text' || active.type === 'textbox' || active.type === 'text') {
                    active.set({ fill: '#1e293b', backgroundColor: '', stroke: null, strokeWidth: 0, shadow: null });
                } else if (active.type === 'rect' || active.type === 'circle') {
                    active.set({ fill: '#3b82f6', stroke: null, strokeWidth: 0, shadow: null });
                }
                this.canvas.renderAll();
                this.syncSidebarWithActiveObject();
                this.saveState();
                this.showNotification("info", "সিলেক্টেড এলিমেন্টের কালার রিসেট হয়েছে।");
            }
        }

        /**
         * ====================================================================
         * 💾 SAVE & REUSE CUSTOMIZED TEMPLATES
         * ====================================================================
         */
        async saveCustomTemplate(name) {
            if (!name || !name.trim()) {
                this.showNotification("warning", "দয়া করে টেমপ্লেটের একটি নাম লিখুন।");
                return;
            }

            this.showLoader("টেমপ্লেট সেভ হচ্ছে...");

            try {
                // Generate Thumbnail
                const thumbnail = this.canvas.toDataURL({ format: 'png', multiplier: 0.25 });
                
                // Export JSON
                const layoutData = this.canvas.toJSON(['customName', 'selectable', 'lockMovementX', 'lockMovementY', 'isFrame', 'isHeadline', 'isDate', 'rx', 'ry']);

                const formData = new FormData();
                formData.append('name', name.trim());
                formData.append('layout_data', JSON.stringify(layoutData));
                formData.append('thumbnail', thumbnail);
                formData.append('frame_url', this.activeFrame || '');
                formData.append('_token', this.config.csrfToken);

                const response = await fetch('/studio/custom-photo-card/save-template', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'টেমপ্লেট সংরক্ষণ ব্যর্থ হয়েছে।');
                }

                this.hideLoader();
                this.showNotification("success", "টেমপ্লেট সফলভাবে সংরক্ষিত হয়েছে!");

                // Dynamically insert into saved templates UI
                this.appendSavedTemplateToUI(data.template);

                // Close modal
                const modal = document.getElementById('save-template-modal');
                if (modal) modal.classList.add('hidden');

            } catch (err) {
                this.hideLoader();
                console.error("Save Template Error:", err);
                this.showNotification("error", err.message || "টেমপ্লেট সেভ করতে সমস্যা হয়েছে।");
            }
        }

        loadCustomTemplate(templateData, frameUrl = null) {
            if (!templateData) return;
            this.showLoader("টেমপ্লেট লোড হচ্ছে...");

            const json = typeof templateData === 'string' ? JSON.parse(templateData) : templateData;

            this.canvas.loadFromJSON(json, () => {
                if (frameUrl) {
                    this.applyFrame(frameUrl);
                } else {
                    this.canvas.renderAll();
                    this.hideLoader();
                    this.fitToScreen();
                    this.saveState();
                    this.renderLayersList();
                    this.showNotification("success", "টেমপ্লেট সফলভাবে লোড হয়েছে!");
                }
            });
        }

        async deleteCustomTemplate(templateId, element) {
            if (!confirm("আপনি কি নিশ্চিতভাবে এই টেমপ্লেটটি মুছে ফেলতে চান?")) return;

            try {
                const response = await fetch(`/studio/custom-photo-card/delete-template/${templateId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    if (element) {
                        const card = element.closest('.saved-template-card');
                        if (card) card.remove();
                    }
                    this.showNotification("info", "টেমপ্লেট মুছে ফেলা হয়েছে।");
                } else {
                    this.showNotification("error", data.message || "ডিলিট করতে সমস্যা হয়েছে।");
                }
            } catch (err) {
                console.error(err);
                this.showNotification("error", "ডিলিট করতে সমস্যা হয়েছে।");
            }
        }

        appendSavedTemplateToUI(tpl) {
            const container = document.getElementById('saved-templates-container');
            const emptyMsg = document.getElementById('saved-templates-empty');
            if (emptyMsg) emptyMsg.style.display = 'none';

            if (!container) return;

            const tplJson = JSON.stringify(tpl.layout_data).replace(/"/g, '&quot;');
            const card = document.createElement('div');
            card.className = 'saved-template-card cursor-pointer border border-slate-200 rounded-xl p-1.5 bg-slate-50 hover:bg-white hover:border-indigo-500 hover:shadow-md transition-all group relative flex flex-col items-center';
            card.innerHTML = `
                <div onclick="window.customStudio.loadCustomTemplate(${JSON.stringify(tpl.layout_data).replace(/"/g, '&quot;')}, '${tpl.frame_url || ''}')" class="w-full h-20 rounded-lg overflow-hidden bg-slate-200 flex items-center justify-center p-1 relative">
                    <img src="${tpl.thumbnail_url || tpl.frame_url || '/placeholder.png'}" alt="${tpl.name}" loading="lazy" class="w-full h-full object-contain">
                </div>
                <div class="flex items-center justify-between w-full mt-1.5 px-1">
                    <span class="text-[10px] font-bold text-slate-700 truncate">${tpl.name}</span>
                    <button type="button" onclick="window.customStudio.deleteCustomTemplate(${tpl.id}, this)" class="text-red-400 hover:text-red-600 p-0.5 text-xs" title="ডিলিট">🗑️</button>
                </div>
            `;
            container.prepend(card);
        }

        /**
         * ====================================================================
         * 🛠️ UI HELPERS
         * ====================================================================
         */
        showLoader(text = "লোড হচ্ছে...") {
            const loader = document.getElementById('canvas-preloader');
            const textEl = document.getElementById('canvas-preloader-text');
            if (loader) loader.classList.remove('hidden');
            if (textEl) textEl.innerText = text;
        }

        hideLoader() {
            const loader = document.getElementById('canvas-preloader');
            if (loader) loader.classList.add('hidden');
        }

        showNotification(type, message) {
            if (window.Toastify) {
                const bg = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#6366f1');
                window.Toastify({
                    text: message,
                    duration: 3500,
                    gravity: "bottom",
                    position: "right",
                    style: { background: bg, borderRadius: "10px", fontWeight: "bold" },
                }).showToast();
            } else {
                alert(message);
            }
        }
    }

    window.CustomPhotoCardEngine = CustomPhotoCardEngine;

})(window);

