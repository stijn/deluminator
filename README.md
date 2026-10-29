# Deluminator

Light/dark switch for [aardling.com](https://aardling.com) (WordPress, Twenty Nineteen). Going dark, the lit page shrinks into the switch like light into Dumbledore's Deluminator; going light, it grows back out.

The reveal follows the toggle that [writewithharper.com](https://writewithharper.com) used, which came from [SveltePress](https://github.com/SveltePress/sveltepress): the View Transitions API takes a snapshot of the page before and after the switch and animates a circular `clip-path` between them, centred on the switch. Browsers without View Transitions, and visitors who ask for reduced motion, get an instant switch.

## What it does
- Remembers the visitor's choice (`localStorage`), otherwise follows the system setting and keeps following it until they choose.
- Sets the mode in the `<head>` before the page paints, so dark pages don't flash white on load.
- Updates `color-scheme` and the browser's `theme-color`, keeps open tabs in step.
- Real `<button>` with `aria-pressed` and a label that says what it will do; keyboard activation starts the reveal from the switch.
- Dark styles for Twenty Nineteen and the site's own extras: author box, footer, "Lees verder" pill, TL;DR boxes, Modern Footnotes, WP Mobile Bottom Menu.
- Carries over choices saved by version 0.8.

## Release flow
1. Bump the version in three places: the `Version:` header and `DELUMINATOR_VERSION` in `deluminator.php`, and `Stable tag` in `readme.txt`. Add a `## x.y.z` section to `CHANGELOG.md`.
2. Commit and push to `main`.
3. GitHub Actions checks the three numbers match, lints, builds `deluminator.zip` and creates release `vx.y.z`. Pushes without a version bump release nothing.
4. aardling.com checks every 12 hours and auto-updates through WordPress's update cron (or click "Check for updates" on the Plugins screen).

## One-time setup on the site
Plugins › Add New › Upload Plugin › `deluminator.zip` from the latest release › "Replace current with uploaded" › Activate. Updates after that arrive by themselves.

## Credits
- Reveal technique: SveltePress `ToggleDark.svelte` (MIT).
- Sun and moon icons: [line-md](https://github.com/cyberalien/line-md) by Vjacheslav Trushkin (MIT).
- Vendored: [plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker) 5.6 by Yahnis Elsts (MIT).
