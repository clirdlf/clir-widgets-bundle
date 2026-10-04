# PHP 8.3–8.5 and WordPress 7.1.2 code audit

Updated October 4, 2026 for plugin version **2.0.0**, after the removal, standards cleanup, and fixes to all five items in the bugs and output risks list. The plugin now registers four shortcodes and two filters; it registers no widgets and ships no JavaScript. WordPress standards and PHP 8.3–8.5 static compatibility scans are clean. Theme integration and production content still need staging verification.

## Scope and evidence

The deployment analysis includes PHP 8.5 for Ubuntu 26.04 LTS: Ubuntu's [default PHP package depends on PHP 8.5](https://packages.ubuntu.com/resolute/php). Automated targets are now PHP 8.3 and 8.5 with WordPress 7.1.2. Local integration checks passed on WordPress 7.1.2, PHP 8.5.11 on macOS, and a temporary MySQL 8.0 container, including network activation and headless Chrome rendering of a controlled fixture. PHP 8.3 execution, Ubuntu PHP-FPM, and actual production themes remain untested locally; the GitHub matrix has not been executed during this update.

WordPress's [compatibility table lists PHP 8.5 support for the 7.1 series](https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/). Core support does not establish compatibility of this plugin, other network plugins, or installed themes.

The current review covers the four remaining runtime PHP files, development checks, and GitHub workflows. Evidence also includes the supplied inventory exports for 26 network sites and the user's sidebar-placement results. No production database was modified. External themes, plugins, custom fields, or live rendering were not comprehensively inspected.

The main file loads `lib/filters.php`, `lib/shortcodes.php`, and `lib/overrides.php`. The unused `lib/utilities.php` module and its include have been removed. `custom-post-types/people.php`, which was not loaded by the main plugin, has also been removed. Quality rulesets and package verification no longer require its retired directory.

## Network usage and completed removals

The supplied shortcode inventory scanned 142,139 published documents across 26 sites. Five tags appeared in 200 distinct posts/pages on eight sites, with 516 occurrences. These are historical export results, not a fresh scan after cleanup. Per-tag document counts overlap because one document may contain several tags. Four of the detected tags remain registered; the map tag was retired afterward.

| Tag in original inventory | Posts/pages | Occurrences | Sites | Current status |
| --- | ---: | ---: | ---: | --- |
| `image_frame` | 166 | 276 | 1 | Registered |
| `clearboth` | 106 | 208 | 1 | Registered |
| `iframe` | 24 | 24 | 4 | Registered |
| `email` | 4 | 7 | 3 | Registered |
| `clir_map` | 1 | 1 | 1 | Removed; stored reference needs migration |

Two additional `[email]` matches occur in Contact Form 7 definitions and were separated from post/page totals because they may be form tags rather than this plugin's shortcode. Stored matches do not establish callback ownership or successful rendering. The local combined report is `report/shortcode-usage.html`; report files are excluded from distribution and Git.

The following cleanup is complete:

- Removed the eleven zero-match tags: `icon`, `community_calendar`, `recent_publications`, `publication`, `random_publication`, `last_featured`, `program_spotlight`, `dlf_post`, `dlf_news`, `menu_entry`, and `clir_modal_window`. Their callbacks and related helpers in `lib/shortcodes.php` were removed. The missing calendar callback, publication-query failures, deprecated publication lookup, broken news output, and modal issues from the original audit no longer exist in those paths.
- Removed the disabled calendar widget, `js/community_calendar.js`, its vendor formatter, and the unreferenced `js/unicorns.js`. Subsequently removed `[clir_map]`, its callback and external asset enqueues, and `js/map.js` at the user's request. No JavaScript remains in the plugin.
- Removed `Informz_Tracking_Widget`, `DLME_Project_Widget`, and `Social_Media_Links`, including applicable includes and registrations. The now-empty widget-loader function was removed.
- The user's network check found no active sidebar placements for `Social_Media_Links`: 19 instances were inactive across three sites (12 on DLF, four on CLIR, three on OR2021). This does not audit external direct PHP calls. Saved widget options were not deleted.
- Removed `clir_category_link`, `clir_format_phone` and its helper file, and the unused `partials/report.php`. Quality rulesets and package verification no longer require the retired `partials/` directory.
- Removed `lib/utilities.php` and all five legacy helpers, and removed `custom-post-types/people.php`. This code cleanup does not delete stored People records or widget settings. External includes or helper callers were not audited on the servers.
- Removed Gulp/npm build tooling and Python packaging/checker steps. Composer runs PHP quality checks; WP-CLI creates the release archive. A temporary local `package-lock.json` is excluded from distribution.

The inventory script intentionally retains all sixteen historical tags, so future scans can find retired tags in drafts or restored content. These removals were authorized by the user; the published-content inventory does not cover widget options, custom fields, or external template calls.

## Resolved bugs and output risks

Locations below use function names rather than line numbers so formatting changes do not invalidate references.

| Original priority | Location | Defect | Implemented fix |
| --- | --- | --- | --- |
| High | `lib/shortcodes.php`: `iframe()`, `image_frame()` | Unescaped attributes, URLs, CSS dimensions, and captions. | URLs allow HTTP/HTTPS; attributes and caption text use context-appropriate escaping. Pixel dimensions accept integers from 1–10,000; iframe dimensions also accept 1–100%. Invalid iframe dimensions use defaults; invalid image dimensions are omitted. Image class names are sanitized. |
| Medium | `lib/shortcodes.php`: `image_frame()` | Missing regex captures, null-content deprecation, limited suffix handling, and guessed thumbnails. | Normalize scalar input and return an empty string for missing/unsupported URLs. Recognize JPEG, PNG, GIF, WebP, and AVIF case-insensitively, including query strings/fragments. Resolve thumbnail URLs through WordPress media APIs; retain the original URL when unavailable. |
| Medium | `clir-widgets-bundle.php`: initialization | WordPress calls precede the direct-request guard; incorrect plugin asset URL. | Move the `ABSPATH` guard before initialization and use `plugin_dir_url(__FILE__)`. A direct CLI request exits cleanly without WordPress functions. |
| Medium | `lib/overrides.php`: `add_iframe()` | TinyMCE settings overwrite other extensions' element rules. | Append the iframe rule while preserving existing settings; preserve a pre-existing iframe rule and avoid duplicates. |
| Low | `lib/shortcodes.php`: `hide_email()` | Invalid or empty content returns null. | Normalize and trim scalar content, validate with `is_email()`, and always return a string. Escape the obfuscated address in both attribute and text contexts. |

Captions now display plain text: stored caption markup is escaped. Unknown images now retain their original URL rather than a guessed `-150x150` filename, so their displayed size may change when dimensions are absent. Review affected DLF content and iframe dimensions on staging. URL escaping does not establish trust in a remote iframe destination or guarantee external-service availability. The TinyMCE setting does not replace server-side HTML sanitization.

The excerpt customization now uses `get_the_excerpt` at priority 11 with the supplied post object, rather than deriving the link from the global post in `excerpt_more`. Its URL is escaped with `esc_url()` and its label with `esc_html__()` using the plugin text domain. It replaces only the default suffix on automatically truncated excerpts; manual excerpts, short excerpts, and different suffixes supplied by other filters are preserved. Direct `wp_trim_excerpt()` calls no longer receive this custom link. Active theme templates are not available in this repository, so their call paths and visible output remain unverified.

## PHP 8.5 migration assessment

An additional PHPCompatibilityWP scan covered PHP **8.3 through 8.5**, including the intervening PHP 8.4 changes. Runtime files and development scripts/tests both produced **zero static errors or warnings**. The installed scanner includes PHP 8.4/8.5 rules, but its coverage and ability to infer runtime values are limited. A clean scan does not certify full compatibility.

The compatibility gate now targets `8.3-8.5`. Reproduce runtime and development scans:

```sh
vendor/bin/phpcs --standard=phpcompatibility.xml.dist \
  --runtime-set testVersion 8.3-8.5 --report=json \
  --report-file=build/php85-compatibility-report.json

vendor/bin/phpcs --standard=phpcompatibility.xml.dist \
  --runtime-set testVersion 8.3-8.5 scripts tests --report=json \
  --report-file=build/php85-development-compatibility-report.json
```

The migration review checked these relevant changes against the remaining code:

| PHP change | Assessment of this repository |
| --- | --- |
| PHP 8.4 deprecates implicitly nullable typed parameters | Remaining callbacks use untyped parameters; their `= null` defaults do not match that deprecated typed declaration. If adding types, declare nullability explicitly. |
| PHP 8.4 deprecates omitted CSV escape arguments | The inventory script explicitly supplies an empty escape argument to `fputcsv()`. No finding was detected in the development scan. |
| PHP 8.5 deprecates noncanonical casts, backticks, null array keys, and nonnumeric string increments | No direct use was identified in the remaining runtime paths. The dormant excerpt-length helper that incremented caller-supplied `$charlength` has been removed. |
| PHP 8.5 warns about non-array destructuring and out-of-range numeric casts | No matching direct operation was identified in the remaining runtime code. Inputs and operations in external plugins/themes still require their own review. |
| PHP 8.5 integrates OPcache into the PHP binary | When migrating server configuration, check for a carried-over `zend_extension=opcache.so` directive, which now emits a warning. This is a server configuration check; no such configuration is stored here. |

Sources: PHP's [8.4 deprecations](https://www.php.net/manual/en/migration84.deprecated.php), [8.5 deprecations](https://www.php.net/manual/en/migration85.deprecated.php), and [8.5 incompatible changes](https://www.php.net/manual/en/migration85.incompatible.php).

Targeted probes on PHP 8.5.11 during the review confirmed these behaviors:

- Before this fix, `image_frame()` with omitted content emitted a null-to-string deprecation and undefined array-key warnings. The updated callback returns an empty string; the new targeted checks run with `E_ALL` and convert warnings/deprecations to exceptions.
- Before removal, `random_image()` with a nonexistent category threw `ValueError` because `array_rand()` received an empty array. The helper and its module have now been removed, eliminating that path from this plugin.

The escaping and image defects have now been fixed in the plugin. The nine quality-gate regression tests, WordPress integration tests, controlled browser checks, PHP lint, and ZIP checks passed under PHP 8.5.11. Runtime JavaScript has been removed and no longer needs a syntax check. The browser test contains development-only JavaScript and checks generated shortcode output with local assets. Production theme rendering and Ubuntu PHP-FPM behavior remain untested locally.

CI quality and WordPress jobs now test both PHP 8.3 and 8.5, and the compatibility ruleset targets `8.3-8.5`. Composer's platform setting was already changed to `8.5.0`; its lock metadata has been synchronized without changing dependency versions. The PHP 8.3 CI job will also check whether the locked dependencies remain installable on that older runtime. The platform setting itself does not establish compatibility. Both baselines remain empty. Staging with actual themes and `E_ALL` is still required before rollout.

## Retired map integration

The original inventory found one `[clir_map]` reference: the DLF page titled **Map**, post ID **14088**. The user subsequently authorized removal of the shortcode and JavaScript despite that stored reference. The page's content was not changed; its tag needs removal or replacement on the site. The inventory script continues to detect the retired tag.

Mixed-content requests, dependency/cache issues, unsafe popup HTML, the undefined `popup`, empty bounds, and multiple-instance limitations are retired with the map code. The plugin no longer enqueues Leaflet, map data, clustering, or spiderfier.

`clearboth` still depends on Bootstrap 3 classes supplied by the theme. Retained output must be checked against each site's actual theme; stored shortcode usage alone does not establish visual compatibility.

## Remaining removal candidates

| Candidate | Current evidence | Further check |
| --- | --- | --- |
| `deadline()` | Not registered or called locally; always returns an empty string. | Search external PHP callers before removal. |
| Bundled DLF images and `process.sh` | Both `dlf_post` and `random_image()` have been removed; no remaining local code references the images. | Check external direct asset references before retiring the files. `process.sh` is already excluded from distribution. |
| Excerpt customization | Historical checks showed the old `excerpt_more` callback attached across the network. It now attaches to `get_the_excerpt`, with explicit post context. Actual frontend use remains unverified. | Search active themes for `the_excerpt()`, `get_the_excerpt()`, direct `wp_trim_excerpt()` calls, and callback removals. Observe uncached frontend requests and inspect rendered excerpts. |
| TinyMCE iframe override | Still attached to `tiny_mce_before_init`. | Confirm whether any sites still use the Classic Editor and need this setting. |

The removed utilities included empty-image-array handling, missing-PDF handling, incomplete link markup, and an ignored excerpt-length argument. Those findings are retired with the module. Sites that directly included the removed files or called their helpers need migration; removing the People registrar does not delete its stored database records.

## Current quality results

Local checks rerun against the current 2.0.0 working tree on October 4, 2026:

| Check | Result |
| --- | --- |
| WordPress standards | Pass: zero errors and zero warnings |
| Standards baseline gate | Pass: zero findings; baseline is empty |
| PHPCompatibilityWP targeting PHP 8.3–8.5 | Pass: zero static findings |
| Additional PHPCompatibilityWP scan targeting PHP 8.3–8.5 | Pass: zero static findings in runtime and development code |
| Quality-gate regression tests | Pass: all nine |
| Shortcode/editor and parser checks | Pass in a full WordPress 7.1.2 installation on PHP 8.5.11 |
| Real media integration | Pass: real upload, generated thumbnail, missing-metadata fallback and cleanup |
| Excerpt edge cases | Pass: explicit post, manual/short excerpt, custom suffix, invalid context, translated label and URL escaping |
| Multisite integration | Pass: network activation, two temporary subsites, rendering, media isolation and context restoration |
| Controlled browser rendering | Pass in headless Chrome: image loading/sizing, caption text, local iframe and email link |
| Direct-request guard | Pass: exits without output or undefined WordPress calls |
| Project PHP lint | Pass on PHP 8.5.11 |
| JavaScript syntax | Not applicable: all JavaScript removed |
| Release ZIP verification | Pass: 11 runtime files, no development files |
| WordPress 7.1.2 / PHP 8.3 activation and browser rendering | Not run locally |
| WordPress 7.1.2 / PHP 8.5 activation | Pass locally on macOS with MySQL 8.0 |
| Ubuntu PHP-FPM and production-theme rendering | Not run locally |

Formatting, documentation, translation domains, parameter usage, and assignment placement have been corrected. Map enqueue warnings were initially corrected, then the entire map feature was removed. The excerpt filter now accepts the excerpt and its post object; the unregistered deadline helper no longer accepts an unused content parameter. The original scan had 2,321 standards findings; the current count is zero. The manual defects listed above were addressed separately from the standards scan.

`tests/shortcode-risks.php` checks quote injection, unsafe URL schemes, absent/non-scalar content, image formats and query strings, thumbnail selection, dimension bounds, email output, and preservation/idempotency of TinyMCE settings. `tests/shortcode-parsing.php` also exercises `do_shortcode()`, adjacent instances, and escaped examples. `tests/media.php` creates a real upload and generated thumbnail, removes metadata to check fallback, and restores metadata before cleanup. `tests/excerpt-edge-cases.php` covers custom suffixes, invalid post contexts, translated label escaping, and unusual permalinks. `tests/multisite.php` runs the suite on two temporary subsites and checks media and restored site context. These tests passed in a full local WordPress installation. External PHP callers of changed or removed helpers were not audited.

`tests/bootstrap.php` requires `CLIR_TEST_ENV=1` and converts unsuppressed warnings/deprecations to exceptions during execution. Use disposable installations: these tests create and delete records, files and sites. The local WP-CLI 2.12.0 bundle emits a PHP 8.5 deprecation before the test handler is installed; this tool-startup issue is separate from the passing plugin tests.

Both baselines are now empty after the clean scans. Any new finding fails the gate. Baselines are keyed by file, sniff, severity, and message; line shifts do not invalidate them. Do not regenerate them merely to accept regressions.

Run `composer check` for database-free baseline regression tests and both gates. WordPress integration tests use `CLIR_TEST_ENV=1 composer test:wordpress -- --path=/path/to/disposable-wordpress`; multisite uses `composer test:multisite` with the same environment flag and a disposable network path. Browser commands and requirements are documented in the README. Since `composer check` stops at a failing standards gate, run `composer check:compatibility` separately when needed. Full reports are written to `build/standards-report.json` and `build/compatibility-report.json`.

## GitHub CI and delivery

The CI workflow has PHP 8.3/8.5 matrices for quality and WordPress 7.1.2/MySQL integration. It activates the packaged plugin, runs the shortcode/media/excerpt suite, checks the generated browser fixture in headless Chrome, converts the disposable installation to a subdirectory network, network-activates the plugin, and runs the two-subsite suite. Registration checks cover all four retained tags and absence of twelve retired tags. Browser results and static reports have separate artifact names per PHP version; only the PHP 8.3 job uploads the release ZIP, avoiding duplicate artifact writes.

Browser coverage checks actual loading and dimensions using local image/iframe assets and plain browser styles. It does not establish appearance in the DLF theme or on production pages. Multisite coverage exercises two subdirectory sites with network activation, not every network configuration or third-party plugin interaction. CI has not been executed as part of this local update; local PHP 8.5 integration and quality checks pass. Actual theme verification for the changed excerpt hook remains pending because no active theme templates were supplied.

`composer package` uses WP-CLI's Composer-installed `dist-archive` command and verifies the ZIP against remaining runtime files. Packaging needs WP-CLI and PHP's ZIP extension, but no WordPress database. Reports, tests, development dependencies, scratch files, and metadata are excluded. The archive extracts into `clir-widgets-bundle`.

The release workflow runs CI and creates a draft GitHub release from the checked commit, using the plugin header version (currently `2.0.0`). Publishing and installation remain manual; increment the header before a new release. No server deployment or account-level branch protection was configured. Validate the retained features on staging and keep the preceding plugin archive for rollback.

## Next cleanup steps

1. Keep both baselines empty and reject new standards or compatibility findings.
2. Establish usage of `deadline()`, the excerpt filter, and the TinyMCE override; remove confirmed unused paths. Check for external references to retired files before rollout.
3. Review affected shortcode pages on staging, especially caption markup, image sizing, and invalid dimension fallbacks.
4. Remove or replace the retired `[clir_map]` reference on DLF page 14088 and review the page on staging.
5. Confirm the new PHP 8.3/8.5 CI matrix passes, then test Ubuntu 26.04/PHP 8.5 and affected production templates across the network before releasing.
