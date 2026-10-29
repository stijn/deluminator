# Working on Deluminator

- This plugin runs on Stijn's live site aardling.com and auto-updates from GitHub releases. Every push to main that bumps the version ships to production.
- Before pushing a version bump: `php -l` every PHP file, bump the version in the three places listed in README.md, add a CHANGELOG section.
- The site uses Twenty Nineteen plus Additional CSS (royal blue #4169e1 links, #f5f5f7 panels). Palette rules go in `assets/deluminator.css` under `html.deluminator-palette.deluminator-dark` and use the `--dl-*` variables; palettes live in `includes/palettes.php`. Contrast-check any new colour.
- Before releasing, check the reveal frame by frame: no frame may show the lit page unclipped on top while the transition runs.
- Test against the live pages before releasing: inject the head and footer output into a page and check light, dark, the reveal both ways, reload persistence and text contrast in dark mode.
- Never commit a GitHub token.
