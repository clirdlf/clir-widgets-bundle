# PHP 8.3 and WordPress 7.1.2 code audit

Reviewed October 4, 2026. This repository is a small legacy plugin with active shortcodes, two registered widgets, and several dormant modules. Its largest risks are unescaped output, empty-result handling, broken rendering, and dependencies on older themes and external scripts. Deleting files solely because they are old would miss active content dependencies.

This review preserves runtime code. The new GitHub workflows and quality baselines provide a starting point for cleanup. Passing a baseline means no additional static findings; it does not mean the plugin is bug-free or ready for production.

## Target and evidence

WordPress [7.1.2 was released September 22, 2026](https://wordpress.org/documentation/wordpress-version/version-7-1-2/). WordPress documents [PHP 8.3 support](https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/); compatibility of WordPress core does not certify this plugin or its theme dependencies.

All 12 original PHP files were read, along with the JavaScript, package metadata, and Gulp configuration. Findings below come from those files. PHP lint and static checks run locally using PHP 8.5.11, with the compatibility scanner explicitly targeting PHP 8.3. Exact WordPress 7.1.2 / PHP 8.3 activation is configured in CI and was not run locally: this machine has no PHP 8.3 binary, database server, or running Docker daemon. No production database, saved widgets, themes, or live page contents were available.

## Bugs and output risks to address first

| Priority | Location | Trigger and resulting behavior | Cleanup direction |
| --- | --- | --- | --- |
| High | `lib/utilities.php:69–74`, called by `lib/shortcodes.php:523` | A category with no matching bundled image passes an empty array to `array_rand()`. This throws `ValueError` on PHP 8. A category such as the default `Blog` has no `blog*` images in this repository. | Handle `glob()` failure and empty results; prefer the selected post's thumbnail before a fallback. |
| High | `lib/shortcodes.php:26–73,151–208,296–335,437–438,536–538` | Shortcode attributes and content are concatenated into URLs, attributes, text, CSS, and HTML without context-appropriate escaping. Quotes or markup can break the page or inject executable markup. | Use `esc_url`, `esc_attr`, and `esc_html`; use `wp_kses_post` only where approved HTML is intended. Validate dimensions and CSS values. Test with users who lack `unfiltered_html`; exploitability depends on who can supply content. |
| High | `lib/informz-widget.php:30–48` | Saved settings are interpolated into quoted JavaScript strings. `strip_tags()` does not make a string safe for JavaScript. A quote can terminate the value. | Serialize settings with `wp_json_encode` and script-safe flags; validate collector and cookie-domain settings; enqueue the script with explicit dependencies. Confirm whether tracking is still needed. |
| Medium | `lib/shortcodes.php:547` | `[community_calendar]` registers a callback that is never defined in this repository. WordPress cannot invoke it; it reports an invalid shortcode callback and leaves the shortcode unrendered. | Remove after a site usage search or implement a supported replacement. The disabled calendar widget is a separate feature. |
| Medium | `lib/shortcodes.php:245–286`, `lib/utilities.php:36–45` | An empty random-publications query reads `posts[0]`; an unknown publication title returns null; a publication with no PDF reads `$pdf->ID` from false. | Guard absent posts and attachments. Return an empty string or useful fallback. Remove unused `end($query)`. |
| Medium | `lib/shortcodes.php:263` | `end()` receives a `WP_Query` object rather than its posts array. Object arguments have been deprecated since PHP 8.1. It does not help select a publication. | Delete the call. See the [PHP end manual](https://www.php.net/manual/en/function.end.php). |
| Medium | `lib/shortcodes.php:284` | `get_page_by_title()` produces a deprecation notice in modern WordPress. | Replace with a bounded `WP_Query` with an explicit post status. WordPress [deprecated it in 6.2](https://developer.wordpress.org/reference/functions/get_page_by_title/). |
| Medium | `lib/shortcodes.php:220–236`, `lib/utilities.php:36–45` | `get_thumb()` returns complete anchors plus an unclosed anchor, then `display_publication()` wraps that inside another anchor. | Return thumbnail markup separately from the single link wrapper. Escape the publication title and URL. |
| Medium | `lib/shortcodes.php:350–383,464–499` | `$output` is uninitialized in recent reports and in news when no posts match. News echoes its closing div instead of returning it. | Initialize output and return the entire markup; shortcode callbacks should not echo output. |
| Medium | `lib/shortcodes.php:395–405` | `dlf_excerpt()` references an undefined `$post`, truncates bytes rather than characters, and can lose an entire short/no-space excerpt. The semicolon makes its `if` statement an empty body. | Use an explicit post argument and `wp_trim_words()` or a documented character limit. Escape the returned permalink. |
| Medium | `lib/shortcodes.php:408–448` | A count of zero divides by zero; nonnumeric values can cause operand errors; unrestricted counts can create expensive queries. The secondary loop never resets global post data. | Bound and normalize count, use valid layout columns, call `wp_reset_postdata()`. The `length` argument is currently unused. |
| Medium | `lib/shortcodes.php:503–539` | Empty recent-post results are indexed. Thumbnail checks use the current global post, and `the_post_thumbnail()` echoes HTML and returns no URL. `setup_postdata()` receives an array and does not establish the selected global post. | Guard empty results and use explicit post IDs with `get_the_post_thumbnail_url()`. Avoid global post changes or restore them correctly. |
| Medium | `lib/shortcodes.php:50–73` | Missing content or an image URL not matching the regex leaves `$matches[1]` and `[2]` undefined; null content is passed to a string function. Thumbnail names are guessed rather than resolved. | Validate content and use WordPress attachment sizes; support query strings, uppercase extensions, and non-JPEG/PNG files if retained. |
| Medium | `lib/social-media-links.php:81` | New/empty widget instances read a missing title, and the stored title is output without escaping. The widget relies on Alchem theme options and icons. | Default the title, apply `widget_title`, escape it, and use sidebar title wrappers. Decide whether to replace the theme integration. |
| Medium | `clir-widgets-bundle.php:15–30` | The direct-request guard runs after WordPress API calls and includes. Direct access fails before reaching the intended guard. The asset URL constant passes `__FILE__` as the URL path, rather than the second plugin-file argument, producing a URL containing a filesystem path. | Put the `ABSPATH` guard before initialization and use `plugin_dir_url(__FILE__)` for the URL constant. `pluginS_URL` casing itself is valid because PHP functions are case-insensitive. |
| Medium if re-enabled | `lib/dlme-project-widget.php:41–79` | Form code assigns three different fields into `$title`, leaves `$url`/`$image` undefined, uses `utr` for the URL field, saves title into all fields, and dumps data during save. Rendered attributes are unescaped. | Remove or rewrite before registering. It is not loaded by the main plugin. |

The PHP manual confirms that [empty input to `array_rand()` raises `ValueError` since PHP 8.0](https://www.php.net/manual/en/function.array-rand.php). The local reproduction also confirmed this exception. WordPress's [output escaping guidance](https://developer.wordpress.org/plugins/security/securing-output/) explains why input sanitization alone cannot protect these output contexts.

## JavaScript and integration issues

`[clir_map]` loads an HTTP spiderfier script and `js/map.js` requests HTTP map tiles. HTTPS sites can block these resources as mixed content. The map declares only Leaflet as a dependency, although it also needs jQuery, marker clustering, spiderfier, and the data script. It ignores the `data` attribute and the localized `layer`, uses global `dlf`, has a fixed element ID, and references undefined `popup` in a click listener. No markers are attached to the spiderfier. Empty datasets and multiple map instances need explicit handling. Popup properties are inserted as raw HTML.

The dormant calendar uses Moment 2.14.1, fixed DOM IDs, global `php_vars`, a hardcoded calendar ID and browser API key, no explicit Moment dependency, and no API-error handling. The calendar formatter inserts remote event properties as HTML. A browser key is not inherently a secret, but its API/referrer restrictions and current ownership cannot be assessed from the code. Do not reuse the key when reviving this feature without checking those settings. The map also uses `php_vars`, so the two features can overwrite each other's localized data.

`js/unicorns.js` assumes global `$`, whereas WordPress normally uses jQuery in no-conflict mode. It loads a third-party script and accumulates every keypress indefinitely. It is not enqueued here.

Shortcode layouts depend on Bootstrap 3 classes and theme-loaded Font Awesome and Bootstrap JavaScript. The plugin does not provide those dependencies. A move to a block theme can leave the HTML present but visually or functionally broken. The modal points `aria-labelledby` to title text instead of the generated heading ID, has no image alt text, and is triggered by a non-keyboard-operable image. Duplicate titles create duplicate IDs.

## Removal candidates and what to verify

Repository reachability is evidence of inactivity here, not proof that a theme or another plugin never includes these files or calls their functions.

| Candidate | Repository evidence | Check before deleting |
| --- | --- | --- |
| `lib/dlme-project-widget.php` | Never required or registered; multiple form/save bugs. | Search installed themes/plugins and saved widget IDs. |
| `custom-post-types/people.php` | Never required by the main plugin. | Check for existing `people` posts, another registrar, and external includes. Removing a registrar does not delete data, but can hide it. |
| `lib/helpers.php` | Never required; no local calls to `clir_format_phone`. | Search themes and other plugins. |
| `partials/report.php` | Never included; stray `hi`, undefined context, and a title expression that is not echoed. | Check external template includes. |
| Calendar widget, formatter and `js/community_calendar.js` | Class file is required but registration is commented out; shortcode callback is missing. | Search `[community_calendar]`, saved widgets, and external registration. Remove the include alongside the module if retired. |
| `js/unicorns.js` | Not enqueued in this repository. | Search theme script tags and external enqueues. |
| `deadline`, `clir_category_link`, `the_excerpt_max_charlength`, `local_debug`, `get_category_ids`, `image_frame` callback details | First five are unregistered and/or have no local callers; `deadline` always returns empty and the excerpt-length helper ignores its length. `image_frame` itself **is registered** and must be inventoried separately. | Search external PHP callers; do not infer that a registered shortcode is unused. |
| `lib/overrides.php` | Active global TinyMCE iframe configuration, but tied to the classic visual editor. | Confirm editor usage; it overwrites rather than merges existing extended elements and does not replace server-side sanitization. |
| Gulp and npm setup, since removed | The reviewed setup required undeclared Gulp, used Gulp 3 syntax, and had unused Sass tasks and a deliberately failing `npm test`. | Removed along with the npm manifests. Composer and WP-CLI now handle checks and packaging; no asset compiler is required. |
| Bundled DLF images | Used dynamically by `random_image()`. | Do not delete until the retained `dlf_post` fallback is replaced. |

Before retiring any registered shortcode, search all sites for its tags, including drafts, reusable blocks/patterns, widget options, templates, and revision content as appropriate. Search for all 16 tags listed in `register_shortcodes()`. Export content and widget options, take a backup, and replace usages on staging. Keep an old plugin ZIP for rollback. Unregistering a shortcode can leave its literal tag visible to readers.

## Coding standards baseline

`phpcs.xml.dist` checks the full WordPress ruleset across all original PHP modules, including dormant files. `phpcompatibility.xml.dist` uses PHPCompatibilityWP with `testVersion=8.3`. The WordPress-specific ruleset accounts for WordPress polyfills; see its [upstream instructions](https://github.com/PHPCompatibility/PHPCompatibilityWP). The 3.0 and 10.0 tool generations currently require explicitly permitted prerelease dependencies; `composer.lock` pins the resolved versions.

Recurring cleanup areas are tabs and spacing, braces, array formatting, missing docblocks, generic global function names, missing translation domains, output escaping, `extract()`, and debug output. Prefix new functions/classes consistently. Keep shortcode tags and saved widget IDs stable unless content/options are migrated. Avoid adding stricter parameter types to legacy callbacks without first normalizing WordPress inputs.

The initial scan found **1,875 errors and 446 warnings**, for **2,321 WordPress standards findings**, of which 2,090 were marked automatically fixable. Many are formatting findings, not 2,321 distinct bugs. PHPCompatibilityWP found **zero static compatibility findings** for PHP 8.3. It does not infer all runtime types or execute code: the `end($query)` object deprecation and empty-array crash still require the manual review above. Do not run a broad automatic formatter before deciding what code to retire.

Baselines in `quality/` record counts per file, sniff, error/warning type, and message. The PHP checker runs through `composer check:standards` and `composer check:compatibility`; `composer check` runs both plus its regression tests. It preserves the original baseline encoding without regenerating the findings. Line movement does not invalidate them. Both errors and warnings count. CI rejects increases or new kinds of findings. This is a count-based budget, so replacing an old violation with another identical violation in the same file can escape detection. Review diffs and shrink baselines after fixes with `composer baseline:standards` or `composer baseline:compatibility`; never regenerate them just to make a failed check green. Full JSON reports retain line numbers in CI artifacts.

## GitHub CI and delivery

`.github/workflows/ci.yml` runs on pushes, pull requests, manual dispatch, and reusable calls. It installs locked development dependencies, runs the PHP checker regression tests, lints PHP, checks both baselines, saves full reports, installs WordPress 7.1.2 with MySQL 8.0 and PHP 8.3, activates the packaged plugin, checks widget registration, and renders two basic shortcodes. `composer package` uses WP-CLI's `dist-archive` command, installed as a locked Composer dependency and loaded through `vendor/autoload.php`. The `.distignore` file excludes development files, tests, shell utilities, and repository metadata. The ZIP always extracts into `clir-widgets-bundle` regardless of the checkout folder name. Packaging needs WP-CLI and PHP's ZIP extension, but no WordPress installation or database. Gulp and Python scripts are no longer used.

The smoke test intentionally covers only activation and two basic render paths. It does not cover the known broken shortcodes, full browser rendering, external services, multisite, or theme compatibility. Green CI must not be interpreted as resolving the findings above. Extend tests around each retained feature when fixing it: empty queries, missing PDFs/images, invalid attributes, output escaping, global post restoration, multiple widget instances, HTTPS map rendering, and modal keyboard access.

`.github/workflows/release.yml` is manually dispatched. It runs the same CI and downloads the checked ZIP to create a **draft** GitHub release at that commit, using the plugin header version as the tag. Publishing and installing remain manual. Update the plugin header before a new release; an existing tag/release is not overwritten. The current PHP header is 0.1.0 and is the release source of truth; the obsolete npm metadata has been removed. Review and stage the current build before distributing it.

No server deployment is configured because no destination or access method was supplied. A future server job should use a named staging environment, an immutable checked artifact, a production environment with required reviewers, and a rollback procedure. Do not insert deployment secrets into pull-request jobs. GitHub branch protection/rulesets, environment reviewers, repository permissions, and credentials are account settings and were not changed. Configure the two CI jobs as required checks after the first successful run.

## Suggested cleanup order

1. Inventory real site usage and choose the retained widgets and shortcodes.
2. Remove confirmed dormant modules and unused frontend/build dependencies in a separate change.
3. Fix escaping, empty inputs, broken publication/news markup, and post-data handling in retained paths.
4. Replace theme-specific layouts and old external scripts where still needed.
5. Reduce the standards baseline as fixes land; add behavior tests for the retained paths.
6. Run the exact target on staging and add server deployment once its destination and access method are known.
