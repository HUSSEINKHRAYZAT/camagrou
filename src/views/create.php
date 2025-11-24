<?php 
$title = 'Create - Camagru';
include 'header.php'; 
?>

<h1>Create Photo</h1>

<div class="create-page">
    <section class="camera-section viewport-panel">
        <div class="viewport-header">
            <div>
                <h3>Studio Canvas</h3>
                <p class="tool-hint">Record, upload, and layer effects in a distraction-free workspace.</p>
            </div>
            <span class="status-pill" id="cameraStatus">Camera idle</span>
        </div>

        <div class="capture-viewport">
            <video id="video" width="640" height="480" autoplay class="hidden"></video>
            <canvas id="previewCanvas" width="640" height="480"></canvas>
            <div class="viewport-placeholder" id="viewportPlaceholder">
                <p>Start your camera or upload an image to begin editing.</p>
                <small>Filters, emojis, and stickers are applied right inside this window.</small>
            </div>
        </div>
        <canvas id="canvas" width="640" height="480" style="display:none;"></canvas>

        <div class="upload-grid">
            <div>
                <p class="tool-hint">Select an emoji, sticker, or filter and tap directly inside the window to drop it.</p>
                <div class="controls">
                    <button id="startCamera" class="btn" type="button">Start Camera</button>
                    <button id="capture" class="btn btn-primary" type="button" disabled>Capture Photo</button>
                    <button id="upload" class="btn btn-outline" type="button">Browse Files</button>
                </div>
            </div>
            <label class="upload-zone" id="uploadZone">
                <input type="file" id="fileInput" accept="image/*">
                <span class="upload-icon">&#8682;</span>
                <strong>Drag &amp; drop files</strong>
                <small>or click to upload from your device</small>
            </label>
        </div>

        <form method="POST" action="index.php?page=create" id="saveForm" class="save-form">
            <input type="hidden" name="action" value="save_image">
            <input type="hidden" name="image_data" id="imageData">
            <button type="submit" class="btn btn-success" id="saveBtn" disabled>Save Image</button>
        </form>
    </section>

    <section class="preview-section tool-panel">
        <header class="tool-header">
            <div>
                <h3>Creative Console</h3>
                <p class="tool-hint">Browse curated stickers, emoji reactions, and cinematic filters in one place.</p>
            </div>
            <label class="picker-search">
                <input type="text" id="pickerSearch" placeholder="Search stickers, emojis, filters...">
                <span class="search-icon">&#x2315;</span>
            </label>
        </header>

        <div class="tab-buttons" role="tablist">
            <button type="button" class="tab-btn active" data-tab="stickers">Stickers</button>
            <button type="button" class="tab-btn" data-tab="emojis">Emojis</button>
            <button type="button" class="tab-btn" data-tab="filters">Filters</button>
        </div>

        <div class="tab-panels">
            <div class="tab-panel active" data-panel="stickers">
                <div class="picker-categories" data-picker="sticker">
                    <button type="button" class="category-chip active" data-category="all">All</button>
                    <button type="button" class="category-chip" data-category="fun">Fun</button>
                    <button type="button" class="category-chip" data-category="retro">Retro</button>
                </div>
                <div class="picker-grid" id="stickerList">
                    <?php $stickers = [
                        ['file' => 'sticker1.png', 'label' => 'Star Burst', 'category' => 'fun'],
                        ['file' => 'sticker2.png', 'label' => 'Retro Cam', 'category' => 'retro'],
                        ['file' => 'sticker3.png', 'label' => 'Cherry Pop', 'category' => 'fun']
                    ]; ?>
                    <?php foreach ($stickers as $sticker): ?>
                        <button type="button" class="picker-card sticker" 
                                data-sticker="<?php echo htmlspecialchars($sticker['file']); ?>"
                                data-category="<?php echo $sticker['category']; ?>"
                                data-search="<?php echo strtolower($sticker['label']); ?>">
                            <img src="public/stickers/<?php echo htmlspecialchars($sticker['file']); ?>" alt="<?php echo htmlspecialchars($sticker['label']); ?>">
                            <span><?php echo htmlspecialchars($sticker['label']); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="tab-panel" data-panel="emojis">
                <div class="picker-categories" data-picker="emoji">
                    <button type="button" class="category-chip active" data-category="all">All</button>
                    <button type="button" class="category-chip" data-category="reaction">Reactions</button>
                    <button type="button" class="category-chip" data-category="celebration">Celebration</button>
                    <button type="button" class="category-chip" data-category="symbols">Symbols</button>
                </div>
                <div class="emoji-grid" id="emojiList">
                    <?php $emojis = [
                        ['icon' => '😍', 'label' => 'Love', 'category' => 'reaction'],
                        ['icon' => '🔥', 'label' => 'Fire', 'category' => 'reaction'],
                        ['icon' => '😂', 'label' => 'LOL', 'category' => 'reaction'],
                        ['icon' => '😎', 'label' => 'Cool', 'category' => 'reaction'],
                        ['icon' => '🌈', 'label' => 'Rainbow', 'category' => 'symbols'],
                        ['icon' => '✨', 'label' => 'Sparkles', 'category' => 'symbols'],
                        ['icon' => '💥', 'label' => 'Boom', 'category' => 'celebration'],
                        ['icon' => '🎉', 'label' => 'Party', 'category' => 'celebration'],
                        ['icon' => '💫', 'label' => 'Whirl', 'category' => 'symbols'],
                        ['icon' => '🥳', 'label' => 'Festive', 'category' => 'celebration']
                    ]; ?>
                    <?php foreach ($emojis as $emoji): ?>
                        <button type="button" class="emoji-chip" data-emoji="<?php echo $emoji['icon']; ?>"
                                data-category="<?php echo $emoji['category']; ?>"
                                data-search="<?php echo strtolower($emoji['label']); ?>">
                            <span><?php echo $emoji['icon']; ?></span>
                            <small><?php echo htmlspecialchars($emoji['label']); ?></small>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="emoji-actions">
                    <label class="emoji-size">
                        Emoji Size
                        <div class="slider-shell">
                            <input type="range" id="emojiSize" min="24" max="160" value="72">
                            <span id="emojiSizeBubble" class="slider-bubble">72px</span>
                        </div>
                    </label>
                    <button type="button" class="btn btn-outline btn-small" id="clearEmojis">Clear Emojis</button>
                </div>
            </div>

            <div class="tab-panel" data-panel="filters">
                <p class="tool-hint">Preview and apply film-grade filters instantly.</p>
                <div class="filter-previews">
                    <?php $filters = [
                        ['id' => 'none', 'name' => 'Original'],
                        ['id' => 'grayscale', 'name' => 'Mono'],
                        ['id' => 'sepia', 'name' => 'Vintage'],
                        ['id' => 'warm', 'name' => 'Sunlit'],
                        ['id' => 'cool', 'name' => 'Neon'],
                        ['id' => 'bright', 'name' => 'Bright'],
                        ['id' => 'noir', 'name' => 'Noir']
                    ]; ?>
                    <?php foreach ($filters as $filter): ?>
                        <button type="button" class="filter-card <?php echo $filter['id'] === 'none' ? 'active' : ''; ?>" data-filter="<?php echo $filter['id']; ?>" data-search="<?php echo strtolower($filter['name']); ?>">
                            <span class="filter-thumb" data-filter="<?php echo $filter['id']; ?>"></span>
                            <span class="filter-name"><?php echo htmlspecialchars($filter['name']); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="public/js/camera.js"></script>

<?php include 'footer.php'; ?>
