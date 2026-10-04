# CLIR Widgets Bundle

A bundle of WordPress widgets for CLIR + DLF sites.

## Development Setup

Use a local WordPress installation such as MAMP, and link this repository into its `wp-content/plugins` directory:

```
$ cd /Applications/MAMP/htdocs/wordpress/wp-content/plugins
$ ln -s ~/projects/clir-widgets-bundle
```

Then activate the plugin in the WordPress admin panel.

Development requires PHP 8.3+, Composer 2, and WP-CLI for packaging. PHP's ZIP extension must be enabled. No Gulp, Node.js, or Python build step is required.

## Review and GitHub workflows

See [the PHP 8.3–8.5 and WordPress 7.1.2 audit](docs/compatibility-audit.md) for bugs, style issues, removal candidates, test limits, and the cleanup order. The plugin no longer ships JavaScript or compiles frontend assets.

Install the development checks with Composer 2 and run:

```sh
composer install
composer check
composer package
```

`composer check` runs the PHP checker regression tests and both baseline checks.
You can run them separately with `composer test`, `composer check:standards`, and
`composer check:compatibility`. Full JSON reports are written to `build/`.

`composer standards` and `composer compatibility` report the entire backlog and
return nonzero when findings remain. After fixing or removing legacy code, review
those reports, then shrink the baseline with `composer baseline:standards` and
`composer baseline:compatibility`. Commit reviewed baseline changes with the cleanup.

`composer package` runs WP-CLI's `dist-archive` command, installed through Composer
and loaded with `--require=vendor/autoload.php`. It needs no WordPress installation
or database. The `.distignore` file excludes development files. The resulting
`build/clir-widgets-bundle.zip` always extracts into `clir-widgets-bundle`, even if
your checkout directory has a different name. Each run replaces the previous ZIP
and verifies that every runtime file is present and development files are excluded.

GitHub Actions runs lint, both baselines, and a WordPress 7.1.2 activation smoke test
on PHP 8.3. It uploads a plugin ZIP and full static reports. Manually dispatch
**Prepare draft plugin release** to rerun checks and attach the tested ZIP to a draft
release. The plugin header version determines the tag; increment it before a new
release. Publish the draft and install on staging manually. Server deployment needs
a destination and credentials; it is not configured here.

## Inventory shortcode usage before cleanup

The read-more customization runs on `get_the_excerpt` using the requested post's
ID. It escapes the link URL and translated label, and replaces only the default
automatic-excerpt suffix. Manual excerpts, short excerpts, and other custom
suffixes are preserved. Themes that call `wp_trim_excerpt()` directly bypass this
customization; check active templates before deployment.

The October 2026 network report found references to `clearboth`, `iframe`, `email`,
`clir_map`, and `image_frame`. The map shortcode and its JavaScript were subsequently
removed at the user's request; the other four tags remain registered. The eleven tags with zero
matches were removed: `icon`, `community_calendar`, `recent_publications`,
`publication`, `random_publication`, `last_featured`, `program_spotlight`,
`dlf_post`, `dlf_news`, `menu_entry`, and `clir_modal_window`. Their callbacks and
helpers exclusive to those callbacks were also removed from `lib/shortcodes.php`.
The inventory script continues to search all sixteen historical tags so it can
detect retired tags in drafts or content restored later. The report covers stored
published content; external themes, widget options, and custom fields were not scanned.

JavaScript cleanup also removed `js/map.js` and the `[clir_map]` callback. The disabled community
calendar widget, `js/community_calendar.js`, its bundled calendar formatter, and
the unreferenced `js/unicorns.js` Easter egg have been removed. The shortcode
inventory does not establish whether an external theme registered the old widget
or loaded those scripts directly.

Copy `scripts/inventory-shortcodes.php` to each site's server, or use a local copy
of each site's database. Run it through WP-CLI in that WordPress installation:

```sh
wp --path=/path/to/site-one eval-file /path/to/inventory-shortcodes.php > site-one-shortcodes.csv
wp --path=/path/to/site-two eval-file /path/to/inventory-shortcodes.php > site-two-shortcodes.csv
```

This read-only script scans published posts, pages, and other published post types
in batches of 500. It reports exact shortcode tag names, occurrence counts, titles,
and view/edit links. A per-tag summary, including zero counts, appears in the terminal.
It never renders shortcodes and works even when this plugin is inactive. For multisite,
add `--url=https://the-subsite.example` and run once per subsite.

### WordPress Multisite

For two subsites in one network, use the same installation path and select each
subsite by URL. List the URLs first:

```sh
wp --path=/path/to/wordpress site list --fields=blog_id,url
wp --path=/path/to/wordpress --url=https://site-one.example eval-file /path/to/inventory-shortcodes.php > site-one-shortcodes.csv
wp --path=/path/to/wordpress --url=https://site-two.example eval-file /path/to/inventory-shortcodes.php > site-two-shortcodes.csv
```

The script uses the selected subsite's posts table; a run without `--url` does not
scan the entire network. The URL can be a mapped domain, subdomain, or subdirectory.
If the plugin is network-activated, review every subsite that might use it before
deleting shared code. Check widgets and template content on each subsite, and check
shared themes, network plugins, and `mu-plugins` for direct PHP calls or includes.
Broad `wp db search --all-tables-with-prefix` searches can include tables belonging
to other subsites, so do not interpret those matches as belonging only to `--url`.

To include drafts, scheduled/private posts, trash, and revisions, pass `all`:

```sh
wp --path=/path/to/site eval-file /path/to/inventory-shortcodes.php all > all-shortcodes.csv
```

Matches are review candidates: literal shortcode text inside examples or block JSON
can appear without executing. Escaped `[[shortcode]]` examples are excluded. The script
searches `post_content`; it does not scan custom fields, widget options, plugin tables,
or PHP templates. Published reusable blocks and database-stored templates are included
if their status is `publish`, even if they are not currently referenced on a page.

Before removing a zero-count feature, also check Appearance > Widgets (including
inactive widgets), Site Editor templates/patterns, and builder/custom-field data. For
a broader database search, use `wp db search '[clir_map' --all-tables-with-prefix`
(substitute each tag). Search installed themes and other plugins for shortcode tags,
callback names, direct file includes, and script enqueues. Replace live usages on staging
before unregistering a shortcode; then review the affected pages and widget areas.
