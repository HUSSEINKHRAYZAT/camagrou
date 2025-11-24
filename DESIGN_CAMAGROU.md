# Camagrou Product Design System

Premium, content-first social experience for photo, video, stories, reels, messaging, and discovery. Built to scale across light, dark, AMOLED, and colorful accent themes on mobile and desktop.

## Brand Pillars
- Effortless focus on content: neutral surfaces, high contrast for media, limited chrome.
- Confident warmth: rounded radii, soft shadows, optimistic accent hues.
- Velocity: snappy animations, thumb-friendly interactions, predictable hierarchy.

## Identity
- Wordmark: geometric sans with subtle rounded terminals.
- Iconography: outline-first with matching filled states for active/selected, 2px stroke, 12px corner rounding on containers.
- Shape language: pill and 14–18px rounded cards, 8px internal rounding on media thumbs.

## Color System (themes)
Define color tokens; accent is user-selectable for colorful theme.

| Token | Light | Dark | AMOLED | Colorful (default accent Azure) |
| --- | --- | --- | --- | --- |
| `bg-primary` | #F8F9FC | #0F1115 | #000000 | same as theme |
| `bg-elevated` | #FFFFFF | #151820 | #0A0A0A | same as theme |
| `surface-muted` | #EEF1F6 | #1D212C | #0F0F0F | same as theme |
| `text-primary` | #0A0E16 | #EEF1F6 | #F5F7FB | same as theme |
| `text-secondary` | #5B6275 | #A9B0C2 | #A9B0C2 | same as theme |
| `accent` | #0066FF | #5EA0FF | #4DA0FF | #2F8CFF (user can pick Coral #FF5F6D, Lime #7ED321, Violet #9B6BFF, Gold #FFB347) |
| `success` | #2EC27E | #4CC38A | #4CC38A | same |
| `warning` | #F5A524 | #F8C156 | #F8C156 | same |
| `danger` | #E5484D | #FF6B6B | #FF6B6B | same |
| `divider` | #E3E7EF | #262A34 | #1A1A1A | same |
| `shadow` | rgba(0,34,85,0.08) | rgba(0,0,0,0.28) | rgba(0,0,0,0.32) | same |

State ramps: `accent-05/10/20/40/60`, `neutral-05/10/20` for hover, press, focus outlines.

## Typography
- Primary: `Satoshi` or `InterTight` (fallback: `Inter`, `system-ui`) — crisp, neutral, high legibility.
- Display: `Neue Haas Grotesk` (fallback `Helvetica Neue`) for headlines.
- Scale: H1 32/38 semibold, H2 24/30 semibold, H3 20/26 semibold, Body 16/22 regular, Secondary 14/20 regular, Caption 12/16 medium.
- Letter spacing tight (-1% on headlines), comfortable line-height 1.35–1.45.

## Spacing & Layout
- Base unit: 4px grid. Common paddings: 12/16/20/24.
- Cards: 16–20px padding, 14–18px radius, soft shadow.
- Lists/feeds: 12px vertical rhythm; gutters 16px mobile, 24px desktop.
- Breakpoints: 0–640 (mobile), 641–1024 (tablet), 1025+ (desktop). Desktop max width 1280 for content columns with fixed side rail (280px) and right rail (320px) for explore/notifications.

## Shadows & Blur
- `shadow-sm`: 0 4px 12px var(`shadow`).
- `shadow-md`: 0 8px 24px var(`shadow`).
- `shadow-lg`: 0 16px 40px rgba(0,0,0,0.20) for modals.
- Backdrop blur for nav bars and player chrome (8–12px).

## Components
- Buttons: primary (accent), secondary (surface outline), ghost, destructive. States: rest/hover/press/focus/disabled with 2px focus ring (accent-40).
- Icon buttons: 44px touch target, 12px radius, filled-on-active.
- Text fields: pill radius 14px, filled surface-muted, clear icon, left icon optional, focus ring accent-40.
- Search bar: 48px height, 14px radius, leading search icon, inline filter pills.
- Nav bar (mobile bottom): 5 icons (Home, Explore, Reels, Create, Messages). Active uses filled icon + accent underline. Desktop: left rail vertical nav with labels.
- Top bar: brand + search + actions (upload, notifications, profile).
- Cards: media-first with 16px padding on meta, actions row (like/comment/share/save), subtle separators.
- Story ring: 60px outer diameter mobile, 72px desktop; gradient ring for new stories, accent outline for seen; add button uses dotted ring.
- Chat bubbles: 16px radius; own messages accent-05 fill, others surface-muted; supports attachments, voice note waveform, reply chip.
- Reels controls: vertical stack right-aligned (like/comment/share/save, avatar-follow, music info pill), progress bar top, captions over gradient scrim.
- Avatars: 20/28/40/60 sizes; status dot bottom-right.
- Pills: filter, category, accent background with text secondary.
- Modals/sheets: bottom sheet 24px top radius mobile; desktop modal 640px width with center alignment.

## Motion Guidelines
- Global: 180–220ms ease-out for micro-interactions; 260–320ms for transitions; use cubic-bezier(0.22,0.61,0.36,1).
- Story transitions: horizontal swipe with parallax background and fade on chrome.
- Reel swipe: vertical snap, slight scale-down on incoming frame, persistent audio.
- Like: heart burst particles + quick fill (120ms) + haptic bump.
- Navigation: bottom-bar icon morph outline→filled; sheets slide-up with spring (overshoot 6%).
- Loading: skeleton shimmer 1400ms linear; lazy load media with fade-in + scale 0.98→1.

## Design Tokens (examples)
- `radius-xs 8`, `radius-sm 12`, `radius-md 14`, `radius-lg 18`, `radius-pill 999`.
- `border-thin 1px divider`, `border-strong 2px`.
- `opacity-muted 0.64`, `opacity-disabled 0.32`.
- `backdrop 8px`.

## Theme Behavior
- Toggle between Light/Dark/AMOLED/Colorful in Settings and quick-toggle in top bar.
- In AMOLED, use no gradients on backgrounds; keep gradients for story rings and CTA only.
- Accent selector (colorful): preset swatches + custom picker; apply to buttons, focus rings, links, sliders, toggles.

## Screen Blueprints (mobile → desktop)
### Home Feed
- Top: brand + search icon + inbox + create. Stories ring horizontally scrollable; tap to open viewer.
- Feed cards: media full-bleed width, metadata block with avatar, username, follow/overflow, action row, caption and comments preview, timestamp; infinite scroll with pull-to-refresh.
- Desktop: left nav rail, center feed 600px width, right rail for suggestions/trending.

### Story Viewer
- Fullscreen, horizontal swipe between stories, vertical swipe to exit. Progress bars top; tap left/right to navigate; bottom sheet for reply with text, emoji, quick reactions, send; share/save controls; long-press to pause.
- Create story entry point: add button on ring and in create sheet.

### Reels / Shorts
- Vertical immersive player with gesture controls: tap to pause, double-tap like, swipe up/down to change. Right stack for actions; bottom gradient with caption, music pill, hashtags; CTA follow button near avatar.
- Desktop: 2-up layout (player left 720px height, right column comments live).

### Explore + Search
- Search bar persistent at top; segmented filters (Top, Accounts, Audio, Tags, Places); trending chips.
- Grid: masonry 2–3 columns mobile, 4–6 desktop; hover/long-press reveals quick actions.
- Detail overlay: opens modal with media, metadata, related content rail.

### Messages
- Inbox: list with avatar, name, snippet, unread badge; filter tabs (All, Unread, Groups); search.
- Chat: sticky header with user status, call/video icons; composer with attachments, camera, sticker, voice hold-to-record; message statuses (sent/delivered/seen), reactions, reply threads.
- Desktop: split view (list left 320px, thread right).

### Notifications
- Tabs: All, Mentions, Follows, Comments, Likes; grouped by day; each row with icon, text, thumbnail, time; swipe left to mute/mark read.
- Desktop: right rail module plus full page.

### Profile
- Header: avatar, name, username, bio, link, counters (posts/followers/following), CTA follow/edit.
- Tabs: Posts (grid), Reels, Tagged, Highlights carousel, Guides.
- Grid: 3-col mobile, 5-col desktop; hover/long-press preview; pinned posts row.
- Edit profile: inline fields, avatar change, theme preview.

### Upload / Create Post
- Entry: bottom nav “Create” opens sheet: Post, Reel, Story, Live (future), Highlight.
- Flow: picker/camera → edit (crop, rotate, filters, brightness/contrast, stickers, text) → caption/add location/tag people → accessibility alt text → share options (also to story).
- Carousel support with reorder.

### New Story Creator
- Camera with AR stickers, text styles, brush, music, templates. Quick layout chips; background color picker; timer and multi-capture.
- Share sheet: close friends, followers, scheduled story.

### Login / Signup / Onboarding
- Minimal hero with gradient illustration; social proof; password visibility toggle; progressive disclosure for extra fields; error states concise.
- Onboarding: pick interests, follow suggestions, choose accent theme, enable notifications.

### Settings
- Sections: Account, Privacy, Notifications, Display & Theme, Security, About.
- Theme switcher with live preview of four themes.
- Privacy toggles, two-factor setup, blocked list, download data, clear cache.

## Wireframe Notes
- Mobile: maintain 16px gutters, 12px between stack elements, 44px min hit targets. Bottom bar persistent except in full-screen reels/stories.
- Desktop: left nav rail 72px (icon) expandable to 220px (labels); right rail contextual modules (suggestions, trending, notifications). Content column centered.
- Modals: use keyboard shortcuts hints on desktop; esc to close.

## Component Library (Figma-ready structure)
- Pages: Foundations → Components → Patterns → Screens → Prototypes.
- Use Auto Layout on all components; variants for size/state/theme.
- Components: buttons (4 styles x 3 sizes), inputs (text/password/search), text area, toggle, checkbox, radio, slider, dropdown, chips, tabs, bottom bar, top bar, cards (post/reel/story highlight), avatars, badges, tooltips, toasts, skeletons, pagination dots, breadcrumbs (desktop), empty states, banners, sheets, modals.
- Icons: 24px grid, 2px stroke; filled variants; custom set for camera, story, reel, explore, save, send.

## Interaction & Accessibility
- 44px touch targets; 2px focus ring with accent; ensure WCAG AA contrast per theme (use accent-60 on dark surfaces).
- Keyboard shortcuts on desktop: `Ctrl/Cmd+K` search, `C` create, `N` new message, `J/K` next/prev in feed, `Esc` close overlays.
- Haptics: light bump on taps, success vibration on post shared, subtle tick on scroll snapping.
- Reduce motion mode: disable parallax/overshoot, keep fades.

## Asset Guidance
- Illustration style: soft gradients with grain, rounded geometric shapes; avoid skeuomorphism.
- Media aspect ratios: support 4:5 (feed default), 1:1, 16:9 (reels), 9:16 (stories).
- Placeholder avatars and thumbnails using neutral gradients.

## Delivery Checklist
- Figma: organized frames for mobile and desktop per screen above, linked prototypes for nav flows (home → story → reel → explore → profile → settings → messages → upload).
- Document token styles, semantic colors, typography, effects, and motion library.
- Provide exportable icon set (SVG) and Lottie for micro-animations (like, loading).
- Include design QA notes: padding specs, target sizes, and motion timings next to components.
