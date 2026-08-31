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
            this.setupFloatingToolbarDrag();

            // Initial fit
            this.fitToScreen();
            window.addEventListener('resize', () => this.fitToScreen());

            // Save initial state
            this.saveState();
            this.renderCustomTemplatesList();

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
            fabric.Object.prototype.cornerStrokeColor = '#4f46e5';
            fabric.Object.prototype.borderColor = '#6366f1';
            fabric.Object.prototype.cornerSize = 13;
            fabric.Object.prototype.cornerStrokeWidth = 2.5;
            fabric.Object.prototype.cornerStyle = 'circle';
            fabric.Object.prototype.borderScaleFactor = 2;
            fabric.Object.prototype.borderDashArray = [5, 5];
            fabric.Object.prototype.padding = 10;
            fabric.Object.prototype.touchCornerSize = 32;
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

                // Remove previous frame if exists
                const existingFrame = this.canvas.getObjects().find(o => o.isFrame);
                if (existingFrame) this.canvas.remove(existingFrame);
                this.canvas.setOverlayImage(null);

                // Add as manageable Canvas Layer Object
                fabric.Image.fromURL(frameUrl, (fabricImg) => {
                    fabricImg.set({
                        originX: 'left',
                        originY: 'top',
                        left: 0,
                        top: 0,
                        scaleX: naturalW / fabricImg.width,
                        scaleY: naturalH / fabricImg.height,
                        selectable: true,
                        evented: true,
                        isFrame: true,
                        customName: '🖼️ ফ্রেম / টেমপ্লেট',
                    });

                    this.canvas.add(fabricImg);
                    this.canvas.bringToFront(fabricImg);
                    this.activeFrame = frameUrl;
                    this.canvas.renderAll();
                    this.hideLoader();
                    this.fitToScreen();
                    this.saveState();
                    this.renderLayersList();
                    this.updateDimensionBadges(naturalW, naturalH);
                    this.showNotification("success", `ফ্রেম লেয়ার যুক্ত হয়েছে (${naturalW}×${naturalH}px)`);
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
            const existingFrame = this.canvas.getObjects().find(o => o.isFrame);
            if (existingFrame) this.canvas.remove(existingFrame);
            this.canvas.setOverlayImage(null);
            this.activeFrame = null;
            this.canvas.renderAll();
            this.saveState();
            this.renderLayersList();
            this.showNotification("info", "ফ্রেম রিমুভ করা হয়েছে।");
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
                if (obj.isFrame) this.activeFrame = null;
                this.canvas.remove(obj);
                this.canvas.renderAll();
                this.saveState();
                this.renderLayersList();
            }
        }

        bringActiveToFront() {
            const active = this.canvas.getActiveObject();
            if (active) {
                this.canvas.bringToFront(active);
                this.canvas.renderAll();
                this.saveState();
                this.renderLayersList();
                this.updateFloatingToolbar();
                this.showNotification("info", "লেয়ারটি একদম উপরে আনা হয়েছে 🔝");
            }
        }

        bringActiveForward() {
            const active = this.canvas.getActiveObject();
            if (active) {
                this.canvas.bringForward(active);
                this.canvas.renderAll();
                this.saveState();
                this.renderLayersList();
                this.updateFloatingToolbar();
                this.showNotification("info", "লেয়ারটি এক স্তর উপরে তোলা হয়েছে ⬆️");
            }
        }

        sendActiveBackward() {
            const active = this.canvas.getActiveObject();
            if (active) {
                this.canvas.sendBackwards(active);
                this.canvas.renderAll();
                this.saveState();
                this.renderLayersList();
                this.updateFloatingToolbar();
                this.showNotification("info", "লেয়ারটি এক স্তর নিচে নামানো হয়েছে ⬇️");
            }
        }

        sendActiveToBack() {
            const active = this.canvas.getActiveObject();
            if (active) {
                const objects = this.canvas.getObjects();
                const backdrop = objects.find(o => o.customName === '🎨 ব্যাকগ্রাউন্ড থিম');
                this.canvas.sendToBack(active);
                if (backdrop) {
                    this.canvas.sendToBack(backdrop);
                }
                this.canvas.renderAll();
                this.saveState();
                this.renderLayersList();
                this.updateFloatingToolbar();
                this.showNotification("info", "লেয়ারটি একদম নিচে পাঠানো হয়েছে 🔻");
            }
        }

        toggleLockActive() {
            const active = this.canvas.getActiveObject();
            if (active) {
                const locked = !active.lockMovementX;
                active.set({
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
                this.showNotification(locked ? "warning" : "success", locked ? "লেয়ারটি লক করা হয়েছে 🔒" : "লেয়ারটি আনলক করা হয়েছে 🔓");
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
                if (this.canvas.contextTop) {
                    this.canvas.clearContext(this.canvas.contextTop);
                }
            });

            this.canvas.on('after:render', () => {
                const topCtx = this.canvas.contextTop;
                if (!topCtx) return;
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
                if (this.canvas.contextTop) {
                    this.canvas.clearContext(this.canvas.contextTop);
                }
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
            let defaultColor = '#1e293b';
            const bg = this.canvas.backgroundColor;
            if (bg === '#0f172a' || bg === '#7f1d1d' || bg === '#000000' || (typeof bg === 'string' && (bg.startsWith('#0') || bg.startsWith('#1')))) {
                defaultColor = '#ffffff';
            }

            const currentFont = document.getElementById('studio-font-select')?.value || 'SolaimanLipi';

            const defaultOptions = {
                left: this.canvas.getWidth() / 2,
                top: this.canvas.getHeight() / 2,
                originX: 'center',
                originY: 'center',
                fontFamily: currentFont,
                fontSize: 48,
                fill: defaultColor,
                textAlign: 'center',
                editable: true,
                width: Math.min(800, this.canvas.getWidth() * 0.85),
                breakWords: true,
                customName: '💬 ' + (text.length > 15 ? text.substring(0, 15) + '...' : text),
            };

            const textbox = new fabric.Textbox(text, Object.assign(defaultOptions, options));
            this.canvas.add(textbox);
            this.canvas.bringToFront(textbox);
            this.canvas.setActiveObject(textbox);
            textbox.initDimensions();
            textbox.setCoords();
            this.canvas.renderAll();
            this.saveState();
            this.renderLayersList();
            this.syncSidebarWithActiveObject();
            this.updateFloatingToolbar();
            this.showNotification("success", "টেক্সট যোগ করা হয়েছে!");
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

        /**
         * ====================================================================
         * ⚡ INSTANT LIVE PREVIEW METHODS FOR QUOTE CARD
         * ====================================================================
         */
        previewQuoteImage(dataUrl) {
            if (!dataUrl) return;
            const canvasW = this.canvas.getWidth();
            const canvasH = this.canvas.getHeight();

            let existingImg = this.canvas.getObjects().find(o => o.isQuotePortrait || (o.customName && o.customName.includes('👤')));
            if (existingImg) {
                this.canvas.remove(existingImg);
            }

            fabric.Image.fromURL(dataUrl, (img) => {
                const maxW = canvasW * 0.44;
                const maxH = canvasH * 0.85;
                const scale = Math.min(maxW / img.width, maxH / img.height);
                const scaledW = img.width * scale;
                const scaledH = img.height * scale;

                const posSelect = document.getElementById('quote-card-pos')?.value || 'left';
                const flipCheck = document.getElementById('quote-card-flip-check')?.checked === true;
                const nameVal = document.getElementById('quote-card-name')?.value || 'বক্তার নাম';

                img.set({
                    scaleX: scale,
                    scaleY: scale,
                    flipX: flipCheck,
                    left: posSelect === 'left' ? (scaledW / 2 + 25) : (canvasW - (scaledW / 2) - 25),
                    top: canvasH - (scaledH / 2),
                    originX: 'center',
                    originY: 'center',
                    selectable: true,
                    isQuotePortrait: true,
                    customName: '👤 ' + nameVal,
                });

                this.canvas.add(img);
                this.canvas.renderAll();
                this.renderLayersList();
            }, { crossOrigin: 'anonymous' });
        }

        recalculateQuoteCardLayout() {
            const canvasW = this.canvas.getWidth();
            const canvasH = this.canvas.getHeight();

            const quoteMark = this.canvas.getObjects().find(o => o.isQuoteMark || o.customName === '❝ কোটেশন মার্ক');
            const quoteBox = this.canvas.getObjects().find(o => o.isQuoteText || o.customName === '💬 মূল উক্তি');
            const barRect = this.canvas.getObjects().find(o => o.isQuoteBar || o.customName === '🔴 অ্যাকসেন্ট বার');
            const nameObj = this.canvas.getObjects().find(o => o.isQuoteName || (o.customName && o.customName.startsWith('🏷️')));
            const desigObj = this.canvas.getObjects().find(o => o.isQuoteDesig || (o.customName && o.customName.startsWith('📋')));
            const portraitImg = this.canvas.getObjects().find(o => o.isQuotePortrait || (o.customName && o.customName.includes('👤')));

            if (!quoteBox && !nameObj) return;

            const position = document.getElementById('quote-card-pos')?.value || 'left';
            const margin = Math.round(canvasW * 0.05); // 5% margin (50px on 1000px canvas)

            let scaledW = 0;
            if (portraitImg) {
                scaledW = portraitImg.getScaledWidth();
            }

            // 1. Calculate Horizontal Text Bounds
            let textLeft = margin;
            let textWidth = canvasW - (margin * 2);

            if (portraitImg && scaledW > 0) {
                if (position === 'left') {
                    textLeft = scaledW + Math.round(canvasW * 0.04);
                    textWidth = canvasW - textLeft - margin;
                } else {
                    textLeft = margin;
                    textWidth = (canvasW - scaledW - Math.round(canvasW * 0.03)) - textLeft;
                }
            }

            // 2. Position Top Quotation Mark ❝ (Generous top spacing, never overlapping quote text)
            const quoteMarkTop = Math.max(35, Math.round(canvasH * 0.08));
            const quoteMarkSize = Math.max(48, Math.round(canvasH * 0.065));
            if (quoteMark) {
                quoteMark.set({
                    left: textLeft,
                    top: quoteMarkTop,
                    fontSize: quoteMarkSize,
                });
                quoteMark.setCoords();
            }

            // 3. Position & Dynamic Sizing for Quote Textbox
            const textTop = quoteMarkTop + quoteMarkSize + 14;
            if (quoteBox) {
                const quoteText = quoteBox.text || '';
                let fontSize = Math.round(canvasH * 0.044);
                if (quoteText.length > 250) fontSize = Math.round(canvasH * 0.024);
                else if (quoteText.length > 160) fontSize = Math.round(canvasH * 0.030);
                else if (quoteText.length > 90) fontSize = Math.round(canvasH * 0.036);
                else if (quoteText.length < 40) fontSize = Math.round(canvasH * 0.050);

                quoteBox.set({
                    left: textLeft,
                    top: textTop,
                    width: textWidth,
                    fontSize: fontSize,
                    lineHeight: 1.4,
                    breakWords: true,
                });
                quoteBox.initDimensions();
                quoteBox.setCoords();

                // Auto-scale font down if total text overflows available height
                const maxAllowedQuoteH = canvasH * 0.50;
                while (fontSize > 16 && quoteBox.getScaledHeight() > maxAllowedQuoteH) {
                    fontSize -= 2;
                    quoteBox.set('fontSize', fontSize);
                    quoteBox.initDimensions();
                    quoteBox.setCoords();
                }
            }

            // 4. Calculate Quote Bottom (Generous 32px breathing room between quote and speaker name)
            const quoteH = quoteBox ? quoteBox.getScaledHeight() : 40;
            const quoteBottom = textTop + quoteH + Math.max(28, Math.round(canvasH * 0.036));

            // 5. Position Speaker Name & Accent Bar
            const nameFontSize = Math.max(22, Math.round(canvasH * 0.028));
            let nameH = 30;
            if (nameObj) {
                nameObj.set({
                    left: textLeft + 20,
                    top: quoteBottom,
                    width: textWidth - 25,
                    fontSize: nameFontSize,
                    lineHeight: 1.25,
                    breakWords: true,
                });
                nameObj.initDimensions();
                nameObj.setCoords();
                nameH = Math.max(28, nameObj.getScaledHeight());
            }

            if (barRect) {
                barRect.set({
                    left: textLeft,
                    top: quoteBottom + 3,
                    width: 6,
                    height: Math.max(26, nameH - 4),
                    rx: 3,
                    ry: 3,
                });
                barRect.setCoords();
            }

            // 6. Position Designation (8px gap below Name)
            if (desigObj) {
                const desigFontSize = Math.max(15, Math.round(canvasH * 0.019));
                const desigTop = quoteBottom + nameH + 8;
                desigObj.set({
                    left: textLeft + 20,
                    top: desigTop,
                    width: textWidth - 25,
                    fontSize: desigFontSize,
                    lineHeight: 1.25,
                    breakWords: true,
                });
                desigObj.initDimensions();
                desigObj.setCoords();
            }

            this.canvas.renderAll();
        }

        updateQuoteLiveField(field, value) {
            let quoteBox = this.canvas.getObjects().find(o => o.isQuoteText || o.customName === '💬 মূল উক্তি');
            let nameObj = this.canvas.getObjects().find(o => o.isQuoteName || (o.customName && o.customName.startsWith('🏷️')));
            let desigObj = this.canvas.getObjects().find(o => o.isQuoteDesig || (o.customName && o.customName.startsWith('📋')));
            let portraitImg = this.canvas.getObjects().find(o => o.isQuotePortrait || (o.customName && o.customName.includes('👤')));

            if (!quoteBox && !nameObj) {
                const quoteVal = document.getElementById('quote-card-text')?.value || 'এখানে আপনার উক্তি লিখুন...';
                const nameVal = document.getElementById('quote-card-name')?.value || 'বক্তার নাম';
                const desigVal = document.getElementById('quote-card-desig')?.value || '';
                const fontVal = document.getElementById('quote-card-font')?.value || "'SolaimanLipi'";
                const themeVal = document.getElementById('quote-card-theme')?.value || 'soft-blue';
                const posVal = document.getElementById('quote-card-pos')?.value || 'left';
                const flipVal = document.getElementById('quote-card-flip-check')?.checked === true;

                this.generateQuoteCard({
                    quote: quoteVal,
                    name: nameVal,
                    designation: desigVal,
                    fontFamily: fontVal,
                    theme: themeVal,
                    position: posVal,
                    flipPhoto: flipVal,
                    removeBg: false,
                    imageSource: null
                });
                return;
            }

            if (field === 'text' && quoteBox) {
                quoteBox.set('text', value || 'এখানে আপনার উক্তি লিখুন...');
            } else if (field === 'name' && nameObj) {
                nameObj.set('text', value || 'বক্তার নাম');
                nameObj.set('customName', '🏷️ ' + (value || 'বক্তার নাম'));
            } else if (field === 'designation') {
                if (desigObj) {
                    desigObj.set('text', value || '');
                    if (!value || !value.trim()) {
                        this.canvas.remove(desigObj);
                    }
                } else if (value && value.trim()) {
                    const newDesig = new fabric.Textbox(value, {
                        left: quoteBox ? quoteBox.left + 20 : 50,
                        top: (nameObj ? nameObj.top + 35 : 200),
                        width: quoteBox ? quoteBox.width - 25 : 300,
                        fontSize: 18,
                        fontFamily: document.getElementById('quote-card-font')?.value || 'SolaimanLipi',
                        fontWeight: 'normal',
                        fill: '#64748b',
                        selectable: true,
                        isQuoteDesig: true,
                        customName: '📋 ' + value,
                    });
                    this.canvas.add(newDesig);
                }
            } else if (field === 'font') {
                if (quoteBox) quoteBox.set('fontFamily', value);
                if (nameObj) nameObj.set('fontFamily', value);
                if (desigObj) desigObj.set('fontFamily', value);
            } else if (field === 'flip' && portraitImg) {
                portraitImg.set('flipX', value);
            } else if (field === 'position' && portraitImg) {
                const canvasW = this.canvas.getWidth();
                const scaledW = portraitImg.getScaledWidth();
                portraitImg.set({
                    left: value === 'left' ? (scaledW / 2 + 25) : (canvasW - (scaledW / 2) - 25)
                });
            } else if (field === 'theme') {
                this.generateQuoteCard({
                    quote: document.getElementById('quote-card-text')?.value,
                    name: document.getElementById('quote-card-name')?.value,
                    designation: document.getElementById('quote-card-desig')?.value,
                    fontFamily: document.getElementById('quote-card-font')?.value,
                    theme: value,
                    position: document.getElementById('quote-card-pos')?.value,
                    flipPhoto: document.getElementById('quote-card-flip-check')?.checked,
                    removeBg: false
                });
                return;
            }

            this.recalculateQuoteCardLayout();
        }

        /**
         * ====================================================================
         * 🎙️ 1-CLICK SMART STATEMENT / QUOTE CARD GENERATOR (AUTO-ARRANGE)
         * ====================================================================
         */
        async generateQuoteCard(params = {}) {
            const quote = (params.quote || '').trim() || 'সংবিধান সংশোধন করেই আমরা জুলাই সনদ বাস্তবায়ন করব।';
            const name = (params.name || '').trim() || 'বক্তার নাম';
            const designation = (params.designation || '').trim() || '';
            const position = params.position || 'left'; // 'left' or 'right'
            const theme = params.theme || 'soft-blue'; // 'soft-blue', 'clean-white', 'dark-elegant', 'breaking-red'
            const flipPhoto = params.flipPhoto === true;
            const imageSource = params.imageSource || null;
            const removeBg = params.removeBg !== false;

            this.showLoader("উক্তি কার্ড তৈরি হচ্ছে... AI প্রসেসিং ও ফন্ট রেন্ডার চলছে");

            // 0. Ensure all Bengali fonts are 100% ready
            if (document.fonts && document.fonts.ready) {
                try {
                    await document.fonts.ready;
                } catch (fontErr) {
                    console.warn("Font readiness check warning:", fontErr);
                }
            }

            try {
                let existingImg = this.canvas.getObjects().find(o => o.isQuotePortrait || (o.customName && o.customName.includes('👤')));
                let resolvedSource = imageSource || (existingImg && existingImg._element ? existingImg._element.src : null);
                let finalImageUrl = resolvedSource;

                // 1. If image provided and removeBg is requested, process via PhotoRoom API
                if (resolvedSource && removeBg && resolvedSource.startsWith('data:')) {
                    const formData = new FormData();
                    formData.append('image', resolvedSource);
                    formData.append('_token', this.config.csrfToken);

                    try {
                        const response = await fetch(this.config.removeBgUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.config.csrfToken
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (data.success && data.output_url) {
                            finalImageUrl = data.output_url;
                            if (data.remaining_credits !== undefined) {
                                this.updateCreditBadge(data.remaining_credits, data.daily_used, data.daily_limit);
                            }
                        } else {
                            console.warn("BG Remove fallback to original image:", data.message);
                        }
                    } catch (bgErr) {
                        console.warn("BG Remove API error, proceeding with original image:", bgErr);
                    }
                }

                // 2. Setup Canvas Dimensions & Theme Styles
                const canvasW = this.canvas.getWidth();
                const canvasH = this.canvas.getHeight();

                this.canvas.clear();

                // Theme Presets Configuration
                let themeConfig = {
                    bgColor: '#f8fafc',
                    gradientStops: [
                        { offset: 0, color: '#f0f9ff' },
                        { offset: 0.5, color: '#ffffff' },
                        { offset: 1, color: '#e2e8f0' }
                    ],
                    quoteMarkColor: '#0f172a',
                    quoteTextColor: '#0f172a',
                    accentBarColor: '#dc2626',
                    nameTextColor: '#0f172a',
                    desigTextColor: '#64748b',
                    dateTextColor: '#64748b',
                };

                if (theme === 'clean-white') {
                    themeConfig = {
                        bgColor: '#ffffff',
                        gradientStops: null,
                        quoteMarkColor: '#dc2626',
                        quoteTextColor: '#111827',
                        accentBarColor: '#dc2626',
                        nameTextColor: '#111827',
                        desigTextColor: '#6b7280',
                        dateTextColor: '#9ca3af',
                    };
                } else if (theme === 'dark-elegant') {
                    themeConfig = {
                        bgColor: '#0f172a',
                        gradientStops: [
                            { offset: 0, color: '#0f172a' },
                            { offset: 0.5, color: '#1e293b' },
                            { offset: 1, color: '#090d16' }
                        ],
                        quoteMarkColor: '#f59e0b',
                        quoteTextColor: '#ffffff',
                        accentBarColor: '#ef4444',
                        nameTextColor: '#ffffff',
                        desigTextColor: '#cbd5e1',
                        dateTextColor: '#94a3b8',
                    };
                } else if (theme === 'breaking-red') {
                    themeConfig = {
                        bgColor: '#7f1d1d',
                        gradientStops: [
                            { offset: 0, color: '#7f1d1d' },
                            { offset: 0.5, color: '#991b1b' },
                            { offset: 1, color: '#450a0a' }
                        ],
                        quoteMarkColor: '#fbbf24',
                        quoteTextColor: '#ffffff',
                        accentBarColor: '#fbbf24',
                        nameTextColor: '#ffffff',
                        desigTextColor: '#fecaca',
                        dateTextColor: '#fca5a5',
                    };
                }

                this.canvas.setBackgroundColor(themeConfig.bgColor, () => this.canvas.renderAll());

                // Add Backdrop
                const backdrop = new fabric.Rect({
                    left: 0,
                    top: 0,
                    width: canvasW,
                    height: canvasH,
                    selectable: false,
                    evented: false,
                    customName: '🎨 ব্যাকগ্রাউন্ড থিম',
                });

                if (themeConfig.gradientStops) {
                    backdrop.set('fill', new fabric.Gradient({
                        type: 'linear',
                        gradientUnits: 'pixels',
                        coords: { x1: 0, y1: 0, x2: 0, y2: canvasH },
                        colorStops: themeConfig.gradientStops
                    }));
                } else {
                    backdrop.set('fill', themeConfig.bgColor);
                }
                this.canvas.add(backdrop);
                this.canvas.sendToBack(backdrop);

                // 3. Load & Position Portrait Image
                const placeElements = (portraitImg = null) => {
                    let scaledW = 0;
                    let scaledH = 0;

                    if (portraitImg) {
                        const maxW = canvasW * 0.44;
                        const maxH = canvasH * 0.85;

                        const scale = Math.min(maxW / portraitImg.width, maxH / portraitImg.height);
                        scaledW = portraitImg.width * scale;
                        scaledH = portraitImg.height * scale;

                        const imgLeft = position === 'left' ? (scaledW / 2 + 25) : (canvasW - (scaledW / 2) - 25);
                        const imgTop = canvasH - (scaledH / 2);

                        portraitImg.set({
                            scaleX: scale,
                            scaleY: scale,
                            flipX: flipPhoto,
                            left: imgLeft,
                            top: imgTop,
                            originX: 'center',
                            originY: 'center',
                            selectable: true,
                            customName: '👤 ' + name,
                        });
                        this.canvas.add(portraitImg);
                    }

                    // 4. Calculate Safe Guaranteed Text Geometry
                    const margin = 55;
                    let textLeft = margin;
                    let textWidth = canvasW - (margin * 2);

                    if (portraitImg && scaledW > 0) {
                        if (position === 'left') {
                            textLeft = scaledW + 55;
                            textWidth = canvasW - textLeft - margin;
                        } else {
                            textLeft = margin;
                            textWidth = (canvasW - scaledW - 25) - textLeft - 30;
                        }
                    }

                    const textTop = Math.max(75, canvasH * 0.18);

                    // 5. Add Top Quotation Mark Icon ❝
                    const quoteMarkTop = Math.max(35, Math.round(canvasH * 0.08));
                    const quoteMark = new fabric.Text('❝', {
                        left: textLeft,
                        top: quoteMarkTop,
                        fontSize: Math.round(canvasH * 0.065),
                        fontFamily: 'Georgia, serif',
                        fontWeight: 'bold',
                        fill: themeConfig.quoteMarkColor,
                        selectable: true,
                        isQuoteMark: true,
                        customName: '❝ কোটেশন মার্ক',
                    });
                    this.canvas.add(quoteMark);

                    // 6. Add Quote Textbox
                    const quoteFont = params.fontFamily || 'SolaimanLipi';
                    const quoteTextbox = new fabric.Textbox(quote, {
                        left: textLeft,
                        top: quoteMarkTop + Math.round(canvasH * 0.065) + 14,
                        width: textWidth,
                        fontSize: Math.round(canvasH * 0.044),
                        fontFamily: quoteFont,
                        fontWeight: 'bold',
                        fill: themeConfig.quoteTextColor,
                        lineHeight: 1.4,
                        textAlign: 'left',
                        breakWords: true,
                        selectable: true,
                        isQuoteText: true,
                        customName: '💬 মূল উক্তি',
                    });
                    this.canvas.add(quoteTextbox);

                    // 7. Add Accent Bar
                    const barRect = new fabric.Rect({
                        left: textLeft,
                        top: quoteMarkTop + 200,
                        width: 6,
                        height: 28,
                        fill: themeConfig.accentBarColor,
                        rx: 3,
                        ry: 3,
                        selectable: true,
                        isQuoteBar: true,
                        customName: '🔴 অ্যাকসেন্ট বার',
                    });
                    this.canvas.add(barRect);

                    // 8. Add Speaker Name Textbox
                    const nameText = new fabric.Textbox(name, {
                        left: textLeft + 20,
                        top: quoteMarkTop + 200,
                        width: textWidth - 25,
                        fontSize: Math.round(canvasH * 0.028),
                        fontFamily: quoteFont,
                        fontWeight: 'bold',
                        fill: themeConfig.nameTextColor,
                        selectable: true,
                        isQuoteName: true,
                        customName: '🏷️ ' + name,
                    });
                    this.canvas.add(nameText);

                    // 9. Add Designation Textbox
                    if (designation && designation.trim()) {
                        const desigText = new fabric.Textbox(designation, {
                            left: textLeft + 20,
                            top: quoteMarkTop + 240,
                            width: textWidth - 25,
                            fontSize: Math.round(canvasH * 0.019),
                            fontFamily: quoteFont,
                            fontWeight: 'normal',
                            fill: themeConfig.desigTextColor,
                            selectable: true,
                            isQuoteDesig: true,
                            customName: '📋 ' + designation,
                        });
                        this.canvas.add(desigText);
                    }

                    // 10. Perform precise layout computation and responsive spacing
                    this.recalculateQuoteCardLayout();

                    // 8. Add Today's Date Stamp (Top Right)
                    const todayDate = new Date().toLocaleDateString('bn-BD', { day: 'numeric', month: 'long', year: 'numeric' });
                    const dateText = new fabric.Text(todayDate, {
                        left: canvasW - margin,
                        top: 30,
                        originX: 'right',
                        fontSize: 18,
                        fontFamily: 'SolaimanLipi',
                        fontWeight: 'bold',
                        fill: themeConfig.dateTextColor,
                        selectable: true,
                        customName: '📅 তারিখ',
                    });
                    this.canvas.add(dateText);

                    this.canvas.renderAll();
                    this.hideLoader();
                    this.saveState();
                    this.renderLayersList();
                    this.fitToScreen();
                    this.showNotification("success", "উক্তি কার্ড সফলভাবে তৈরি হয়েছে!");
                };

                // Load image or proceed
                if (finalImageUrl) {
                    fabric.Image.fromURL(finalImageUrl, (img) => {
                        placeElements(img);
                    }, { crossOrigin: 'anonymous' });
                } else {
                    placeElements(null);
                }

            } catch (err) {
                this.hideLoader();
                console.error("Quote Card Generation Error:", err);
                this.showNotification("error", err.message || "উক্তি কার্ড তৈরি করতে সমস্যা হয়েছে।");
            }
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

                const zoomPct = Math.round((active.scaleX || 1) * 100);
                const zoomSlider = document.getElementById('image-zoom-slider');
                const zoomVal = document.getElementById('image-zoom-val');
                const zoomNum = document.getElementById('image-zoom-num');
                if (zoomSlider) zoomSlider.value = zoomPct;
                if (zoomVal) zoomVal.innerText = zoomPct + '%';
                if (zoomNum) zoomNum.value = zoomPct;
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

        setupFloatingToolbarDrag() {
            const toolbar = document.getElementById('floating-context-toolbar');
            const dragHandle = document.getElementById('floating-drag-handle');
            if (!toolbar) return;

            const handle = dragHandle || toolbar;

            let isDragging = false;
            let startClientX = 0;
            let startClientY = 0;
            let startObjLeft = 0;
            let startObjTop = 0;
            let activeObj = null;

            handle.addEventListener('mousedown', (e) => {
                // If clicked on input/button/select inside toolbar, ignore
                if (['BUTTON', 'SELECT', 'INPUT'].includes(e.target.tagName)) {
                    return;
                }

                activeObj = this.canvas.getActiveObject();
                if (!activeObj) return;

                isDragging = true;
                startClientX = e.clientX;
                startClientY = e.clientY;
                startObjLeft = activeObj.left;
                startObjTop = activeObj.top;

                document.body.style.cursor = 'move';
                e.preventDefault();
                e.stopPropagation();
            });

            window.addEventListener('mousemove', (e) => {
                if (!isDragging || !activeObj) return;

                const dx = (e.clientX - startClientX) / this.zoomLevel;
                const dy = (e.clientY - startClientY) / this.zoomLevel;

                activeObj.set({
                    left: startObjLeft + dx,
                    top: startObjTop + dy
                });
                activeObj.setCoords();
                this.canvas.renderAll();
                this.updateFloatingToolbar();
            });

            window.addEventListener('mouseup', () => {
                if (isDragging) {
                    isDragging = false;
                    document.body.style.cursor = 'default';
                    this.saveState();
                }
            });
        }

        setupContextMenu() {
            const contextMenu = document.getElementById('canvas-context-menu');
            if (!contextMenu) return;

            this.canvas.on('mouse:down', (opt) => {
                if (opt.button === 3) { // Right click
                    const target = opt.target;
                    if (target) {
                        this.canvas.setActiveObject(target);
                        this.canvas.renderAll();
                    }
                    const active = this.canvas.getActiveObject();
                    if (active) {
                        opt.e.preventDefault();
                        const x = Math.min(window.innerWidth - 240, opt.e.clientX);
                        const y = Math.min(window.innerHeight - 340, opt.e.clientY);
                        contextMenu.style.top = y + 'px';
                        contextMenu.style.left = x + 'px';
                        contextMenu.classList.remove('hidden');

                        const bgBtn = document.getElementById('context-bg-remove-btn');
                        if (bgBtn) {
                            bgBtn.style.display = (active.type === 'image') ? 'flex' : 'none';
                        }
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
                // Lock (Ctrl + L)
                else if (e.ctrlKey && e.key.toLowerCase() === 'l') {
                    e.preventDefault();
                    this.toggleLockActive();
                }
                // Layer Up (Ctrl + ] or ])
                else if (e.key === ']') {
                    if (active) {
                        e.preventDefault();
                        if (e.ctrlKey) this.bringActiveToFront();
                        else this.bringActiveForward();
                    }
                }
                // Layer Down (Ctrl + [ or [)
                else if (e.key === '[') {
                    if (active) {
                        e.preventDefault();
                        if (e.ctrlKey) this.sendActiveToBack();
                        else this.sendActiveBackward();
                    }
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

        setActiveImageScale(percent) {
            const active = this.canvas.getActiveObject();
            if (active && active.type === 'image') {
                const p = Math.max(10, Math.min(500, parseFloat(percent) || 100));
                const scaleVal = p / 100;
                active.set({
                    scaleX: scaleVal,
                    scaleY: scaleVal,
                });
                active.setCoords();
                this.canvas.renderAll();
                this.saveState();
                this.updateFloatingToolbar();

                const slider = document.getElementById('image-zoom-slider');
                const badge = document.getElementById('image-zoom-val');
                const numInput = document.getElementById('image-zoom-num');
                if (slider) slider.value = Math.round(p);
                if (badge) badge.innerText = Math.round(p) + '%';
                if (numInput) numInput.value = Math.round(p);
            }
        }

        zoomActiveImage(delta) {
            const active = this.canvas.getActiveObject();
            if (active && active.type === 'image') {
                const newScaleX = Math.max(0.05, active.scaleX + delta);
                const newScaleY = Math.max(0.05, active.scaleY + delta);
                active.set({ scaleX: newScaleX, scaleY: newScaleY });
                active.setCoords();
                this.canvas.renderAll();
                this.saveState();
                this.updateFloatingToolbar();

                const p = Math.round(newScaleX * 100);
                const slider = document.getElementById('image-zoom-slider');
                const badge = document.getElementById('image-zoom-val');
                const numInput = document.getElementById('image-zoom-num');
                if (slider) slider.value = p;
                if (badge) badge.innerText = p + '%';
                if (numInput) numInput.value = p;
            }
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
            try {
                // Temporarily discard selection so blue outline boxes don't appear in export
                const active = this.canvas.getActiveObject();
                if (active) {
                    this.canvas.discardActiveObject();
                    this.canvas.renderAll();
                }

                const dataUrl = this.canvas.toDataURL({
                    format: format,
                    multiplier: multiplier,
                    quality: 1.0,
                    enableRetinaScaling: true,
                });

                if (active) {
                    this.canvas.setActiveObject(active);
                    this.canvas.renderAll();
                }

                const link = document.createElement('a');
                link.download = `photocard_${Date.now()}.${format}`;
                link.href = dataUrl;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                this.showNotification("success", "কার্ড ডাউনলোড সফল হয়েছে!");
            } catch (err) {
                console.error("Download Error:", err);
                this.showNotification("error", "ডাউনলোড করতে সমস্যা হয়েছে: " + (err.message || "Canvas Error"));
            }
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

        applySmartContrastShadow(type = 'dark') {
            const active = this.canvas.getActiveObject();
            if (active) {
                if (type === 'none') {
                    this.removeTextShadow();
                    return;
                }

                let shadowColor = 'rgba(0, 0, 0, 0.85)';
                let blur = 8;
                let offsetX = 2;
                let offsetY = 2;

                if (type === 'glow') {
                    shadowColor = 'rgba(255, 255, 255, 0.95)';
                    blur = 14;
                    offsetX = 0;
                    offsetY = 0;
                } else if (type === 'deep') {
                    shadowColor = '#000000';
                    blur = 12;
                    offsetX = 3;
                    offsetY = 4;
                }

                active.set('shadow', new fabric.Shadow({
                    color: shadowColor,
                    blur: blur,
                    offsetX: offsetX,
                    offsetY: offsetY
                }));

                this.canvas.renderAll();
                this.saveState();
                this.syncSidebarWithActiveObject();
                this.showNotification("success", "স্মার্ট কনট্রাস্ট শ্যাডো অ্যাপ্লাই হয়েছে!");
            } else {
                this.showNotification("warning", "প্রথমে ক্যানভাস থেকে কোনো টেক্সট বা উপাদান সিলেক্ট করুন।");
            }
        }

        applyTextBackgroundPill(color = '#000000') {
            const active = this.canvas.getActiveObject();
            if (active && (active.type === 'i-text' || active.type === 'textbox' || active.type === 'text')) {
                if (color === 'none') {
                    this.removeTextBackground();
                    return;
                }

                active.set('backgroundColor', color);
                if (color === '#000000' || color === '#dc2626' || color === '#1e293b' || color === '#4f46e5') {
                    active.set('fill', '#ffffff');
                } else if (color === '#ffffff' || color === '#f8fafc') {
                    active.set('fill', '#0f172a');
                }

                this.canvas.renderAll();
                this.saveState();
                this.syncSidebarWithActiveObject();
                this.showNotification("success", "টেক্সট ব্যাকগ্রাউন্ড পিল অ্যাপ্লাই হয়েছে!");
            } else {
                this.showNotification("warning", "প্রথমে ক্যানভাস থেকে টেক্সট সিলেক্ট করুন।");
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
         * 💾 SAVE & REUSE CUSTOMIZED TEMPLATES / PRESETS
         * ====================================================================
         */
        getCustomTemplates() {
            try {
                const raw = localStorage.getItem('studio_custom_templates_v1');
                return raw ? JSON.parse(raw) : [];
            } catch (e) {
                console.error("Error reading custom templates from localStorage:", e);
                return [];
            }
        }

        saveCustomTemplates(list) {
            try {
                localStorage.setItem('studio_custom_templates_v1', JSON.stringify(list));
            } catch (e) {
                console.error("Error saving custom templates to localStorage:", e);
            }
        }

        async saveCurrentAsTemplate(name = '') {
            const templateName = (name || '').trim() || ('টেমপ্লেট #' + (this.getCustomTemplates().length + 1));
            
            this.showLoader("টেমপ্লেট সেভ হচ্ছে...");

            try {
                // Deselect active object so selection handles don't appear in preview thumbnail
                this.canvas.discardActiveObject();
                this.canvas.renderAll();

                const previewUrl = this.canvas.toDataURL({
                    format: 'jpeg',
                    quality: 0.75,
                    multiplier: 0.25
                });

                const canvasJson = this.canvas.toJSON([
                    'customName', 'selectable', 'lockMovementX', 'lockMovementY', 
                    'lockScalingX', 'lockScalingY', 'lockRotation', 'hasControls',
                    'isQuoteText', 'isQuoteName', 'isQuoteDesig', 'isQuoteMark', 
                    'isQuoteBar', 'isQuotePortrait', 'isFrame'
                ]);

                const quoteParams = {
                    quote: document.getElementById('quote-card-text')?.value || '',
                    name: document.getElementById('quote-card-name')?.value || '',
                    designation: document.getElementById('quote-card-desig')?.value || '',
                    fontFamily: document.getElementById('quote-card-font')?.value || '',
                    theme: document.getElementById('quote-card-theme')?.value || '',
                    position: document.getElementById('quote-card-pos')?.value || 'left',
                    flipPhoto: document.getElementById('quote-card-flip-check')?.checked || false,
                };

                const newTemplate = {
                    id: 'tpl_' + Date.now(),
                    name: templateName,
                    preview: previewUrl,
                    width: this.canvas.getWidth(),
                    height: this.canvas.getHeight(),
                    canvasJson: canvasJson,
                    quoteParams: quoteParams,
                    activeFrame: this.activeFrame,
                    createdAt: new Date().toLocaleDateString('bn-BD', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })
                };

                // Save locally (immediate 0ms availability)
                const templates = this.getCustomTemplates();
                templates.unshift(newTemplate);
                this.saveCustomTemplates(templates);

                // Optional background server sync if endpoint available
                try {
                    const formData = new FormData();
                    formData.append('name', templateName);
                    formData.append('layout_data', JSON.stringify(canvasJson));
                    formData.append('thumbnail', previewUrl);
                    formData.append('frame_url', this.activeFrame || '');
                    formData.append('_token', this.config.csrfToken);
                    fetch('/studio/custom-photo-card/save-template', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.config.csrfToken },
                        body: formData
                    }).catch(() => {});
                } catch (e) {}

                this.hideLoader();
                this.renderCustomTemplatesList();
                this.showNotification("success", `"${templateName}" টেমপ্লেট হিসেবে সফলভাবে সেভ হয়েছে!`);

                // Close modal
                const modal = document.getElementById('save-template-modal');
                if (modal) modal.classList.add('hidden');

            } catch (err) {
                this.hideLoader();
                console.error("Failed to save template:", err);
                this.showNotification("error", "টেমপ্লেট সেভ করতে সমস্যা হয়েছে।");
            }
        }

        loadCustomTemplate(templateId) {
            const templates = this.getCustomTemplates();
            const tpl = templates.find(t => t.id === templateId);
            if (!tpl) {
                this.showNotification("error", "টেমপ্লেটটি খুঁজে পাওয়া যায়নি।");
                return;
            }

            this.showLoader(`"${tpl.name}" টেমপ্লেট লোড হচ্ছে...`);

            if (tpl.width && tpl.height) {
                this.setCanvasDimensions(tpl.width, tpl.height);
            }

            const json = typeof tpl.canvasJson === 'string' ? JSON.parse(tpl.canvasJson) : tpl.canvasJson;

            this.canvas.loadFromJSON(json, () => {
                this.canvas.renderAll();
                this.activeFrame = tpl.activeFrame || null;
                this.hideLoader();
                this.fitToScreen();
                this.saveState();
                this.renderLayersList();

                // If template had quote settings, restore quote inputs in sidebar
                if (tpl.quoteParams) {
                    const qText = document.getElementById('quote-card-text');
                    const qName = document.getElementById('quote-card-name');
                    const qDesig = document.getElementById('quote-card-desig');
                    const qFont = document.getElementById('quote-card-font');
                    const qTheme = document.getElementById('quote-card-theme');
                    const qPos = document.getElementById('quote-card-pos');
                    const qFlip = document.getElementById('quote-card-flip-check');

                    if (qText && tpl.quoteParams.quote) qText.value = tpl.quoteParams.quote;
                    if (qName && tpl.quoteParams.name) qName.value = tpl.quoteParams.name;
                    if (qDesig && tpl.quoteParams.designation) qDesig.value = tpl.quoteParams.designation;
                    if (qFont && tpl.quoteParams.fontFamily) qFont.value = tpl.quoteParams.fontFamily;
                    if (qTheme && tpl.quoteParams.theme) qTheme.value = tpl.quoteParams.theme;
                    if (qPos && tpl.quoteParams.position) qPos.value = tpl.quoteParams.position;
                    if (qFlip) qFlip.checked = (tpl.quoteParams.flipPhoto === true);
                }

                this.showNotification("success", `"${tpl.name}" টেমপ্লেট সফলভাবে লোড হয়েছে!`);
            });
        }

        deleteCustomTemplate(templateId) {
            if (!confirm("আপনি কি নিশ্চিতভাবে এই টেমপ্লেটটি মুছে ফেলতে চান?")) return;

            let templates = this.getCustomTemplates();
            templates = templates.filter(t => t.id !== templateId);
            this.saveCustomTemplates(templates);
            this.renderCustomTemplatesList();
            this.showNotification("info", "টেমপ্লেট মুছে ফেলা হয়েছে।");
        }

        renderCustomTemplatesList() {
            const container = document.getElementById('custom-templates-list');
            if (!container) return;

            const templates = this.getCustomTemplates();
            if (templates.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 px-3 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl space-y-2">
                        <div class="text-3xl">💾</div>
                        <h4 class="text-xs font-black text-slate-700">কোনো সেভ করা টেমপ্লেট নেই</h4>
                        <p class="text-[10px] text-slate-400 max-w-[220px] mx-auto leading-relaxed">
                            বর্তমান ক্যানভাস ডিজাইনটি ভবিষ্যতে রি-ইউজ করার জন্য নিচের বাটনে চাপ দিয়ে সেভ করে রাখুন।
                        </p>
                        <button type="button" onclick="openSaveTemplateModal()" class="mt-2 py-1.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-[11px] rounded-xl transition shadow-xs">
                            + নতুন টেমপ্লেট সেভ করুন
                        </button>
                    </div>
                `;
                return;
            }

            let html = '<div class="grid grid-cols-2 gap-3">';
            templates.forEach(tpl => {
                html += `
                    <div class="group bg-white border border-slate-200 hover:border-indigo-400 rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between">
                        <div class="relative bg-slate-100 aspect-square overflow-hidden cursor-pointer flex items-center justify-center p-1" onclick="window.customStudio.loadCustomTemplate('${tpl.id}')">
                            <img src="${tpl.preview}" alt="${tpl.name}" class="w-full h-full object-contain group-hover:scale-105 transition duration-200">
                            <div class="absolute inset-0 bg-indigo-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <span class="bg-white text-indigo-700 font-black text-[10px] px-2.5 py-1 rounded-lg shadow-md flex items-center gap-1">
                                    ⚡ লোড করুন
                                </span>
                            </div>
                        </div>
                        <div class="p-2.5 space-y-1.5 bg-white border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <h4 class="font-black text-xs text-slate-800 truncate" title="${tpl.name}">${tpl.name}</h4>
                                <button type="button" onclick="window.customStudio.deleteCustomTemplate('${tpl.id}')" class="text-slate-400 hover:text-red-600 p-1 transition" title="ডিলিট">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                </button>
                            </div>
                            <div class="flex items-center justify-between text-[9px] text-slate-400 font-bold">
                                <span>${tpl.createdAt}</span>
                                <span>${tpl.width}×${tpl.height}</span>
                            </div>
                            <button type="button" onclick="window.customStudio.loadCustomTemplate('${tpl.id}')" class="w-full py-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white font-bold text-[10px] rounded-lg transition text-center flex items-center justify-center gap-1">
                                <span>⚡ ব্যবহার করুন</span>
                            </button>
                        </div>
                    </div>
                `;
            });
            html += '</div>';

            container.innerHTML = html;
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

