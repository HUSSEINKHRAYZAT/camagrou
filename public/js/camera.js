// Camera + creation workspace logic with live filters/overlays
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const previewCanvas = document.getElementById('previewCanvas');
const startCameraBtn = document.getElementById('startCamera');
const captureBtn = document.getElementById('capture');
const uploadBtn = document.getElementById('upload');
const fileInput = document.getElementById('fileInput');
const saveBtn = document.getElementById('saveBtn');
const imageDataInput = document.getElementById('imageData');
const filterButtons = document.querySelectorAll('.filter-card');
const emojiButtons = document.querySelectorAll('.emoji-chip');
const emojiSizeInput = document.getElementById('emojiSize');
const emojiSizeBubble = document.getElementById('emojiSizeBubble');
const clearEmojisBtn = document.getElementById('clearEmojis');
const removeEmojiBtn = document.getElementById('removeEmoji');
const removeStickerBtn = document.getElementById('removeSticker');
const viewportPlaceholder = document.getElementById('viewportPlaceholder');
const tabButtons = document.querySelectorAll('.tab-btn');
const tabPanels = document.querySelectorAll('.tab-panel');
const categoryChips = document.querySelectorAll('.category-chip');
const pickerSearch = document.getElementById('pickerSearch');
const uploadZone = document.getElementById('uploadZone');
const stickerItems = document.querySelectorAll('#stickerList .picker-card');
const emojiItems = document.querySelectorAll('#emojiList .emoji-chip');
const cameraStatus = document.getElementById('cameraStatus');

let stream = null;
let capturedImage = null;
let baseImageEl = null;
let currentFilter = 'none';
let selectedEmoji = null;
let selectedSticker = null;
let cameraActive = false;
let previewMode = false;
let liveRenderId = null;

const emojiOverlays = [];
const stickerOverlays = [];
const stickerCache = new Map();
const activeCategories = { stickers: 'all', emojis: 'all' };
let activeTab = 'stickers';
let resizingEmoji = null;
const MIN_EMOJI_SIZE = 24;
const MAX_EMOJI_SIZE = 200;
const STICKER_DEFAULT_SIZE = 150;

const filterMap = {
    none: 'none',
    grayscale: 'grayscale(1)',
    sepia: 'sepia(0.85) saturate(1.1)',
    warm: 'contrast(1.05) saturate(1.25) hue-rotate(-10deg)',
    cool: 'contrast(0.95) saturate(1.1) hue-rotate(15deg)',
    bright: 'brightness(1.12) saturate(1.1)',
    noir: 'grayscale(1) contrast(1.2) brightness(0.92)'
};

const setCameraStatus = text => cameraStatus && (cameraStatus.textContent = text);

const clearEmojiSelection = () => {
    emojiButtons.forEach(btn => btn.classList.remove('active'));
    selectedEmoji = null;
};

const clearAllSelections = () => {
    clearEmojiSelection();
    clearStickerSelection();
};

const resetEmojiOverlays = () => {
    emojiOverlays.length = 0;
    clearEmojiSelection();
};

const resetStickerOverlays = () => {
    stickerOverlays.length = 0;
    selectedSticker = null;
    stickerItems.forEach(s => s.classList.remove('active'));
};

const clearStickerSelection = () => {
    selectedSticker = null;
    stickerItems.forEach(s => s.classList.remove('active'));
};

const updateViewportState = () => {
    const hasImage = capturedImage || cameraActive;
    if (previewCanvas) previewCanvas.classList.toggle('active', !!hasImage);
    if (video) video.classList.toggle('hidden', !!hasImage);
    if (viewportPlaceholder) viewportPlaceholder.classList.toggle('hidden', !!hasImage || cameraActive);
};

const updateSizeBubble = () => {
    if (!emojiSizeInput || !emojiSizeBubble) return;
    const value = emojiSizeInput.value;
    const min = parseInt(emojiSizeInput.min || '0', 10);
    const max = parseInt(emojiSizeInput.max || '100', 10);
    const percent = ((value - min) / (max - min)) * 100;
    emojiSizeBubble.textContent = `${value}px`;
    emojiSizeBubble.style.left = `${Math.min(Math.max(percent, 0), 100)}%`;
};

const applyPickerFilters = () => {
    const query = ((pickerSearch && pickerSearch.value) || '').trim().toLowerCase();
    if (activeTab === 'stickers') {
        stickerItems.forEach(item => {
            const matchesCategory = activeCategories.stickers === 'all' || item.dataset.category === activeCategories.stickers;
            const label = (item.dataset.search || '').toLowerCase();
            const matchesSearch = !query || label.includes(query);
            item.style.display = matchesCategory && matchesSearch ? '' : 'none';
        });
    } else if (activeTab === 'emojis') {
        emojiItems.forEach(item => {
            const matchesCategory = activeCategories.emojis === 'all' || item.dataset.category === activeCategories.emojis;
            const label = (item.dataset.search || '').toLowerCase();
            const matchesSearch = !query || label.includes(query);
            item.style.display = matchesCategory && matchesSearch ? '' : 'none';
        });
    } else {
        filterButtons.forEach(button => {
            const label = (button.dataset.search || '').toLowerCase();
            button.style.display = !query || label.includes(query) ? '' : 'none';
        });
    }
};

const toggleUploadZone = isActive => uploadZone && uploadZone.classList[isActive ? 'add' : 'remove']('dragging');

const stopLiveRender = () => {
    if (liveRenderId) {
        cancelAnimationFrame(liveRenderId);
        liveRenderId = null;
    }
};

const drawStickers = ctx => {
    if (!stickerOverlays.length) return;
    stickerOverlays.forEach(overlay => {
        let img = stickerCache.get(overlay.src);
        if (!img) {
            img = new Image();
            img.src = 'public/stickers/' + overlay.src;
            img.crossOrigin = 'anonymous';
            stickerCache.set(overlay.src, img);
            img.onload = () => {
                if (cameraActive && !capturedImage && !previewMode) {
                    startLiveRender();
                } else {
                    drawPreview();
                }
            };
            return;
        }
        if (!img.complete) return;
        const size = overlay.size || STICKER_DEFAULT_SIZE;
        const rotation = overlay.rotation || 0;
        
        ctx.save();
        ctx.translate(overlay.x, overlay.y);
        ctx.rotate((rotation * Math.PI) / 180);
        
        // For PNG with "Untitled Project" filename, remove black background
        const needsBackgroundRemoval = overlay.src.toLowerCase().includes('untitled project');
        
        if (needsBackgroundRemoval) {
            // Create a temporary canvas to process the image
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = img.width;
            tempCanvas.height = img.height;
            const tempCtx = tempCanvas.getContext('2d');
            
            // Draw the image
            tempCtx.drawImage(img, 0, 0);
            
            // Get image data and remove black pixels
            const imageData = tempCtx.getImageData(0, 0, img.width, img.height);
            const data = imageData.data;
            const threshold = 50; // Adjust to remove near-black colors
            
            for (let i = 0; i < data.length; i += 4) {
                const r = data[i];
                const g = data[i + 1];
                const b = data[i + 2];
                
                // If pixel is close to black, make it transparent
                if (r <= threshold && g <= threshold && b <= threshold) {
                    data[i + 3] = 0; // Set alpha to 0 (transparent)
                }
            }
            
            tempCtx.putImageData(imageData, 0, 0);
            ctx.drawImage(tempCanvas, -size / 2, -size / 2, size, size);
        } else {
            // For SVG and other images, draw normally
            ctx.drawImage(img, -size / 2, -size / 2, size, size);
        }
        
        ctx.restore();
    });
};

const drawEmojis = ctx => {
    if (!emojiOverlays.length) return;
    ctx.save();
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    emojiOverlays.forEach(item => {
        ctx.font = `${item.size || 64}px 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Emoji', sans-serif`;
        ctx.shadowColor = 'rgba(0,0,0,0.25)';
        ctx.shadowBlur = 15;
        ctx.fillText(item.emoji, item.x, item.y);
    });
    ctx.restore();
};

const renderLiveFrame = () => {
    if (!cameraActive || previewMode || !previewCanvas || !video) return;
    const ctx = previewCanvas.getContext('2d');
    ctx.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
    ctx.save();
    ctx.filter = filterMap[currentFilter] || 'none';
    ctx.drawImage(video, 0, 0, previewCanvas.width, previewCanvas.height);
    ctx.restore();
    drawStickers(ctx);
    drawEmojis(ctx);
    liveRenderId = requestAnimationFrame(renderLiveFrame);
};

const startLiveRender = () => {
    stopLiveRender();
    liveRenderId = requestAnimationFrame(renderLiveFrame);
};

const enterPreviewMode = () => {
    previewMode = true;
    if (captureBtn) captureBtn.textContent = 'Retake';
    stopLiveRender();
    updateViewportState();
    setCameraStatus('Preview ready');
};

const exitPreviewMode = () => {
    previewMode = false;
    if (captureBtn) captureBtn.textContent = 'Capture Photo';
    updateViewportState();
    setCameraStatus(cameraActive ? 'Camera live' : 'Camera idle');
    if (cameraActive) startLiveRender();
};

const clearCapturedImage = () => {
    capturedImage = null;
    baseImageEl = null;
    if (imageDataInput) imageDataInput.value = '';
    resetEmojiOverlays();
    resetStickerOverlays();
    if (saveBtn) saveBtn.disabled = true;
    if (!cameraActive && captureBtn) captureBtn.disabled = true;
    if (previewCanvas) {
        const ctx = previewCanvas.getContext('2d');
        ctx.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
    }
    exitPreviewMode();
};

const handleImageFile = file => {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = event => {
        const img = new Image();
        img.onload = function() {
            const context = canvas.getContext('2d');
            context.drawImage(img, 0, 0, 640, 480);
            capturedImage = canvas.toDataURL('image/png');
            baseImageEl = img;
            resetEmojiOverlays();
            resetStickerOverlays();
            drawPreview();
            saveBtn.disabled = false;
            captureBtn.disabled = false;
            enterPreviewMode();
            setCameraStatus('Image ready');
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
};

const startCamera = async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
        if (!video) return;
        video.srcObject = stream;
        captureBtn.disabled = false;
        cameraActive = true;
        startCameraBtn.textContent = 'Stop Camera';
        updateViewportState();
        startLiveRender();
        setCameraStatus('Camera live');
    } catch (err) {
        alert('Error accessing camera: ' + err.message);
    }
};

const stopCamera = () => {
    stopLiveRender();
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    captureBtn.disabled = true;
    startCameraBtn.textContent = 'Start Camera';
    cameraActive = false;
    updateViewportState();
    setCameraStatus('Camera idle');
};

// Initial UI state
updateViewportState();
setCameraStatus('Camera idle');

// Tabs
tabButtons.forEach(button => {
    button.addEventListener('click', () => {
        if (button.classList.contains('active')) return;
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabPanels.forEach(panel => panel.classList.remove('active'));
        button.classList.add('active');
        const tabName = button.getAttribute('data-tab');
        activeTab = tabName;
        const targetPanel = document.querySelector(`.tab-panel[data-panel="${tabName}"]`);
        if (targetPanel) targetPanel.classList.add('active');
        if (tabName === 'emojis') {
            clearStickerSelection();
        } else if (tabName === 'stickers') {
            clearEmojiSelection();
        }
        applyPickerFilters();
    });
});

// Category chips
categoryChips.forEach(chip => {
    chip.addEventListener('click', () => {
        const parent = chip.closest('.picker-categories');
        const picker = parent ? parent.dataset.picker : null;
        if (!picker) return;
        parent.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        if (picker === 'sticker') activeCategories.stickers = chip.dataset.category || 'all';
        if (picker === 'emoji') activeCategories.emojis = chip.dataset.category || 'all';
        applyPickerFilters();
    });
});

if (pickerSearch) pickerSearch.addEventListener('input', applyPickerFilters);
applyPickerFilters();

// Sticker selection
stickerItems.forEach(sticker => {
    sticker.addEventListener('click', function() {
        stickerItems.forEach(s => s.classList.remove('active'));
        this.classList.add('active');
        selectedSticker = this.getAttribute('data-sticker');
        clearEmojiSelection();
        if (capturedImage) drawPreview();
    });
});

// Start/stop camera
startCameraBtn?.addEventListener('click', () => {
    if (cameraActive) {
        stopCamera();
    } else {
        startCamera();
    }
});

// Capture photo
captureBtn?.addEventListener('click', () => {
    if (previewMode) {
        clearCapturedImage();
        return;
    }
    if (!video) return;
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, 640, 480);
    capturedImage = canvas.toDataURL('image/png');
    baseImageEl = null;
    resetEmojiOverlays();
    resetStickerOverlays();
    drawPreview();
    saveBtn.disabled = false;
    enterPreviewMode();
});

// Upload photo
uploadBtn?.addEventListener('click', () => fileInput && fileInput.click());
fileInput?.addEventListener('change', e => {
    handleImageFile(e.target.files[0]);
    e.target.value = '';
});

// Drag/drop upload
if (uploadZone) {
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadZone.addEventListener(eventName, event => {
            event.preventDefault();
            toggleUploadZone(true);
        });
    });
    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, event => {
            event.preventDefault();
            if (eventName === 'drop') {
                const files = event.dataTransfer && event.dataTransfer.files;
                const file = files && files[0];
                handleImageFile(file);
            }
            toggleUploadZone(false);
        });
    });
}

// Filter selection
filterButtons.forEach(button => {
    button.addEventListener('click', () => {
        filterButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        currentFilter = button.getAttribute('data-filter');
        if (cameraActive && !previewMode) {
            startLiveRender();
        } else {
            drawPreview();
        }
    });
});

// Emoji selection
emojiButtons.forEach(button => {
    button.addEventListener('click', () => {
        if (button.classList.contains('active')) {
            button.classList.remove('active');
            selectedEmoji = null;
            return;
        }
        emojiButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        selectedEmoji = button.getAttribute('data-emoji');
        clearStickerSelection();
    });
});

emojiSizeInput?.addEventListener('input', updateSizeBubble);
updateSizeBubble();

clearEmojisBtn?.addEventListener('click', () => {
    clearCapturedImage();
});

removeEmojiBtn?.addEventListener('click', () => {
    emojiOverlays.pop();
    renderCurrent();
});

removeStickerBtn?.addEventListener('click', () => {
    stickerOverlays.pop();
    renderCurrent();
});

const renderCurrent = () => {
    if (cameraActive && !previewMode) {
        startLiveRender();
        return;
    }
    drawPreview();
};

const hitTestEmoji = (x, y) => {
    for (let i = emojiOverlays.length - 1; i >= 0; i--) {
        const item = emojiOverlays[i];
        const size = item.size || 64;
        const radius = size / 2;
        const dx = x - item.x;
        const dy = y - item.y;
        if (dx * dx + dy * dy <= radius * radius) {
            return i;
        }
    }
    return -1;
};

const hitTestSticker = (x, y) => {
    for (let i = stickerOverlays.length - 1; i >= 0; i--) {
        const item = stickerOverlays[i];
        const size = item.size || STICKER_DEFAULT_SIZE;
        const half = size / 2;
        if (x >= item.x - half && x <= item.x + half && y >= item.y - half && y <= item.y + half) {
            return i;
        }
    }
    return -1;
};

// Overlay placement and emoji resizing (live or preview)
previewCanvas?.addEventListener('mousedown', event => {
    if (!cameraActive && !capturedImage) return;
    const rect = previewCanvas.getBoundingClientRect();
    const scaleX = previewCanvas.width / rect.width;
    const scaleY = previewCanvas.height / rect.height;
    const x = (event.clientX - rect.left) * scaleX;
    const y = (event.clientY - rect.top) * scaleY;

    // Drag stickers first (move, resize with Shift, rotate with Ctrl)
    const stickerIndex = hitTestSticker(x, y);
    if (stickerIndex !== -1) {
        const target = stickerOverlays[stickerIndex];
        const startX = x;
        const startY = y;
        const startSize = target.size || STICKER_DEFAULT_SIZE;
        const startRotation = target.rotation || 0;
        const isResize = event.shiftKey;
        const isRotate = event.ctrlKey;
        
        const moveHandler = moveEvent => {
            const mx = (moveEvent.clientX - rect.left) * scaleX;
            const my = (moveEvent.clientY - rect.top) * scaleY;
            
            if (isResize) {
                // Resize: vertical drag to change size
                const delta = startY - my;
                const newSize = Math.max(50, Math.min(300, startSize + delta));
                stickerOverlays[stickerIndex].size = newSize;
            } else if (isRotate) {
                // Rotate: horizontal drag to change rotation
                const delta = mx - startX;
                const newRotation = (startRotation + delta) % 360;
                stickerOverlays[stickerIndex].rotation = newRotation;
            } else {
                // Move
                stickerOverlays[stickerIndex].x = mx;
                stickerOverlays[stickerIndex].y = my;
            }
            renderCurrent();
        };
        const upHandler = () => {
            window.removeEventListener('mousemove', moveHandler);
            window.removeEventListener('mouseup', upHandler);
        };
        window.addEventListener('mousemove', moveHandler);
        window.addEventListener('mouseup', upHandler);
        return;
    }

    // Drag/move existing emoji; hold Shift to resize while dragging
    const hitIndex = hitTestEmoji(x, y);
    if (hitIndex !== -1) {
        const target = emojiOverlays[hitIndex];
        const start = { x, y, size: target.size || 64 };
        const isResize = event.shiftKey;
        const moveHandler = moveEvent => {
            const mx = (moveEvent.clientX - rect.left) * scaleX;
            const my = (moveEvent.clientY - rect.top) * scaleY;
            if (isResize) {
                const delta = start.y - my;
                const newSize = Math.min(Math.max(start.size + delta, MIN_EMOJI_SIZE), MAX_EMOJI_SIZE);
                emojiOverlays[hitIndex].size = newSize;
                if (emojiSizeInput) {
                    emojiSizeInput.value = newSize;
                    updateSizeBubble();
                }
            } else {
                emojiOverlays[hitIndex].x = mx;
                emojiOverlays[hitIndex].y = my;
            }
            renderCurrent();
        };
        const upHandler = () => {
            window.removeEventListener('mousemove', moveHandler);
            window.removeEventListener('mouseup', upHandler);
        };
        window.addEventListener('mousemove', moveHandler);
        window.addEventListener('mouseup', upHandler);
        return;
    }

    // Place new overlay (only one type at a time based on active selection)
    if (!selectedEmoji && !selectedSticker) return;
    if (selectedEmoji) {
        emojiOverlays.push({
            emoji: selectedEmoji,
            x,
            y,
            size: parseInt(emojiSizeInput ? emojiSizeInput.value : 64, 10)
        });
    } else if (selectedSticker) {
        stickerOverlays.push({
            src: selectedSticker,
            x,
            y,
            size: STICKER_DEFAULT_SIZE,
            rotation: 0
        });
    }
    renderCurrent();
});

// Draw preview with current captured image + overlays
function drawPreview() {
    if (!capturedImage || !previewCanvas) return Promise.resolve();
    const context = previewCanvas.getContext('2d');
    const renderImage = baseImageEl
        ? Promise.resolve(baseImageEl)
        : new Promise(resolve => {
            const img = new Image();
            img.onload = () => {
                baseImageEl = img;
                resolve(img);
            };
            img.src = capturedImage;
        });

    return renderImage.then(img => new Promise(resolve => {
        context.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
        context.save();
        context.filter = filterMap[currentFilter] || 'none';
        context.drawImage(img, 0, 0, previewCanvas.width, previewCanvas.height);
        context.restore();

        const finalize = () => {
            drawStickers(context);
            drawEmojis(context);
            if (imageDataInput) imageDataInput.value = previewCanvas.toDataURL('image/png');
            updateViewportState();
            resolve();
        };

        finalize();
    }));
}

// Save form
const saveForm = document.getElementById('saveForm');
if (saveForm) {
    saveForm.addEventListener('submit', async function(e) {
        if (!capturedImage) {
            e.preventDefault();
            alert('Please capture or upload an image first!');
            return false;
        }
        e.preventDefault();
        await drawPreview();
        saveForm.submit();
    });
}
