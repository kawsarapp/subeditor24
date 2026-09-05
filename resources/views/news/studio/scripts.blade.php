<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js"></script>

<script>
    // ==========================================
    // ⚙️ ১. কনফিগারেশন (partials ফোল্ডার থেকে লোড হচ্ছে)
    // ==========================================
    @include('partials.studio_fonts')
    @include('partials.studio_templates')

    // ==========================================
    // 🌍 ২. গ্লোবাল ভেরিয়েবল
    // ==========================================
    var canvas, mainImageObj = null, frameObj = null, currentLayout = null; 
    let history = [], historyStep = -1, isHistoryProcessing = false, currentZoom = 1;
    
    var savedPrefs = {};
    try { savedPrefs = JSON.parse(localStorage.getItem('studio_prefs')) || {}; } catch (e) {}
    var dbPrefs = {!! json_encode($settings->design_preferences ?? null) !!};

    // 🔥 DB Templates এর layout_data ইনজেক্ট (key = 'custom_db_5' format)
    window.DB_LAYOUTS = {};
    window.DB_FONT_URLS = {}; // 🔤 Custom font URLs for DB templates
    @foreach($availableTemplates as $template)
        @if($template['layout'] === 'dynamic' && !empty($template['layout_data']))
            window.DB_LAYOUTS['{{ $template['key'] }}'] = {!! json_encode($template['layout_data']) !!};
            @if(!empty($template['font_url']))
            window.DB_FONT_URLS['{{ $template['key'] }}'] = '{{ $template['font_url'] }}';
            @endif
        @endif
    @endforeach

    var userSettings = {
        logo: {!! json_encode($settings->logo_url ?? null) !!},
        template: 'custom_png',
        font: savedPrefs.font || dbPrefs?.font || "'Hind Siliguri', sans-serif",
        color: savedPrefs.color || dbPrefs?.color || '#ffffff',
        bg: savedPrefs.bg || dbPrefs?.bg || '',
        size: savedPrefs.size || dbPrefs?.size || 60,
        frameUrl: savedPrefs.frameUrl || dbPrefs?.frameUrl || null,
        titlePos: savedPrefs.titlePos || dbPrefs?.titlePos || null, 
        datePos: savedPrefs.datePos || dbPrefs?.datePos || null,
        layout: savedPrefs.layout || dbPrefs?.layout || 'bottom',
        templateKey: savedPrefs.templateKey || dbPrefs?.templateKey || null
    };
    
    var newsData = {
        title: {!! json_encode(!empty($newsItem->ai_title) ? $newsItem->ai_title : $newsItem->title) !!},
        image: "{{ $newsItem->thumbnail_url ? route('proxy.image', ['url' => $newsItem->thumbnail_url]) : '' }}"
    };

    window.qrTargetUrl = "{{ !empty($newsItem->original_link) ? $newsItem->original_link : route('news.public-preview', $newsItem->id) }}";

    // ==========================================
    // 🚀 ৩. ক্যানভাস ইনিশিয়ালাইজেশন ও কোর ফাংশন
    // ==========================================
    document.addEventListener("DOMContentLoaded", function() { initCanvas(); });

    function initCanvas() {
        canvas = new fabric.Canvas('newsCanvas', { backgroundColor: '#fff', preserveObjectStacking: true, selection: true, renderOnAddRemove: false });

        setTimeout(() => { loadStoredCustomFont(); loadFonts(); }, 10);

        if (newsData.image) {
            fabric.Image.fromURL(newsData.image, function(img) {
                if (img) { setupMainImage(img); canvas.requestRenderAll(); }
                restoreSavedDesign(); 
                canvas.set('renderOnAddRemove', true);
                canvas.requestRenderAll();
                setTimeout(regenerateQrCode, 400);
            }, { crossOrigin: 'anonymous' });
        } else {
            restoreSavedDesign();
            canvas.set('renderOnAddRemove', true);
            setTimeout(regenerateQrCode, 400);
        }

        canvas.on('selection:created', updateSidebarValues);
        canvas.on('selection:updated', updateSidebarValues);
        canvas.on('object:added', () => { saveHistory(); renderLayerList(); });
        canvas.on('object:removed', () => { saveHistory(); renderLayerList(); });
        canvas.on('object:modified', () => { saveHistory(); }); 
        
        initKeyboardEvents();
        activateDebugTools();
        setTimeout(fitToScreen, 50); 
        window.addEventListener('resize', fitToScreen);
    }

    // ==========================================
    // 🔳 QR Code Overlay Generator
    // ==========================================
    window.addOrUpdateQrCodeOnCanvas = function(url, position, size) {
        url = url || window.qrTargetUrl;
        position = position || (document.getElementById('qr-position-select')?.value || 'bottom-right');
        size = size || 120;

        if (!url || typeof QRCode === 'undefined') return;

        let tempDiv = document.createElement('div');
        tempDiv.style.position = 'absolute';
        tempDiv.style.left = '-9999px';
        document.body.appendChild(tempDiv);

        try {
            new QRCode(tempDiv, {
                text: url,
                width: size,
                height: size,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            setTimeout(() => {
                let imgEl = tempDiv.querySelector('img') || tempDiv.querySelector('canvas');
                let dataUrl = imgEl ? (imgEl.src || (imgEl.toDataURL ? imgEl.toDataURL() : '')) : '';
                if (tempDiv.parentNode) tempDiv.parentNode.removeChild(tempDiv);

                if (!dataUrl) return;

                fabric.Image.fromURL(dataUrl, function(qrImg) {
                    if (!qrImg) return;

                    const existingQr = canvas.getObjects().find(o => o.isQrCode);
                    if (existingQr) {
                        canvas.remove(existingQr);
                    }

                    let canvasW = canvas.width || 1080;
                    let canvasH = canvas.height || 1080;
                    let left = canvasW - size - 35;
                    let top = canvasH - size - 35;

                    if (position === 'bottom-left') {
                        left = 35;
                        top = canvasH - size - 35;
                    } else if (position === 'top-right') {
                        left = canvasW - size - 35;
                        top = 35;
                    } else if (position === 'top-left') {
                        left = 35;
                        top = 35;
                    }

                    qrImg.set({
                        left: left,
                        top: top,
                        selectable: true,
                        isQrCode: true,
                        hasBorders: true,
                        padding: 4,
                        cornerColor: '#4f46e5',
                        cornerSize: 8
                    });

                    canvas.add(qrImg);
                    canvas.bringToFront(qrImg);
                    canvas.requestRenderAll();
                });
            }, 100);
        } catch(err) {
            if (tempDiv.parentNode) tempDiv.parentNode.removeChild(tempDiv);
            console.error("QR Code Error:", err);
        }
    };

    window.toggleQrCodeOnCanvas = function(enabled) {
        const existingQr = canvas.getObjects().find(o => o.isQrCode);
        if (!enabled && existingQr) {
            canvas.remove(existingQr);
            canvas.requestRenderAll();
        } else if (enabled) {
            window.regenerateQrCode();
        }
    };

    window.changeQrCodePosition = function(position) {
        const check = document.getElementById('qr-toggle-check');
        if (check && !check.checked) check.checked = true;
        window.addOrUpdateQrCodeOnCanvas(window.qrTargetUrl, position);
    };

    window.regenerateQrCode = function() {
        const check = document.getElementById('qr-toggle-check');
        if (check && !check.checked) return;
        const pos = document.getElementById('qr-position-select')?.value || 'bottom-right';
        window.addOrUpdateQrCodeOnCanvas(window.qrTargetUrl, pos);
    };

    function fitToScreen() {
        const container = document.getElementById('workspace-container');
        const wrapper = document.getElementById('canvas-wrapper');
        if (!container || !wrapper || !canvas) return;
        const canvasW = canvas.getWidth() || 1080;
        const canvasH = canvas.getHeight() || 1080;
        const scale = Math.min((container.clientWidth - 40) / canvasW, (container.clientHeight - 40) / canvasH);
        currentZoom = Math.min(scale, 1); 
        updateZoomDisplay();
    }
	
	function changeZoom(delta) {
        currentZoom += delta;
        if (currentZoom < 0.1) currentZoom = 0.1;
        if (currentZoom > 2.0) currentZoom = 2.0;
        updateZoomDisplay();
    }
	
	function updateZoomDisplay() {
        const wrapper = document.getElementById('canvas-wrapper');
        const zoomText = document.getElementById('zoom-level');
        if (wrapper) wrapper.style.transform = `scale(${currentZoom})`;
        if (zoomText) zoomText.innerText = Math.round(currentZoom * 100) + "%";
    }

    function setupMainImage(img) {
        if (mainImageObj) canvas.remove(mainImageObj);
        const scale = Math.max(canvas.width / img.width, canvas.height / img.height);
        img.set({ scaleX: scale, scaleY: scale, left: canvas.width / 2, top: canvas.height / 2, originX: 'center', originY: 'center', selectable: true, isMainImage: true });
        mainImageObj = img; canvas.add(img); canvas.sendToBack(img);
    }

    window.controlMainImage = function(action, value) {
        let img = canvas.getObjects().find(o => o.isMainImage);
        if (!img) { alert("❌ কোনো নিউজ ইমেজ পাওয়া যায়নি!"); return; }
        switch (action) {
            case 'zoom': let newScale = img.scaleX + value; if (newScale > 0.1) img.set({ scaleX: newScale, scaleY: newScale }); break;
            case 'moveX': img.set('left', img.left + value); break;
            case 'moveY': img.set('top', img.top + value); break;
            case 'reset': const scale = Math.max(canvas.width / img.width, canvas.height / img.height); img.set({ scaleX: scale, scaleY: scale, left: canvas.width / 2, top: canvas.height / 2, originX: 'center', originY: 'center' }); break;
        }
        img.setCoords(); canvas.requestRenderAll(); saveHistory();
    };

    // ==========================================
    // 🎨 ৪. টেমপ্লেট ও ডিজাইন অ্যাপ্লাই লজিক
    // ==========================================
    window.applyAdminTemplate = function(imageUrl, layoutName, isRestore = false, templateKey = null) {
        console.log("🚀 Applying Template:", layoutName, templateKey);
        if (!isRestore) { window.userSettings.titlePos = null; window.userSettings.datePos = null; }
        currentLayout = layoutName; userSettings.frameUrl = imageUrl;
        if (templateKey) userSettings.templateKey = templateKey;

        const objects = canvas.getObjects();
        let titleObj = objects.find(obj => obj.isHeadline);
        let dateObj = objects.find(obj => obj.isDate);
        let mainImgObj = objects.find(obj => obj.isMainImage);

        for (let i = objects.length - 1; i >= 0; i--) {
            let obj = objects[i];
            if (!obj.isMainImage && !obj.isHeadline && !obj.isDate) canvas.remove(obj);
        }

        if(!titleObj) {
            titleObj = new fabric.Textbox(newsData.title || "Headline Here", { left: 50, top: 800, width: 980, fontSize: 60, fill: '#ffffff', fontFamily: 'Hind Siliguri', fontWeight: 'bold', textAlign: 'center', isHeadline: true });
            canvas.add(titleObj);
        }

        fabric.Image.fromURL(imageUrl, function(img) {
            if (!img) return;

            const naturalW = img.naturalWidth || img.width;
            const naturalH = img.naturalHeight || img.height;

            // 🔥 DYNAMIC NATURAL FRAME SIZING (NO 1:1 FORCING)
            if (naturalW && naturalH && (canvas.width !== naturalW || canvas.height !== naturalH)) {
                canvas.setWidth(naturalW);
                canvas.setHeight(naturalH);
                fitToScreen();
            }

            img.set({ left: 0, top: 0, scaleX: canvas.width / img.width, scaleY: canvas.height / img.height, selectable: false, evented: false, isFrame: true });
            window.frameObj = img; canvas.add(img);

            if(mainImgObj) canvas.sendToBack(mainImgObj);
            canvas.sendToBack(img);
            if(mainImgObj) canvas.bringForward(img);
            if(titleObj) canvas.bringToFront(titleObj);
            if(dateObj) canvas.bringToFront(dateObj);

            // 🔥 Dynamic (DB) template: DB_LAYOUTS থেকে layout নাও, fallback = bottom
            let targetLayout;
            if (layoutName === 'dynamic' && templateKey && window.DB_LAYOUTS[templateKey]) {
                targetLayout = window.DB_LAYOUTS[templateKey];

                // 🔤 এই DB template এ custom font_url আছে কিনা চেক করো → auto-load
                const dbFontUrl = window.DB_FONT_URLS && window.DB_FONT_URLS[templateKey];
                if (dbFontUrl) {
                    applyCustomFont('CustomFont', dbFontUrl);
                    console.log('🔤 Custom font loaded from DB template:', dbFontUrl);
                }
            } else {
                targetLayout = STUDIO_TEMPLATES[layoutName] || STUDIO_TEMPLATES['bottom'];
            }

            // Image Zooming
            if (mainImgObj && targetLayout.image) {
                const imgConfig = targetLayout.image;
                let finalScale = Math.max(imgConfig.width / mainImgObj.width, imgConfig.height / mainImgObj.height) * (imgConfig.zoom !== undefined ? imgConfig.zoom : 1.0);
                mainImgObj.set({ scaleX: finalScale, scaleY: finalScale, left: imgConfig.left + (imgConfig.width / 2), top: imgConfig.top + (imgConfig.height / 2), originX: 'center', originY: 'center', clipPath: null });
                mainImgObj.setCoords();
            } else if (mainImgObj) {
                const scale = Math.max(canvas.width / mainImgObj.width, canvas.height / mainImgObj.height);
                mainImgObj.set({ scaleX: scale, scaleY: scale, left: canvas.width / 2, top: canvas.height / 2, originX: 'center', originY: 'center', clipPath: null });
                mainImgObj.setCoords();
            }

            // Title Positioning
            if(titleObj) {
                if (isRestore && window.userSettings?.titlePos) { titleObj.set(window.userSettings.titlePos); } 
                else {
                    const config = targetLayout.title;
                    titleObj.set({ top: config.top, left: config.left, width: config.width, textAlign: config.textAlign, originX: config.originX, fontSize: config.fontSize, backgroundColor: config.backgroundColor, fill: config.fill, fontFamily: config.fontFamily });
                    if(config.fontFamily && !config.fontFamily.includes('📂')) WebFont.load({ google: { families: [config.fontFamily.replace(/'/g, "").split(',')[0].trim()] } });
                    updateUI(config.fontSize, config.fill, config.fontFamily);
                    Object.assign(userSettings, { color: config.fill, font: config.fontFamily, size: config.fontSize, bg: config.backgroundColor });
                }
                titleObj.setCoords(); 
            }

            // Date Positioning
            if(dateObj) {
                if (isRestore && window.userSettings?.datePos) { dateObj.set(window.userSettings.datePos); } 
                else {
                    const dConfig = targetLayout.date;
                    dateObj.set({ top: dConfig.top, left: dConfig.left, originX: dConfig.originX, fontSize: dConfig.fontSize, fill: dConfig.fill, backgroundColor: dConfig.backgroundColor, fontFamily: dConfig.fontFamily });
                }
                dateObj.setCoords();
            }

            canvas.requestRenderAll(); saveHistory();
        }, { crossOrigin: 'anonymous' });
    };

    function restoreSavedDesign() {
        if (userSettings.frameUrl) { 
            applyAdminTemplate(userSettings.frameUrl, userSettings.layout || 'bottom', true, userSettings.templateKey || null); 
        } else {
            let titleObj = canvas.getObjects().find(o => o.isHeadline);
            if(!titleObj) { titleObj = new fabric.Textbox(newsData.title, { left: 50, top: 800, width: 980, fontSize: 60, fill: '#000', fontFamily: 'Hind Siliguri', fontWeight: 'bold', textAlign: 'center', isHeadline: true }); canvas.add(titleObj); }
        }
        setTimeout(() => {
            let titleObj = canvas.getObjects().find(o => o.isHeadline);
            if (titleObj) {
                let fontName = userSettings.font;
                if(!fontName.includes('📂')) WebFont.load({ google: { families: [fontName.replace(/'/g, "").split(',')[0].trim()] } });
                titleObj.set({ fill: userSettings.color, fontSize: parseInt(userSettings.size), backgroundColor: userSettings.bg, fontFamily: fontName });
                updateUI(userSettings.size, userSettings.color, userSettings.font); canvas.requestRenderAll();
            }
        }, 600);
        if (userSettings.logo) addProfileLogo(userSettings.logo);
        addDateText();
    }

    function saveCurrentDesign() {
        const titleObj = canvas.getObjects().find(obj => obj.isHeadline);
        const dateObj = canvas.getObjects().find(obj => obj.isDate);
        const preferences = {
            template: userSettings.template, frameUrl: userSettings.frameUrl,
            font: titleObj ? titleObj.fontFamily : userSettings.font, color: titleObj ? titleObj.fill : userSettings.color,
            bg: titleObj ? titleObj.backgroundColor : userSettings.bg, size: titleObj ? titleObj.fontSize : userSettings.size,
            titlePos: titleObj ? { left: titleObj.left, top: titleObj.top, width: titleObj.width, textAlign: titleObj.textAlign, originX: titleObj.originX, fill: titleObj.fill, fontFamily: titleObj.fontFamily } : null, 
            datePos: dateObj ? { left: dateObj.left, top: dateObj.top, originX: dateObj.originX } : null, 
            layout: currentLayout || userSettings.layout,
            templateKey: userSettings.templateKey || null   // 🔥 DB template key সেভ
        };
        fetch("{{ route('settings.save-design') }}", { method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') }, body: JSON.stringify({ preferences }) })
        .then(res => res.json()).then(data => { if (data.success) { alert("✅ ডিফল্ট ডিজাইন সেভ হয়েছে!"); localStorage.setItem('studio_prefs', JSON.stringify(preferences)); Object.assign(userSettings, preferences); } });
    }

    // ==========================================
    // 💾 SAVE & REUSE CUSTOM STUDIO TEMPLATES
    // ==========================================
    window.openSaveTemplateModal = function() {
        const modal = document.getElementById('saveTemplateModal');
        if (modal) {
            modal.classList.remove('hidden');
            const input = document.getElementById('studioTemplateNameInput');
            if (input) {
                const count = document.querySelectorAll('#mySavedTemplatesGrid > div[id^="my-template-card-"]').length;
                input.value = 'আমার টেমপ্লেট #' + (count + 1);
                setTimeout(() => input.focus(), 50);
            }
        }
    };

    window.closeSaveTemplateModal = function() {
        const modal = document.getElementById('saveTemplateModal');
        if (modal) modal.classList.add('hidden');
    };

    window.submitSaveTemplate = function() {
        const nameInput = document.getElementById('studioTemplateNameInput');
        const name = (nameInput?.value || '').trim();
        if (!name) {
            alert('⚠️ অনুগ্রহ করে টেমপ্লেটের একটি নাম লিখুন!');
            nameInput?.focus();
            return;
        }

        const titleObj = canvas.getObjects().find(obj => obj.isHeadline);
        const dateObj = canvas.getObjects().find(obj => obj.isDate);
        const mainImg = canvas.getObjects().find(obj => obj.isMainImage);

        let imageConfig = { left: 40, top: 120, width: 1000, height: 450, zoom: 1.1 };
        if (mainImg) {
            const scaleX = mainImg.scaleX || 1;
            const scaleY = mainImg.scaleY || 1;
            imageConfig = {
                left: mainImg.left - ((mainImg.width * scaleX) / 2),
                top: mainImg.top - ((mainImg.height * scaleY) / 2),
                width: mainImg.width * scaleX,
                height: mainImg.height * scaleY,
                zoom: 1.0
            };
        }

        const layoutData = {
            title: titleObj ? {
                top: titleObj.top,
                left: titleObj.left,
                width: titleObj.width,
                textAlign: titleObj.textAlign || 'center',
                originX: titleObj.originX || 'center',
                fontSize: titleObj.fontSize || 50,
                fill: titleObj.fill || '#ffffff',
                fontFamily: titleObj.fontFamily || 'Hind Siliguri',
                backgroundColor: titleObj.backgroundColor || ''
            } : { top: 800, left: 540, width: 980, textAlign: 'center', originX: 'center', fontSize: 60, fill: '#ffffff', fontFamily: 'Hind Siliguri', backgroundColor: '' },
            date: dateObj ? {
                top: dateObj.top,
                left: dateObj.left,
                originX: dateObj.originX || 'left',
                fontSize: dateObj.fontSize || 30,
                fill: dateObj.fill || '#ffffff',
                backgroundColor: dateObj.backgroundColor || '',
                fontFamily: dateObj.fontFamily || 'Hind Siliguri'
            } : { top: 50, left: 50, originX: 'left', fontSize: 30, fill: '#ffffff', backgroundColor: '', fontFamily: 'Hind Siliguri' },
            image: imageConfig
        };

        const thumbnail = canvas.toDataURL({ format: 'png', multiplier: 0.25, quality: 0.8 });

        const btn = document.getElementById('btnSubmitSaveTemplate');
        const origText = btn ? btn.innerHTML : '';
        if (btn) { btn.innerHTML = '⏳ সেভ হচ্ছে...'; btn.disabled = true; }

        const payload = {
            name: name,
            frame_url: userSettings.frameUrl || null,
            layout_data: layoutData,
            thumbnail: thumbnail
        };

        fetch("{{ route('news.studio.save-template') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.template) {
                const tpl = data.template;
                
                if (!window.DB_LAYOUTS) window.DB_LAYOUTS = {};
                window.DB_LAYOUTS[tpl.key] = tpl.layout_data;

                const grid = document.getElementById('mySavedTemplatesGrid');
                const emptyMsg = document.getElementById('noSavedTemplatesMsg');
                if (emptyMsg) emptyMsg.remove();

                const cardHtml = `
                    <div id="my-template-card-${tpl.id}" class="relative group border border-slate-200 rounded-xl overflow-hidden bg-white hover:border-indigo-500 transition shadow-xs">
                        <div onclick="applyAdminTemplate('${tpl.frame_url || ''}', 'dynamic', false, '${tpl.key}')" class="cursor-pointer p-1">
                            <img src="${tpl.thumbnail_url || tpl.frame_url}" alt="${tpl.name}" loading="lazy" class="w-full h-16 object-contain bg-slate-100 rounded-lg">
                            <p class="text-[10px] text-center font-bold text-slate-700 truncate mt-1 group-hover:text-indigo-600">${tpl.name}</p>
                        </div>
                        <button type="button" onclick="deleteSavedTemplate(${tpl.id}, event)" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 bg-rose-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] shadow-md transition hover:scale-110 cursor-pointer" title="টেমপ্লেট মুছুন">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                `;

                if (grid) {
                    grid.insertAdjacentHTML('afterbegin', cardHtml);
                }

                const badge = document.getElementById('myTemplatesCountBadge');
                if (badge) {
                    const count = document.querySelectorAll('#mySavedTemplatesGrid > div[id^="my-template-card-"]').length;
                    badge.innerText = count;
                }

                closeSaveTemplateModal();
                alert(`✅ "${tpl.name}" টেমপ্লেট সফলভাবে সংরক্ষিত হয়েছে! এখন যেকোনো নিউজে ১-ক্লিকে রিইউজ করতে পারবেন।`);
            } else {
                alert("❌ " + (data.message || 'টেমপ্লেট সেভ করতে সমস্যা হয়েছে'));
            }
        })
        .catch(err => {
            console.error("Save Template Error:", err);
            alert("❌ সেভ করতে ত্রুটি হয়েছে!");
        })
        .finally(() => {
            if (btn) { btn.innerHTML = origText; btn.disabled = false; }
        });
    };

    window.deleteSavedTemplate = function(templateId, e) {
        if (e) e.stopPropagation();
        if (!confirm('আপনি কি নিশ্চিতভাবে এই টেমপ্লেটটি মুছে ফেলতে চান?')) return;

        fetch(`/news/studio/delete-template/${templateId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const el = document.getElementById(`my-template-card-${templateId}`);
                if (el) el.remove();

                const badge = document.getElementById('myTemplatesCountBadge');
                const remaining = document.querySelectorAll('#mySavedTemplatesGrid > div[id^="my-template-card-"]').length;
                if (badge) badge.innerText = remaining;

                if (remaining === 0) {
                    const grid = document.getElementById('mySavedTemplatesGrid');
                    if (grid) {
                        grid.innerHTML = `
                            <div id="noSavedTemplatesMsg" class="col-span-2 py-4 text-center">
                                <p class="text-[11px] text-slate-400 font-semibold mb-1">কোনো সেভ করা টেমপ্লেট নেই</p>
                                <button type="button" onclick="openSaveTemplateModal()" class="text-[10px] font-bold text-indigo-600 hover:underline">
                                    + বর্তমান ডিজাইন সেভ করুন
                                </button>
                            </div>
                        `;
                    }
                }
            } else {
                alert("❌ " + (data.message || 'ডিলিট করতে ব্যর্থ হয়েছে'));
            }
        })
        .catch(err => alert("❌ ডিলিট করতে সমস্যা হয়েছে"));
    };

    // ==========================================
    // 🔤 ৫. ফন্ট ম্যানেজমেন্ট
    // ==========================================
    // ==========================================
    function smartLoadFont(fontName, callback) {
        if (!fontName) return callback();
        let cleanFont = fontName.replace(/'/g, "").split(',')[0].trim();
        if (cleanFont.includes('📂 ')) cleanFont = cleanFont.replace('📂 ', '');
        
        // Skip webfont loader for Local Bangla Fonts that are pre-loaded via CSS
        const skipLoaderFonts = ['SolaimanLipi', 'Noto Serif Cond', 'AdorshoLipi', 'Kalpurush', 'Siyam Rupali', 'Hind Siliguri'];
        
        if (skipLoaderFonts.includes(cleanFont)) {
            let chk = setInterval(() => {
                if (document.fonts.check(`12px "${cleanFont}"`)) { clearInterval(chk); callback(); }
            }, 100);
            setTimeout(() => { clearInterval(chk); callback(); }, 1500); // 1.5s timeout fallback
            return;
        }

        if (STUDIO_FONTS.local.includes(cleanFont)) { callback(); } 
        else { WebFont.load({ google: { families: [cleanFont + ':400,700'] }, active: callback, inactive: callback }); }
    }

    function loadFonts() {
        WebFont.load({ 
            google: { families: STUDIO_FONTS.google }, 
            custom: { families: STUDIO_FONTS.local }, 
            active: function() { 
                console.log("✅ All Standard Fonts Loaded!"); 
                if(canvas) canvas.requestRenderAll(); 
            } 
        });
    }

    window.uploadCustomFont = function(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const fontName = file.name.split('.')[0]; 
                const fontUrl = e.target.result;
                applyCustomFont(fontName, fontUrl);
                try { localStorage.setItem('custom_font_name', fontName); localStorage.setItem('custom_font_url', fontUrl); alert(`✅ ফন্ট '${fontName}' সেভ হয়েছে!`); } 
                catch (err) { alert("⚠️ ফন্টটি বড় হওয়ায় ব্রাউজারে সেভ করা যায়নি, তবে এখন ব্যবহার করতে পারবেন।"); }
            };
            reader.readAsDataURL(file);
        }
    };

    function applyCustomFont(fontName, fontUrl) {
        const newFont = new FontFace(fontName, `url(${fontUrl})`);
        newFont.load().then(function(loadedFont) {
            document.fonts.add(loadedFont);
            const select = document.getElementById('font-family');
            if(select && !Array.from(select.options).some(opt => opt.value === fontName)) { select.add(new Option("📂 " + fontName, fontName), select.options[0]); }
            select.value = fontName;
            const obj = canvas.getActiveObject();
            if (obj && (obj.type === 'text' || obj.type === 'textbox')) { obj.set("fontFamily", fontName); canvas.requestRenderAll(); saveHistory(); }
            userSettings.font = fontName;
        });
    }

    function loadStoredCustomFont() {
        const storedName = localStorage.getItem('custom_font_name'), storedUrl = localStorage.getItem('custom_font_url');
        if (storedName && storedUrl) applyCustomFont(storedName, storedUrl);
    }

    function changeFont(fontName) {
        const obj = canvas.getActiveObject();
        if (!obj) return;
        if(fontName.includes('📂')) { obj.set("fontFamily", fontName.replace('📂 ', '')); canvas.requestRenderAll(); saveHistory(); return; }
        const cleanFont = fontName.replace(/'/g, "").split(',')[0].trim();
        if (STUDIO_FONTS.local.includes(cleanFont)) { obj.set("fontFamily", cleanFont); canvas.requestRenderAll(); saveHistory(); if(obj.isHeadline) savePreference('font', fontName); } 
        else { WebFont.load({ google: { families: [cleanFont + ':400,700'] }, active: function() { obj.set("fontFamily", cleanFont); canvas.requestRenderAll(); if(obj.isHeadline) savePreference('font', fontName); saveHistory(); } }); }
    }

    // ==========================================
    // 📑 ৬. লেয়ার ম্যানেজমেন্ট
    // ==========================================
    window.renderLayerList = function() {
        const container = document.getElementById('layer-list-container');
        if (!container) return; container.innerHTML = '';
        const objects = canvas.getObjects().slice().reverse();
        if (objects.length === 0) { container.innerHTML = '<p class="text-xs text-gray-400 text-center">কোনো লেয়ার নেই</p>'; return; }

        objects.forEach((obj, index) => {
            const realIndex = objects.length - 1 - index;
            let name = "Shape / Rect", icon = "🟦";
            if (obj.isMainImage) { name = "News Image"; icon = "🖼️"; } else if (obj.isFrame) { name = "Frame / Overlay"; icon = "🔲"; } else if (obj.isHeadline) { name = "Headline Text"; icon = "📝"; } else if (obj.isDate) { name = "Date Text"; icon = "📅"; } else if (obj.type === 'image') { name = "Logo / Image"; icon = "📷"; } else if (obj.type === 'text' || obj.type === 'textbox') { name = "Custom Text"; icon = "✍️"; }
            const isActive = canvas.getActiveObject() === obj ? "border-indigo-500 bg-indigo-50" : "border-gray-200 bg-white";
            container.innerHTML += `<div class="flex items-center justify-between p-2 border rounded-lg ${isActive} hover:bg-gray-50 transition group cursor-pointer" onclick="selectLayer(${realIndex})"><div class="flex items-center gap-2 truncate"><span class="text-lg">${icon}</span><span class="text-xs font-bold text-gray-700 truncate w-32">${name}</span></div><div class="flex gap-1 opacity-60 group-hover:opacity-100"><button onclick="toggleVisibility(event, ${realIndex})" class="p-1 hover:text-blue-600">${obj.visible ? '👁️' : '🚫'}</button><button onclick="toggleLock(event, ${realIndex})" class="p-1 hover:text-red-600">${obj.lockMovementX ? '🔒' : '🔓'}</button><button onclick="deleteLayer(event, ${realIndex})" class="p-1 hover:text-red-600">🗑️</button></div></div>`;
        });
    };

    window.selectLayer = function(index) { const obj = canvas.item(index); if (obj) { canvas.setActiveObject(obj); canvas.renderAll(); renderLayerList(); } };
    window.toggleVisibility = function(e, index) { e.stopPropagation(); const obj = canvas.item(index); if (obj) { obj.visible = !obj.visible; if (!obj.visible) canvas.discardActiveObject(); canvas.renderAll(); renderLayerList(); } };
    window.toggleLock = function(e, index) { e.stopPropagation(); const obj = canvas.item(index); if (obj) { const isLocked = !obj.lockMovementX; obj.set({ lockMovementX: isLocked, lockMovementY: isLocked, lockScalingX: isLocked, lockScalingY: isLocked, lockRotation: isLocked, selectable: !isLocked }); canvas.renderAll(); renderLayerList(); } };
    window.deleteLayer = function(e, index) { e.stopPropagation(); if(confirm('ডিলিট করতে চান?')) { canvas.remove(canvas.item(index)); saveHistory(); renderLayerList(); } };
    window.moveLayer = function(direction) { const obj = canvas.getActiveObject(); if(!obj) return; if(direction === 'up') canvas.bringForward(obj); if(direction === 'down') canvas.sendBackwards(obj); if(direction === 'top') canvas.bringToFront(obj); if(direction === 'bottom') canvas.sendToBack(obj); canvas.renderAll(); saveHistory(); renderLayerList(); };

    // ==========================================
    // 🌐 ৭. এপিআই এবং পোস্টিং (Web & Social)
    // ==========================================
    function dataURLToBlob(dataURL) {
        var arr = dataURL.split(','), mime = arr[0].match(/:(.*?);/)[1], bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
        while(n--) u8arr[n] = bstr.charCodeAt(n);
        return new Blob([u8arr], {type:mime});
    }

    function postDirectFromStudio() {
        const isSocialOnly = document.getElementById('socialOnlyCheck').checked;
        if (!confirm(isSocialOnly ? "⚠️ 'Only Social' সিলেক্ট করেছেন। নিশ্চিত?" : "সরাসরি পোস্ট করতে চান?")) return;
        const btn = document.querySelector('button[onclick="postDirectFromStudio()"]'); const originalText = btn.innerHTML; btn.innerHTML = "⏳ Uploading..."; btn.disabled = true;
        canvas.discardActiveObject(); canvas.renderAll();
        try {
            const formData = new FormData(); formData.append('design_image', dataURLToBlob(canvas.toDataURL({ format: 'png', multiplier: 1.5, quality: 1.0 })), 'studio-final.png');
            if (isSocialOnly) formData.append('social_only', '1');
            fetch("{{ route('news.publish-studio', $newsItem->id) }}", { method: "POST", headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, body: formData })
            .then(res => res.json()).then(data => { if (data.success) { alert("✅ পাঠানো হয়েছে!"); window.location.href = "{{ route('news.index') }}"; } else { alert("❌ " + data.message); btn.innerHTML = originalText; btn.disabled = false; } });
        } catch (error) { alert("❌ ক্যানভাস এরর।"); btn.innerHTML = originalText; btn.disabled = false; }
    }

    function toggleAllFbPages(checked) {
        document.querySelectorAll('.fb-page-checkbox').forEach(cb => cb.checked = checked);
    }

    function checkSelectAllState() {
        const total = document.querySelectorAll('.fb-page-checkbox').length;
        const checked = document.querySelectorAll('.fb-page-checkbox:checked').length;
        const selectAllCb = document.getElementById('selectAllFbPages');
        if (selectAllCb) {
            selectAllCb.checked = (total > 0 && total === checked);
            selectAllCb.indeterminate = (checked > 0 && checked < total);
        }
    }

    // Call this once on load to set the initial toggle state properly
    document.addEventListener("DOMContentLoaded", function() { setTimeout(checkSelectAllState, 500); });

    function confirmStudioPost() {
        const isSocialOnly = document.getElementById('modalSocialOnly').checked, categoryId = document.getElementById('modalCategory').value, caption = document.getElementById('modalCaption').value;
        if (!isSocialOnly && !categoryId) { alert("⚠️ ওয়েবসাইটে পোস্ট করার জন্য ক্যাটাগরি সিলেক্ট করুন।"); return; }
        
        const checkedFbPages = Array.from(document.querySelectorAll('.fb-page-checkbox:checked')).map(cb => cb.value);
        
        const btn = document.getElementById('btnFinalPost'); const originalText = btn.innerHTML; btn.innerHTML = "⏳ Uploading..."; btn.disabled = true;
        canvas.discardActiveObject(); canvas.renderAll();
        try {
            const formData = new FormData(); formData.append('design_image', dataURLToBlob(canvas.toDataURL({ format: 'png', multiplier: 1.5, quality: 1.0 })), 'studio-final.png');
            if (isSocialOnly) formData.append('social_only', '1'); else if (categoryId) formData.append('category_id', categoryId);
            formData.append('social_caption', caption); 
            
            // 🔥 Multi-select Facebook pages logic
            if (document.getElementById('fbPageCheckboxList')) {
                if (checkedFbPages.length > 0) {
                    checkedFbPages.forEach(id => formData.append('selected_fb_page_ids[]', id));
                } else {
                    formData.append('skip_fb', '1'); // no pages checked -> explicitly tell backend to skip FB
                }
            }
            
            fetch("{{ route('news.publish-studio', $newsItem->id) }}", { method: "POST", headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, body: formData })
            .then(res => res.json()).then(data => { if (data.success) { alert("✅ পাবলিশিং শুরু হয়েছে!"); window.location.href = "{{ route('news.index') }}"; } else { alert("❌ " + data.message); btn.innerHTML = originalText; btn.disabled = false; } });
        } catch (error) { alert("❌ ক্যানভাস এরর।"); btn.innerHTML = originalText; btn.disabled = false; }
    }

    function refreshStudioCategories() {
        const btn = document.querySelector('button[onclick="refreshStudioCategories()"]'), select = document.getElementById('modalCategory');
        const originalText = btn.innerHTML; btn.innerHTML = "⏳ Loading..."; btn.disabled = true;
        fetch('/settings/fetch-categories').then(res => res.json()).then(data => {
            if (data.error) alert('❌ ' + data.error);
            else {
                select.innerHTML = '<option value="">-- Select Category --</option>';
                if (Array.isArray(data) && data.length > 0) { data.forEach(cat => select.innerHTML += `<option value="${cat.id}">${cat.name} (ID: ${cat.id})</option>`); select.innerHTML += `<option value="1">Uncategorized</option>`; alert("✅ আপডেট হয়েছে!"); } else alert("⚠️ ক্যাটাগরি নেই।");
            }
        }).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
    }

    // ==========================================
    // 🛠️ ৮. ইউটিলিটি ও হিস্ট্রি (Undo/Redo, UI)
    // ==========================================
    function openPublishModal() { document.getElementById('studioPublishModal').classList.remove('hidden'); document.getElementById('studioPublishModal').classList.add('flex'); }
    function closePublishModal() { document.getElementById('studioPublishModal').classList.add('hidden'); document.getElementById('studioPublishModal').classList.remove('flex'); }
    function toggleCategoryField(isChecked) { const w = document.getElementById('categoryFieldWrapper'); isChecked ? w.classList.add('opacity-50', 'pointer-events-none') : w.classList.remove('opacity-50', 'pointer-events-none'); }
    function updateUI(size, color, font) { if(document.getElementById('val-size')) document.getElementById('val-size').innerText = size; if(document.getElementById('text-size')) document.getElementById('text-size').value = size; if(document.getElementById('text-color')) document.getElementById('text-color').value = color; if(document.getElementById('font-family')) document.getElementById('font-family').value = font; }
    function switchTab(tabName) { document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active')); event.target.classList.add('active'); ['design', 'text', 'image', 'layers'].forEach(t => document.getElementById('tab-' + t).classList.add('hidden')); document.getElementById('tab-' + tabName).classList.remove('hidden'); }
    function updateActiveProp(prop, value) { const obj = canvas.getActiveObject(); if (obj) { obj.set(prop, value); if(prop === 'backgroundColor') document.getElementById('transparent-bg-check').checked = false; canvas.renderAll(); if(obj.isHeadline) { if(prop === 'fill') savePreference('color', value); if(prop === 'backgroundColor') savePreference('bg', value); if(prop === 'fontSize') savePreference('size', value); } saveHistory(); } if(prop==='fontSize') document.getElementById('val-size').innerText = value; }
    function toggleTransparentBg(checked) { const obj = canvas.getActiveObject(); if (obj) { const color = checked ? '' : (document.getElementById('text-bg').value || '#000'); obj.set('backgroundColor', color); canvas.renderAll(); if(obj.isHeadline) savePreference('bg', color); } }
    function toggleStyle(style) { const obj = canvas.getActiveObject(); if (!obj) return; if (style === 'bold') obj.set('fontWeight', obj.fontWeight === 'bold' ? 'normal' : 'bold'); if (style === 'italic') obj.set('fontStyle', obj.fontStyle === 'italic' ? 'normal' : 'italic'); if (style === 'underline') obj.set('underline', !obj.underline); canvas.renderAll(); }
    function addText(text, size = 50) { const t = new fabric.Textbox(text, { left: 100, top: 100, width: 400, fontSize: size, fill: '#fff', fontFamily: 'Hind Siliguri', fontWeight: 'bold', textAlign: 'center', backgroundColor: 'rgba(0,0,0,0.5)' }); canvas.add(t); canvas.setActiveObject(t); switchTab('text'); }
    function savePreference(key, value) { try { const prefs = JSON.parse(localStorage.getItem('studio_prefs')) || {}; prefs[key] = value; localStorage.setItem('studio_prefs', JSON.stringify(prefs)); } catch(e) {} }
    function downloadCard() { 
        canvas.discardActiveObject(); 
        canvas.renderAll(); 
        const link = document.createElement('a'); 
        link.download = `News_${Date.now()}.png`; 
        link.href = canvas.toDataURL({ format: 'png', multiplier: 1.5, quality: 1.0 }); 
        link.click(); 
        
        // Track the download
        fetch("{{ route('news.track-download', $newsItem->id) }}", { 
            method: "POST", 
            headers: { 
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                "Accept": "application/json"
            } 
        }).catch(err => console.error("Download tracking failed", err));
    }
    function resetCanvas() { if (confirm('রিসেট করতে চান?')) { localStorage.removeItem('studio_prefs'); localStorage.removeItem('custom_font_url'); location.reload(); } }
    function saveHistory() { if (isHistoryProcessing || !canvas) return; const json = JSON.stringify(canvas); if (historyStep >= 0 && history[historyStep] === json) return; historyStep++; history = history.slice(0, historyStep); history.push(json); }
    function undo() { if (historyStep > 0) { isHistoryProcessing = true; historyStep--; canvas.loadFromJSON(history[historyStep], function () { canvas.renderAll(); isHistoryProcessing = false; }); } }
    function redo() { if (historyStep < history.length - 1) { isHistoryProcessing = true; historyStep++; canvas.loadFromJSON(history[historyStep], function () { canvas.renderAll(); isHistoryProcessing = false; }); } }
    function initKeyboardEvents() { document.addEventListener('keydown', function(e) { if ((e.key === 'Delete' || e.key === 'Backspace') && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') { const obj = canvas.getActiveObject(); if (obj) canvas.remove(obj); } if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undo(); } if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); redo(); } }); }
    function updateSidebarValues() { const obj = canvas.getActiveObject(); if (!obj) return; if (obj.type === 'textbox' || obj.type === 'text') { switchTab('text'); if(document.getElementById('text-content')) document.getElementById('text-content').value = obj.text; if(document.getElementById('text-color')) document.getElementById('text-color').value = obj.fill; } }
    function uploadLogo(input) { if (input.files && input.files[0]) { const r = new FileReader(); r.onload = function (e) { addProfileLogo(e.target.result); }; r.readAsDataURL(input.files[0]); } }
    function addImageOnCanvas(input) { if (input.files && input.files[0]) { const r = new FileReader(); r.onload = function (e) { fabric.Image.fromURL(e.target.result, function(img) { img.scaleToWidth(300); canvas.add(img); canvas.centerObject(img); canvas.setActiveObject(img); }); }; r.readAsDataURL(input.files[0]); } }
    function deleteActive() { const obj = canvas.getActiveObject(); if (obj) canvas.remove(obj); }
    function addProfileLogo(url) { fabric.Image.fromURL(url, function(img) { img.scaleToWidth(150); img.set({ left: 880, top: 50 }); canvas.add(img); canvas.bringToFront(img); }, { crossOrigin: "anonymous" }); }
    function addDateText() { const oldDate = canvas.getObjects().find(o => o.isDate); if(oldDate) canvas.remove(oldDate); const date = new Date(); const months = ["জানুয়ারি", "ফেব্রুয়ারি", "মার্চ", "এপ্রিল", "মে", "জুন", "জুলাই", "আগস্ট", "সেপ্টেম্বর", "অক্টোবর", "নভেম্বর", "ডিসেম্বর"]; const convert = (num) => num.toString().split('').map(d => ['০','১','২','৩','৪','৫','৬','৭','৮','৯'][d]||d).join(''); const dateStr = `${convert(date.getDate())} ${months[date.getMonth()]}, ${convert(date.getFullYear())}`; const dateText = new fabric.Text(dateStr, { left: 50, top: 50, fontSize: 24, fill: '#fff', fontFamily: 'Hind Siliguri', backgroundColor: '#d90429', padding: 8, isDate: true }); canvas.add(dateText); canvas.bringToFront(dateText); }
    function setBackgroundImage(input) { if (input.files && input.files[0]) { const r = new FileReader(); r.onload = function (e) { fabric.Image.fromURL(e.target.result, function(img) { setupMainImage(img); saveHistory(); }); }; r.readAsDataURL(input.files[0]); } }
    function addCustomFrame(input) { if (input.files && input.files[0]) { const r = new FileReader(); r.onload = function (e) { applyAdminTemplate(e.target.result, 'bottom'); }; r.readAsDataURL(input.files[0]); } }
    function removeFrame() { if(frameObj) { canvas.remove(frameObj); frameObj = null; } userSettings.frameUrl = null; savePreference('frameUrl', null); saveHistory(); }
    function activateDebugTools() { const debugBox = document.createElement('div'); debugBox.id = 'pos-finder'; debugBox.style.cssText = "position:fixed; bottom:20px; left:20px; background:rgba(0,0,0,0.8); color:#00ff00; padding:15px; z-index:9999; font-family:monospace; font-size:14px; border-radius:8px; pointer-events:none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"; debugBox.innerHTML = "Select text to see pos"; document.body.appendChild(debugBox); function updatePositionDisplay() { const obj = canvas.getActiveObject(); if (!obj) { debugBox.innerHTML = "Select object"; return; } debugBox.innerHTML = `Top: ${Math.round(obj.top)}<br>Left: ${Math.round(obj.left)}<br>OriginX: ${obj.originX}`; } canvas.on('object:moving', updatePositionDisplay); canvas.on('selection:created', updatePositionDisplay); }

</script>