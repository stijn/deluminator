# Working on Deluminator

- This plugin runs on Stijn's live site aardling.com and auto-updates from GitHub releases. Every push to main that bumps the version ships to production.
- Before pushing a version bump: `php -l` both PHP files, bump the version in the three places listed in README.md, add a CHANGELOG section.
- The site uses Twenty Nineteen plus Additional CSS (royal blue #4169e1 links, #f5f5f7 panels). New dark rules go in `assets/deluminator.css` under `html.deluminator-dark`.
- Test against the live pages before releasing: inject the head and footer output into a page and check light, dark, the reveal both ways, reload persistence and text contrast in dark mode.
- Never commit a GitHub token.
