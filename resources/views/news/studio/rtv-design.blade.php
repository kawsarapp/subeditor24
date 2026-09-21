<script>
    // ==========================================
    // 🎨 RTV CODE-BASED DESIGN (SEPARATE FILE)
    // ==========================================
    
    window.drawRtvDesign = function() {
        console.log("🚀 Drawing RTV Design from Separate File...");

        // 1. Settings reset & cleanup
        if(window.userSettings) {
            window.userSettings.frameUrl = null; 
            window.userSettings.titlePos = null;
            window.savePreference('frameUrl', null); // Preserve save
        }
        
        // Cleanup (delete all except image, title, date)
        const objects = canvas.getObjects();
        let mainImgObj = objects.find(obj => obj.isMainImage);
        let titleObj = objects.find(obj => obj.isHeadline);
        let dateObj = objects.find(obj => obj.isDate);

        for (let i = objects.length - 1; i >= 0; i--) {
            let obj = objects[i];
            if (obj.isMainImage || obj.isHeadline || obj.isDate) continue; 
            canvas.remove(obj);
        }

        // 2. Background (Deep Blue Gradient)
        const bgRect = new fabric.Rect({
            left: 0, top: 0, width: canvas.width, height: canvas.height,
            selectable: false, evented: false, isFrame: true
        });
        
        const bgGradient = new fabric.Gradient({
            type: 'linear',
            coords: { x1: 0, y1: 0, x2: 0, y2: canvas.height },
            colorStops: [
                { offset: 0, color: '#0a1a45' }, // Dark blue
                { offset: 1, color: '#003399' }  // Light blue
            ]
        });
        bgRect.set('fill', bgGradient);
        canvas.add(bgRect);
        canvas.sendToBack(bgRect);

        // 3. Header Shape (Red)
        const headerPath = new fabric.Path('M 0 0 L 1080 0 L 1080 100 L 600 120 L 480 120 L 0 100 z', {
            selectable: false, evented: false
        });
        const headerGradient = new fabric.Gradient({
            type: 'linear',
            coords: { x1: 0, y1: 0, x2: 1080, y2: 0 },
            colorStops: [
                { offset: 0, color: '#8a0000' }, 
                { offset: 0.5, color: '#d90429' },
                { offset: 1, color: '#8a0000' }
            ]
        });
        headerPath.set('fill', headerGradient);
        canvas.add(headerPath);

        // 4. Main Image Frame (Yellow Border)
        const frameBox = new fabric.Rect({
            left: 40, top: 150, width: 1000, height: 600,
            rx: 20, ry: 20, 
            fill: 'rgba(255,255,255,0.1)', 
            stroke: '#fcdb00', // Yellow border
            strokeWidth: 5,
            selectable: false, evented: false
        });
        canvas.add(frameBox);

        // 5. White Footer
        const footerStrip = new fabric.Rect({
            left: 0, top: 930, width: 1080, height: 150,
            fill: '#ffffff',
            selectable: false, evented: false
        });
        canvas.add(footerStrip);

        // 6. Footer Text
        const appText = new fabric.Text("Download News App Now!", {
            left: 540, top: 960, fontSize: 32,
            fontFamily: 'Hind Siliguri', fill: '#003399', fontWeight: 'bold',
            originX: 'center', selectable: false
        });
        canvas.add(appText);
        
        // Play Store Icon (Simulated)
        const playBox = new fabric.Rect({ left: 400, top: 1020, width: 120, height: 40, fill: '#333', rx: 5, ry: 5, selectable: false });
        const appBox = new fabric.Rect({ left: 560, top: 1020, width: 120, height: 40, fill: '#333', rx: 5, ry: 5, selectable: false });
        // canvas.add(playBox); // Replace with icon image if available
        // canvas.add(appBox);

        // 7. Logo link
        // Use fabric.Image.fromURL('YOUR_LOGO_URL_HERE', ...)
        
        // Details button
        const detailsBtn = new fabric.Rect({
            left: 390, top: 850, width: 300, height: 60, rx: 30, ry: 30,
            fill: '#000000', selectable: false
        });
        const detailsText = new fabric.Text("Details in Comment", {
            left: 540, top: 862, fontSize: 28, fill: '#fcdb00', fontWeight: 'bold',
            fontFamily: 'Hind Siliguri', originX: 'center', selectable: false
        });
        canvas.add(detailsBtn);
        canvas.add(detailsText);

        // 8. Fixed text positions
        if(titleObj) {
            titleObj.set({
                top: 500,        
                left: 540,
                width: 900,
                textAlign: 'center',
                originX: 'center',
                fill: '#ffffff',
                stroke: '#000000',
                strokeWidth: 0,
                shadow: new fabric.Shadow({ color: 'rgba(0,0,0,0.8)', blur: 5, offsetX: 2, offsetY: 2 })
            });
            titleObj.bringToFront();
        }
        
        if(dateObj) {
            dateObj.set({ top: 80, left: 950, originX: 'right', fill: '#ffffff' });
            dateObj.bringToFront();
        }

        // 9. Main image position
        if(mainImgObj) {
            mainImgObj.set({ left: 50, top: 160 });
            mainImgObj.scaleToWidth(980); 
            // Simple scaling for image placement
            canvas.sendToBack(mainImgObj);
            canvas.sendToBack(bgRect); 
        }

        canvas.requestRenderAll();
        if(typeof window.saveHistory === 'function') window.saveHistory();
    };
</script>