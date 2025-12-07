(() => {
    const init = () => {
    // DOM Elements - Profile editing
    const modal = document.getElementById('profileEditModal');
    const openBtn = document.getElementById('openEditProfile');
    const closeBtn = document.getElementById('closeEditProfile');
    const backdrop = document.getElementById('profileEditBackdrop');
    const bioInput = document.getElementById('bioInput');
    const bioDisplay = document.getElementById('profileBio');
    const avatarPreviewImg = document.getElementById('avatarPreviewImg');
    const avatarUpload = document.getElementById('avatarUpload');
    const avatarGrid = document.getElementById('avatarGrid');
    const avatarImg = document.getElementById('profileAvatarImg');
    const saveBtn = document.getElementById('saveProfileUi');
    const storyAddBtn = document.getElementById('addStoryBtn');
    const storyUpload = document.getElementById('storyUpload');
    const storyForm = document.getElementById('storyForm');
    
    // DOM Elements - Story viewer
    const storyItems = document.querySelectorAll('.story-item');
    const storyViewer = document.getElementById('storyViewer');
    const storyViewerImg = document.getElementById('storyViewerImg');
    const storyProgressBars = document.getElementById('storyProgressBars');
    const closeStoryViewer = document.getElementById('closeStoryViewer');
    const prevStory = document.getElementById('prevStory');
    const nextStory = document.getElementById('nextStory');
    const tapPrev = document.getElementById('tapPrev');
    const tapNext = document.getElementById('tapNext');
    const storyCounter = document.getElementById('storyCounter');
    const storyTime = document.getElementById('storyTime');
    
    // Constants
    const STORY_DURATION = 7000;

    // State variables
    let selectedAvatarSrc = (avatarImg && avatarImg.getAttribute('src')) || '';
    let selectedStorySrc = null;
    let currentStoryIndex = 0;
    let storiesData = [];
    let storiesTimestamps = [];
    let storyTimer = null;
    let progressIntervals = [];

    // Helper function to format time ago (Instagram-style)
    const formatTimeAgo = (timestamp) => {
        const now = new Date();
        const storyTime = new Date(timestamp);
        const diffMs = now - storyTime;
        const diffSecs = Math.floor(diffMs / 1000);
        const diffMins = Math.floor(diffSecs / 60);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);

        if (diffSecs < 60) return 'now';
        if (diffMins < 60) return `${diffMins}m`;
        if (diffHours < 24) return `${diffHours}h`;
        return `${diffDays}d`;
    };

    // Helper function to create progress bars
    const createProgressBars = (count) => {
        if (!storyProgressBars) return;
        
        storyProgressBars.innerHTML = '';
        for (let i = 0; i < count; i++) {
            const progressContainer = document.createElement('div');
            progressContainer.className = 'story-viewer__progress-bar';
            
            const progressFill = document.createElement('div');
            progressFill.className = 'story-viewer__progress-fill';
            progressFill.id = `storyProgress${i}`;
            
            progressContainer.appendChild(progressFill);
            storyProgressBars.appendChild(progressContainer);
        }
    };

    // Helper function to update progress bars
    const updateProgressBars = (currentIndex) => {
        for (let i = 0; i < storiesData.length; i++) {
            const progressFill = document.getElementById(`storyProgress${i}`);
            if (!progressFill) continue;
            
            if (i < currentIndex) {
                // Completed stories
                progressFill.style.width = '100%';
                progressFill.style.transition = 'none';
            } else if (i === currentIndex) {
                // Current story - animate
                progressFill.style.width = '0%';
                progressFill.style.transition = 'none';
                requestAnimationFrame(() => {
                    progressFill.style.transition = `width ${STORY_DURATION}ms linear`;
                    progressFill.style.width = '100%';
                });
            } else {
                // Future stories
                progressFill.style.width = '0%';
                progressFill.style.transition = 'none';
            }
        }
    };

    // Helper function to clear all intervals
    const clearAllTimers = () => {
        if (storyTimer) clearTimeout(storyTimer);
        progressIntervals.forEach(interval => clearInterval(interval));
        progressIntervals = [];
    };

    // Profile edit modal functions (only for own profile)
    const openModal = () => {
        if (!modal) return;
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('open');
    };

    const closeModal = () => {
        if (!modal) return;
        modal.setAttribute('aria-hidden', 'true');
        modal.classList.remove('open');
    };

    const setAvatar = src => {
        if (!src) return;
        selectedAvatarSrc = src;
        if (avatarPreviewImg) {
            avatarPreviewImg.src = src;
            avatarPreviewImg.style.display = 'block';
        }
        if (avatarImg) {
            avatarImg.src = src;
            avatarImg.style.display = 'block';
        }
    };

    // Event listeners for profile editing (only if elements exist)
    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    backdrop?.addEventListener('click', closeModal);

    avatarUpload?.addEventListener('change', e => {
        const file = e.target.files && e.target.files[0];
        if (!file) return;
        try {
            const reader = new FileReader();
            reader.onload = ev => {
                if (ev.target?.result) setAvatar(ev.target.result);
            };
            reader.readAsDataURL(file);
        } catch (err) {
            console.error('Avatar load failed', err);
        }
    });

    if (avatarGrid) {
        avatarGrid.addEventListener('click', e => {
            const btn = e.target.closest('.avatar-grid__item');
            if (!btn || !btn.dataset.src) return;
            setAvatar(btn.dataset.src);
        });
    }

    saveBtn?.addEventListener('click', () => {
        if (!bioDisplay || !bioInput) return;
        bioDisplay.textContent = bioInput.value || 'Add a short bio to tell the world about you.';
        closeModal();
    });

    // Story upload (for own profile)
    storyAddBtn?.addEventListener('click', () => {
        storyUpload?.click();
    });

    storyUpload?.addEventListener('change', e => {
        const file = e.target.files && e.target.files[0];
        if (!file || !storyForm) return;
        storyForm.submit();
    });

    // Story viewer functions
    const startStoryTimer = () => {
        updateProgressBars(currentStoryIndex);
        
        clearAllTimers();
        storyTimer = setTimeout(() => {
            if (currentStoryIndex < storiesData.length - 1) {
                showStory(currentStoryIndex + 1);
            } else {
                closeStoryViewerFn();
            }
        }, STORY_DURATION);
    };

    const showStory = (index) => {
        if (!storiesData || index < 0 || index >= storiesData.length) return;
        currentStoryIndex = index;
        
        // Update story image
        if (storyViewerImg) storyViewerImg.src = storiesData[index];
        
        // Update counter
        if (storyCounter) storyCounter.textContent = `${index + 1} / ${storiesData.length}`;
        
        // Update time ago
        if (storyTime && storiesTimestamps[index]) {
            storyTime.textContent = formatTimeAgo(storiesTimestamps[index]);
        }
        
        // Show/hide navigation buttons
        if (prevStory) prevStory.style.display = index > 0 ? 'flex' : 'none';
        if (nextStory) nextStory.style.display = index < storiesData.length - 1 ? 'flex' : 'none';
        
        startStoryTimer();
    };

    const openStoryViewer = (stories, timestamps = [], startIndex = 0) => {
        if (!stories || stories.length === 0 || !storyViewer) return;
        
        storiesData = stories;
        storiesTimestamps = timestamps;
        currentStoryIndex = startIndex;
        
        // Create progress bars
        createProgressBars(stories.length);
        
        storyViewer.setAttribute('aria-hidden', 'false');
        storyViewer.classList.add('open');
        storyViewer.style.display = 'flex';
        
        showStory(currentStoryIndex);
    };

    const closeStoryViewerFn = () => {
        if (!storyViewer) return;
        clearAllTimers();
        storyViewer.classList.remove('open');
        storyViewer.setAttribute('aria-hidden', 'true');
        storyViewer.style.display = 'none';
        storiesData = [];
        storiesTimestamps = [];
        currentStoryIndex = 0;
        if (storyProgressBars) storyProgressBars.innerHTML = '';
    };

    // Story ring click handlers
    storyItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const allStories = Array.from(storyItems).map(s => s.getAttribute('data-story')).filter(Boolean);
            const allTimestamps = Array.from(storyItems).map(s => s.getAttribute('data-story-timestamp')).filter(Boolean);
            const clickedIndex = parseInt(this.getAttribute('data-story-index')) || 0;
            console.log('Story clicked!', { allStories, allTimestamps, clickedIndex });
            openStoryViewer(allStories, allTimestamps, clickedIndex);
        });
    });

    // Tap area navigation (Instagram-style)
    tapPrev?.addEventListener('click', (e) => {
        e.stopPropagation();
        if (currentStoryIndex > 0) {
            showStory(currentStoryIndex - 1);
        }
    });

    tapNext?.addEventListener('click', (e) => {
        e.stopPropagation();
        if (currentStoryIndex < storiesData.length - 1) {
            showStory(currentStoryIndex + 1);
        } else {
            closeStoryViewerFn();
        }
    });

    // Navigation buttons
    prevStory?.addEventListener('click', (e) => {
        e.stopPropagation();
        if (currentStoryIndex > 0) {
            showStory(currentStoryIndex - 1);
        }
    });

    nextStory?.addEventListener('click', (e) => {
        e.stopPropagation();
        if (currentStoryIndex < storiesData.length - 1) {
            showStory(currentStoryIndex + 1);
        }
    });

    // Close story viewer
    closeStoryViewer?.addEventListener('click', (e) => {
        e.stopPropagation();
        closeStoryViewerFn();
    });

    storyViewer?.addEventListener('click', (e) => {
        if (e.target === storyViewer || e.target.classList.contains('story-viewer__backdrop')) {
            closeStoryViewerFn();
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!storyViewer || !storyViewer.classList.contains('open')) return;
        
        if (e.key === 'ArrowLeft' && currentStoryIndex > 0) {
            showStory(currentStoryIndex - 1);
        } else if (e.key === 'ArrowRight' && currentStoryIndex < storiesData.length - 1) {
            showStory(currentStoryIndex + 1);
        } else if (e.key === 'Escape') {
            closeStoryViewerFn();
        }
    });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
