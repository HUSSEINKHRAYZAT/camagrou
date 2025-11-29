(() => {
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
    const storyActiveRing = document.getElementById('storyActiveRing');
    const storyThumb = document.getElementById('storyThumb');
    const storyLabel = document.getElementById('storyLabel');
    const addStoryLabel = document.getElementById('addStoryLabel');
    const storyViewer = document.getElementById('storyViewer');
    const storyViewerImg = document.getElementById('storyViewerImg');
    const storyProgressBar = document.getElementById('storyProgressBar');
    const STORY_DURATION = 7000;

    if (!modal || !openBtn || !bioInput || !bioDisplay) return;

    let selectedAvatarSrc = (avatarImg && avatarImg.getAttribute('src')) || '';
    let selectedStorySrc = null;

    const openModal = () => {
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('open');
    };

    const closeModal = () => {
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

    openBtn.addEventListener('click', openModal);
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
        bioDisplay.textContent = bioInput.value || 'Add a short bio to tell the world about you.';
        closeModal();
    });

    const setStory = src => {
        selectedStorySrc = src;
        if (storyThumb) {
            storyThumb.style.backgroundImage = `url(${src})`;
            storyThumb.style.display = src ? 'block' : 'none';
        }
        if (storyActiveRing) {
            storyActiveRing.dataset.story = src || '';
            storyActiveRing.classList.toggle('has-story', !!src);
            storyActiveRing.setAttribute('aria-hidden', src ? 'false' : 'true');
        }
        if (storyLabel) storyLabel.textContent = src ? 'View Story' : 'No story yet';
        if (addStoryLabel) addStoryLabel.textContent = src ? 'Change Story' : 'Add Story';
    };

    storyAddBtn?.addEventListener('click', () => {
        storyUpload?.click();
    });

    storyUpload?.addEventListener('change', e => {
        const file = e.target.files && e.target.files[0];
        if (!file || !storyForm) return;
        storyForm.submit();
    });

    const startStoryTimer = () => {
        if (!storyProgressBar) return;
        storyProgressBar.style.transition = 'none';
        storyProgressBar.style.width = '0%';
        requestAnimationFrame(() => {
            storyProgressBar.style.transition = `width ${STORY_DURATION}ms linear`;
            storyProgressBar.style.width = '100%';
        });
    };

    storyActiveRing?.addEventListener('click', () => {
        const src = storyActiveRing.dataset.story;
        if (!src || !storyViewer || !storyViewerImg) return;
        storyViewerImg.src = src;
        storyViewer.setAttribute('aria-hidden', 'false');
        storyViewer.classList.add('open');
        startStoryTimer();
        setTimeout(() => {
            storyViewer.classList.remove('open');
            storyViewer.setAttribute('aria-hidden', 'true');
        }, STORY_DURATION);
    });

    storyViewer?.addEventListener('click', () => {
        storyViewer.classList.remove('open');
        storyViewer.setAttribute('aria-hidden', 'true');
    });
})();
