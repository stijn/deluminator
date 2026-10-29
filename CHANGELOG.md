# Changelog

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
