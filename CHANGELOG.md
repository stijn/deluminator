# Changelog

## 1.1.0
- Fixed: the page flashed in its old colours for one frame at the end of the reveal. The clip now lives in CSS with fill "both", so it holds from the first frame to the last; the page's own CSS transitions pause during the switch. Checked frame by frame in Chromium and WebKit.
- New Auto state that follows the device, with the same icon as news.eurth.org and gov.eurth.org. The switch cycles Auto → the opposite of what you see → the other one → Auto, so the first click always changes the page. Auto is the default.
- Dark palettes, chosen under Settings › Deluminator: Solarized Dark (default), WCAG Dark (AAA contrast) and Dracula. Every text colour is checked against every background it sits on.
- A theme with its own dark mode (prefers-color-scheme styles) keeps it: the switch then drives the theme's light and dark styles. A setting can force the Deluminator palette instead.
- Mobile address-bar colour follows the actual page background.
- Dutch labels for the switch. Settings are removed when the plugin is deleted.

## 1.0.0
- The reveal now animates a real snapshot of the page (View Transitions API, as on writewithharper.com) instead of a flat coloured overlay. Going dark the lit page shrinks into the switch; going light it grows back out, from wherever the switch is.
- Fixed: the overlay stayed on screen after switching and covered the whole page.
- Fixed: dark pages flashed white on every load. The mode is now set in the head before the page paints.
- Follows the visitor's system setting until they choose; keeps tabs in step; updates color-scheme and theme-color.
- Dark styles for Twenty Nineteen and aardling.com's own panels, footnotes and mobile bottom menu. Images are dimmed slightly instead of made half-transparent.
- Accessible switch: aria-pressed, a label that says what it will do, visible focus, reduced-motion respected. Frosted disc so it stays visible over header photos.
- Choices saved by 0.8 carry over.
- Self-updates from GitHub releases.

## 0.8
- First version (February 2025).
