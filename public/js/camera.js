// Camera functionality
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
let selectedSticker = null;
let capturedImage = null;
let currentFilter = 'none';
let selectedEmoji = null;
const emojiOverlays = [];
let cameraActive = false;
let previewMode = false;
let activeTab = 'stickers';
const activeCategories = {
    stickers: 'all',
    emojis: 'all'
};

const filterMap = {
    none: 'none',
    grayscale: 'grayscale(1)',
    sepia: 'sepia(0.85) saturate(1.1)',
    warm: 'contrast(1.05) saturate(1.25) hue-rotate(-10deg)',
    cool: 'contrast(0.95) saturate(1.1) hue-rotate(15deg)',
    bright: 'brightness(1.12) saturate(1.1)',
    noir: 'grayscale(1) contrast(1.2) brightness(0.92)'
};

const clearEmojiSelection = () => {
    if (emojiButtons.length) {
        emojiButtons.forEach(btn => btn.classList.remove('active'));
    }
    selectedEmoji = null;
};

const resetEmojiOverlays = () => {
    emojiOverlays.length = 0;
    clearEmojiSelection();
};

const setCameraStatus = text => {
    if (cameraStatus) {
        cameraStatus.textContent = text;
    }
};

const updateSizeBubble = () => {
    if (!emojiSizeInput || !emojiSizeBubble) return;
    const value = emojiSizeInput.value;
    const min = parseInt(emojiSizeInput.min || '0', 10);
    const max = parseInt(emojiSizeInput.max || '100', 10);
    const percent = ((value - min) / (max - min)) * 100;
    const clamped = Math.min(Math.max(percent, 0), 100);
    emojiSizeBubble.textContent = `${value}px`;
    emojiSizeBubble.style.left = `${clamped}%`;
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

const handleImageFile = file => {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(event) {
        const img = new Image();
        img.onload = function() {
            const context = canvas.getContext('2d');
            context.drawImage(img, 0, 0, 640, 480);
            capturedImage = canvas.toDataURL('image/png');
            resetEmojiOverlays();
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

const toggleUploadZone = isActive => {
    if (uploadZone) {
        uploadZone.classList[isActive ? 'add' : 'remove']('dragging');
    }
};

const updateViewportState = () => {
    if (capturedImage) {
        if (previewCanvas) previewCanvas.classList.add('active');
        if (video) video.classList.add('hidden');
        if (viewportPlaceholder) viewportPlaceholder.classList.add('hidden');
    } else if (cameraActive) {
        if (previewCanvas) previewCanvas.classList.remove('active');
        if (video) video.classList.remove('hidden');
        if (viewportPlaceholder) viewportPlaceholder.classList.add('hidden');
    } else {
        if (previewCanvas) previewCanvas.classList.remove('active');
        if (video) video.classList.add('hidden');
        if (viewportPlaceholder) viewportPlaceholder.classList.remove('hidden');
    }
};

const enterPreviewMode = () => {
    previewMode = true;
    if (captureBtn) {
        captureBtn.textContent = 'Retake';
    }
    updateViewportState();
    setCameraStatus('Preview ready');
};

const exitPreviewMode = () => {
    previewMode = false;
    if (captureBtn) {
        captureBtn.textContent = 'Capture Photo';
    }
    updateViewportState();
    setCameraStatus(cameraActive ? 'Camera live' : 'Camera idle');
};

const clearCapturedImage = () => {
    capturedImage = null;
    if (imageDataInput) {
        imageDataInput.value = '';
    }
    resetEmojiOverlays();
    if (saveBtn) {
        saveBtn.disabled = true;
    }
    if (!cameraActive && captureBtn) {
        captureBtn.disabled = true;
    }
    exitPreviewMode();
};

updateViewportState();
setCameraStatus('Camera idle');

if (tabButtons.length) {
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('active')) return;
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanels.forEach(panel => panel.classList.remove('active'));
            button.classList.add('active');
            const tabName = button.getAttribute('data-tab');
            activeTab = tabName;
            const targetPanel = document.querySelector(`.tab-panel[data-panel="${tabName}"]`);
            if (targetPanel) {
                targetPanel.classList.add('active');
            }
            applyPickerFilters();
        });
    });
}

if (categoryChips.length) {
    categoryChips.forEach(chip => {
        chip.addEventListener('click', () => {
            const parent = chip.closest('.picker-categories');
            const picker = parent ? parent.dataset.picker : null;
            if (!picker) return;
            parent.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            if (picker === 'sticker') {
                activeCategories.stickers = chip.dataset.category || 'all';
            } else if (picker === 'emoji') {
                activeCategories.emojis = chip.dataset.category || 'all';
            }
            applyPickerFilters();
        });
    });
}

if (pickerSearch) {
    pickerSearch.addEventListener('input', () => applyPickerFilters());
}
applyPickerFilters();

// Sticker selection
if (stickerItems.length) {
    stickerItems.forEach(sticker => {
        sticker.addEventListener('click', function() {
            stickerItems.forEach(s => s.classList.remove('active'));
            this.classList.add('active');
            selectedSticker = this.getAttribute('data-sticker');
            
            if (capturedImage) {
                drawPreview();
            }
        });
    });
}

// Start/stop camera toggle
startCameraBtn.addEventListener('click', async function() {
    if (cameraActive) {
        stopCamera();
        return;
    }

    try {
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { width: 640, height: 480 } 
        });
        video.srcObject = stream;
        captureBtn.disabled = false;
        cameraActive = true;
        startCameraBtn.textContent = 'Stop Camera';
        updateViewportState();
        setCameraStatus('Camera live');
    } catch (err) {
        alert('Error accessing camera: ' + err.message);
    }
});

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    captureBtn.disabled = true;
    startCameraBtn.textContent = 'Start Camera';
    cameraActive = false;
    updateViewportState();
    setCameraStatus('Camera idle');
}

// Capture photo
captureBtn.addEventListener('click', function() {
    if (previewMode) {
        clearCapturedImage();
        return;
    }

    let context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, 640, 480);
    capturedImage = canvas.toDataURL('image/png');
    resetEmojiOverlays();
    drawPreview();
    saveBtn.disabled = false;
    enterPreviewMode();
});

// Upload photo
uploadBtn.addEventListener('click', function() {
    if (fileInput) {
        fileInput.click();
    }
});

if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        handleImageFile(e.target.files[0]);
        e.target.value = '';
    });
}

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
if (filterButtons.length) {
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            currentFilter = button.getAttribute('data-filter');
            drawPreview();
        });
    });
}

// Emoji selection & placement
if (emojiButtons.length) {
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
        });
    });
}

if (emojiSizeInput) {
    emojiSizeInput.addEventListener('input', updateSizeBubble);
    updateSizeBubble();
}

if (clearEmojisBtn) {
    clearEmojisBtn.addEventListener('click', () => {
        resetEmojiOverlays();
        drawPreview();
    });
}

if (previewCanvas) {
    previewCanvas.addEventListener('click', event => {
        if (!selectedEmoji || !capturedImage) return;
        const rect = previewCanvas.getBoundingClientRect();
        const scaleX = previewCanvas.width / rect.width;
        const scaleY = previewCanvas.height / rect.height;
        const x = (event.clientX - rect.left) * scaleX;
        const y = (event.clientY - rect.top) * scaleY;
        emojiOverlays.push({
            emoji: selectedEmoji,
            x,
            y,
            size: parseInt(emojiSizeInput ? emojiSizeInput.value : 64, 10)
        });
        drawPreview();
    });
}

// Draw preview with edits
function drawPreview() {
    if (!capturedImage) return;

    const context = previewCanvas.getContext('2d');
    const img = new Image();

    img.onload = function() {
        context.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
        context.save();
        context.filter = filterMap[currentFilter] || 'none';
        context.drawImage(img, 0, 0, previewCanvas.width, previewCanvas.height);
        context.restore();

    const finalize = () => {
        drawEmojis(context);
        if (imageDataInput) {
            imageDataInput.value = previewCanvas.toDataURL('image/png');
        }
        updateViewportState();
    };

        if (selectedSticker) {
            const stickerImg = new Image();
            stickerImg.onload = function() {
                const stickerWidth = 150;
                const stickerHeight = 150;
                const x = (previewCanvas.width - stickerWidth) / 2;
                const y = (previewCanvas.height - stickerHeight) / 2;
                context.drawImage(stickerImg, x, y, stickerWidth, stickerHeight);
                finalize();
            };
            stickerImg.src = 'public/stickers/' + selectedSticker;
        } else {
            finalize();
        }
    };

    img.src = capturedImage;
}

function drawEmojis(context) {
    if (!emojiOverlays.length) return;

    context.save();
    context.textAlign = 'center';
    context.textBaseline = 'middle';
    emojiOverlays.forEach(item => {
        context.font = `${item.size || 64}px 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Emoji', sans-serif`;
        context.shadowColor = 'rgba(0,0,0,0.25)';
        context.shadowBlur = 15;
        context.fillText(item.emoji, item.x, item.y);
    });
    context.restore();
}

// Save form
const saveForm = document.getElementById('saveForm');
if (saveForm) {
    saveForm.addEventListener('submit', function(e) {
        if (!capturedImage) {
            e.preventDefault();
            alert('Please capture or upload an image first!');
            return false;
        }
    });
}
