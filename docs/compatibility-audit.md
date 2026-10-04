# PHP 8.3–8.5 and WordPress 7.1.2 code audit

Updated October 4, 2026 after the shortcode, JavaScript, widget, partial, and helper cleanup. The plugin now registers five shortcodes and two filters; it registers no widgets. The main remaining risks are unescaped shortcode output, fragile image handling, and the legacy map integration.

## Scope and evidence

The deployment analysis now includes PHP 8.5 for Ubuntu 26.04 LTS: Ubuntu's [default PHP package depends on PHP 8.5](https://packages.ubuntu.com/resolute/php). The existing automated targets remain PHP 8.3 and WordPress 7.1.2. The workflow's version setting is a test configuration, not evidence that its download or activation has succeeded. Local checks used PHP 8.5.11 on macOS; Ubuntu's PHP-FPM configuration and a complete WordPress runtime were not tested locally.

WordPress's [compatibility table lists PHP 8.5 support for the 7.1 series](https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/). Core support does not establish compatibility of this plugin, other network plugins, or installed themes.

The current review covers the six remaining runtime PHP files, `js/map.js`, development checks, and GitHub workflows. Evidence also includes the supplied inventory exports for 26 network sites and the user's sidebar-placement results. No production database was modified. External themes, plugins, custom fields, or live rendering were not comprehensively inspected.

The main file loads `lib/filters.php`, `lib/shortcodes.php`, and `lib/overrides.php`. The shortcodes file loads `lib/utilities.php`. `custom-post-types/people.php` remains in the package but is not loaded by the main plugin.

## Network usage and completed removals

The supplied shortcode inventory scanned 142,139 published documents across 26 sites. Five tags appeared in 200 distinct posts/pages on eight sites, with 516 occurrences. Per-tag document counts overlap because one document may contain several tags.

| Retained tag | Posts/pages | Occurrences | Sites |
| --- | ---: | ---: | ---: |
| `image_frame` | 166 | 276 | 1 |
| `clearboth` | 106 | 208 | 1 |
| `iframe` | 24 | 24 | 4 |
| `email` | 4 | 7 | 3 |
| `clir_map` | 1 | 1 | 1 |

Two additional `[email]` matches occur in Contact Form 7 definitions and were separated from post/page totals because they may be form tags rather than this plugin's shortcode. Stored matches do not establish callback ownership or successful rendering. The local combined report is `report/shortcode-usage.html`; report files are excluded from distribution and Git.

The following cleanup is complete:

- Removed the eleven zero-match tags: `icon`, `community_calendar`, `recent_publications`, `publication`, `random_publication`, `last_featured`, `program_spotlight`, `dlf_post`, `dlf_news`, `menu_entry`, and `clir_modal_window`. Their callbacks and related helpers in `lib/shortcodes.php` were removed. The missing calendar callback, publication-query failures, deprecated publication lookup, broken news output, and modal issues from the original audit no longer exist in those paths.
- Removed the disabled calendar widget, `js/community_calendar.js`, its vendor formatter, and the unreferenced `js/unicorns.js`. `js/map.js` remains for the retained map shortcode.
- Removed `Informz_Tracking_Widget`, `DLME_Project_Widget`, and `Social_Media_Links`, including applicable includes and registrations. The now-empty widget-loader function was removed.
- The user's network check found no active sidebar placements for `Social_Media_Links`: 19 instances were inactive across three sites (12 on DLF, four on CLIR, three on OR2021). This does not audit external direct PHP calls. Saved widget options were not deleted.
- Removed `clir_category_link`, `clir_format_phone` and its helper file, and the unused `partials/report.php`. Quality rulesets and package verification no longer require the retired `partials/` directory.
- Removed Gulp/npm build tooling and Python packaging/checker steps. Composer runs PHP quality checks; WP-CLI creates the release archive. A temporary local `package-lock.json` is excluded from distribution.

The inventory script intentionally retains all sixteen historical tags, so future scans can find retired tags in drafts or restored content. These removals were authorized by the user; the published-content inventory does not cover widget options, custom fields, or external template calls.

## Remaining bugs and output risks

Locations below use function names rather than line numbers so formatting changes do not invalidate references.

| Priority | Location | Trigger and resulting behavior | Recommended change |
| --- | --- | --- | --- |
| High | `lib/shortcodes.php`: `iframe()`, `image_frame()` | Attributes and content enter URLs, HTML attributes, CSS, and captions without context-appropriate escaping. Quotes or markup can break output or introduce executable markup; exploitability depends on who can supply content. | Validate dimensions and supported values; use URL, attribute, and text escaping. Allow HTML only where intentionally supported. `extract()` has been replaced with explicit attribute access. |
| Medium | `lib/shortcodes.php`: `image_frame()` | Missing content or a nonmatching URL leaves regex captures undefined. The regex uses an unescaped dot and only accepts lowercase JPEG/PNG suffixes. Thumbnail filenames are guessed rather than resolved. | Normalize content, check matches, and prefer attachment metadata/sizes. Handle query strings, unsupported formats, and missing thumbnails gracefully. |
| Medium | `clir-widgets-bundle.php`: initialization | WordPress API calls and includes run before the direct-request guard. `CLIR_WIDGETS_PLUGIN_URL` passes `__FILE__` as a URL path instead of the plugin-file argument. | Put the `ABSPATH` guard before initialization and use `plugin_dir_url(__FILE__)`. The mixed-case function name itself is valid PHP. The URL constant currently has no local consumers after widget removal. |
| Medium | `lib/filters.php`: `wpdocs_excerpt_more()` | The generated permalink is not escaped. It derives the post ID from global context, which can be wrong when an excerpt is generated for another post. | Escape output and verify post context in actual templates before retaining this customization. The translation now uses the plugin text domain. |
| Medium | `lib/overrides.php`: `add_iframe()` | The TinyMCE filter replaces existing `extended_valid_elements`, potentially discarding another extension's settings. | Merge existing configuration if still needed. Confirm Classic Editor usage; this setting does not replace server-side HTML sanitization. |
| Low | `lib/shortcodes.php`: `hide_email()` | Invalid or empty content returns null rather than a string. | Normalize callback input and return an empty string for invalid addresses. Test valid and invalid content. |

Escaping must match the output context. Static compatibility checks do not validate permissions, runtime input types, browser behavior, or external-service availability.

## PHP 8.5 migration assessment

An additional PHPCompatibilityWP scan covered PHP **8.3 through 8.5**, including the intervening PHP 8.4 changes. Runtime files and development scripts/tests both produced **zero static errors or warnings**. The installed scanner includes PHP 8.4/8.5 rules, but its coverage and ability to infer runtime values are limited. A clean scan does not certify full compatibility.

Reproduce the additional scans without changing the existing PHP 8.3 gate:

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
| PHP 8.5 deprecates noncanonical casts, backticks, null array keys, and nonnumeric string increments | No direct use was identified in the reviewed paths, except that the dormant excerpt-length helper increments caller-supplied `$charlength`; nonnumeric string input could hit the increment deprecation. Remove that unused helper or validate its input. |
| PHP 8.5 warns about non-array destructuring and out-of-range numeric casts | No matching direct operation was identified in the remaining runtime code. Inputs and operations in external plugins/themes still require their own review. |
| PHP 8.5 integrates OPcache into the PHP binary | When migrating server configuration, check for a carried-over `zend_extension=opcache.so` directive, which now emits a warning. This is a server configuration check; no such configuration is stored here. |

Sources: PHP's [8.4 deprecations](https://www.php.net/manual/en/migration84.deprecated.php), [8.5 deprecations](https://www.php.net/manual/en/migration85.deprecated.php), and [8.5 incompatible changes](https://www.php.net/manual/en/migration85.incompatible.php).

Targeted probes on PHP 8.5.11 confirmed two existing failures:

- `image_frame()` with omitted content emits a null-to-string deprecation from `preg_match()` and undefined array-key warnings for the missing captures. The probe supplied a minimal `shortcode_atts()` substitute; it tested the helper's PHP behavior, not WordPress rendering. This is a pre-existing defect still present on PHP 8.5.
- `random_image()` with a nonexistent category throws `ValueError` because `array_rand()` receives an empty array. It has no remaining local callers, so this is a dormant helper defect unless external code calls it.

PHP 8.5 did not eliminate the escaping, map, or image issues described elsewhere in this audit. The nine quality-gate regression tests passed under PHP 8.5.11; PHP lint, JavaScript syntax, and ZIP checks also passed during this review. Full WordPress activation, frontend rendering, and Ubuntu PHP-FPM behavior remain untested.

Before a production upgrade, extend the CI quality and WordPress jobs to test both PHP 8.3 and 8.5, widen the compatibility ruleset to `8.3-8.5`, and test staging with `E_ALL`. The Composer platform setting remains `8.3.0`, preserving dependency resolution for the older supported runtime; it is not evidence of PHP 8.5 compatibility. No workflow, dependency lock, baseline, or production configuration was changed for this analysis update.

## Map integration

`clir_map` has one stored post/page reference: the DLF page titled **Map**, post ID **14088**. It therefore remains registered.

The PHP callback loads an HTTP spiderfier script; `js/map.js` requests HTTP tiles. HTTPS pages can block these resources. Map dependencies now explicitly include jQuery, Leaflet, map data, clustering, and spiderfier, with footer loading. Pinned libraries have release versions; unpinned remote resources use the callback file's modification time as a cache revision, not as an upstream release version. The local map script uses its own modification time. Changes to remote data alone do not change those local cache revisions. The map still ignores the supplied `data` attribute and localized `layer`, uses a fixed element ID, and does not support multiple instances.

The spiderfier click listener references undefined `popup`, and no markers are added to the spiderfier. `fitBounds()` has no empty-dataset guard. Remote organization and location properties are inserted into popup HTML without escaping. Review whether spiderfier is needed alongside clustering, use HTTPS assets and explicit dependencies, validate data, and test empty datasets and multiple maps before deployment.

`clearboth` still depends on Bootstrap 3 classes supplied by the theme. Retained output must be checked against each site's actual theme; stored shortcode usage alone does not establish visual compatibility.

## Remaining removal candidates

| Candidate | Current evidence | Further check |
| --- | --- | --- |
| `custom-post-types/people.php` | Not included by the main plugin; defines the public `people` post type. | Count records across every site, including nonpublished statuses. Search external registrars, includes, queries, templates, and navigation links. Removing registration can hide existing content without deleting it. |
| `deadline()` | Not registered or called locally; always returns an empty string. | Search external PHP callers before removal. |
| `lib/utilities.php` functions | No remaining local callers for `the_excerpt_max_charlength`, `get_thumb`, `local_debug`, `clean_category`, or `random_image`; the file is still included. | Search installed themes/plugins for calls and includes. If unused, remove the functions and include together. |
| Bundled DLF images and `process.sh` | The former `dlf_post` caller is removed. `random_image()` still references the images, although it has no local callers. | Resolve external helper usage before retiring its assets. `process.sh` is already excluded from distribution. |
| `excerpt_more` customization | User reports the callback attached across the site list; actual frontend use remains unverified. | Observe uncached frontend requests and inspect rendered excerpts. Attachment alone does not prove visible use. |
| TinyMCE iframe override | Still attached to `tiny_mce_before_init`. | Confirm whether any sites still use the Classic Editor and need this setting. |

Dormant utility bugs remain relevant only if external callers use them: `random_image()` can pass an empty array to `array_rand()` and throw on PHP 8; `get_thumb()` dereferences a missing PDF and returns incomplete link markup; the excerpt-length helper ignores its length. `local_debug()` now returns escaped JSON inside a preformatted wrapper without printing directly. This changes its diagnostic format from the previous dump behavior.

## Current quality results

Local checks after cleanup:

| Check | Result |
| --- | --- |
| WordPress standards | Pass: zero errors and zero warnings |
| Standards baseline gate | Pass: zero findings; baseline is empty |
| PHPCompatibilityWP targeting PHP 8.3 | Pass: zero static findings |
| Additional PHPCompatibilityWP scan targeting PHP 8.3–8.5 | Pass: zero static findings in runtime and development code |
| Quality-gate regression tests | Pass: all nine |
| Project PHP lint | Pass on PHP 8.5.11 |
| `js/map.js` syntax | Pass with `node --check` |
| Release ZIP verification | Pass: 14 runtime files, no development files |
| WordPress 7.1.2 / PHP 8.3 activation and browser rendering | Not run locally |
| WordPress 7.1.2 / PHP 8.5 activation and Ubuntu PHP-FPM | Not run locally |

Formatting, documentation, translation domains, parameter usage, assignment placement, and map enqueue warnings have been corrected. The excerpt filter accepts no hook arguments because it replaces the suffix; the unregistered deadline helper no longer accepts an unused content parameter. The original scan had 2,321 standards findings; the current count is zero. A clean standards scan does not resolve the manual runtime findings above.

Both baselines are now empty after the clean scans. Any new finding fails the gate. Baselines are keyed by file, sniff, severity, and message; line shifts do not invalidate them. Do not regenerate them merely to accept regressions.

Run `composer check` for regression tests and both gates. Since it stops at a failing standards gate, run `composer check:compatibility` separately when needed. Full reports are written to `build/standards-report.json` and `build/compatibility-report.json`. Fixable findings require diff review; generic function names, escaping, translation domains, and runtime behavior also need manual attention.

## GitHub CI and delivery

The CI workflow configures PHP 8.3 quality checks and a separate WordPress 7.1.2/MySQL activation job. The smoke test verifies activation, renders `clearboth` and `email`, checks callable registration for all five retained tags, and checks that the eleven retired tags are absent. It no longer expects widget registrations.

Smoke coverage does not establish iframe/image/map rendering, external-service behavior, theme compatibility, or multisite compatibility. CI has not been executed as part of this local update; local standards and compatibility gates pass.

`composer package` uses WP-CLI's Composer-installed `dist-archive` command and verifies the ZIP against remaining runtime files. Packaging needs WP-CLI and PHP's ZIP extension, but no WordPress database. Reports, tests, development dependencies, scratch files, and metadata are excluded. The archive extracts into `clir-widgets-bundle`.

The release workflow runs CI and creates a draft GitHub release from the checked commit, using the plugin header version (currently `0.1.0`). Publishing and installation remain manual; increment the header before a new release. No server deployment or account-level branch protection was configured. Validate the retained features on staging and keep the preceding plugin archive for rollback.

## Next cleanup steps

1. Keep both baselines empty and reject new standards or compatibility findings.
2. Establish usage of the remaining helpers, People post type, excerpt filter, and TinyMCE override; remove confirmed unused paths.
3. Fix retained shortcode escaping and image input handling.
4. Repair or replace the DLF map integration and test it on HTTPS staging.
5. Add PHP 8.5 CI coverage alongside PHP 8.3, then test WordPress 7.1.2 with Ubuntu 26.04/PHP 8.5 and affected templates across the network before releasing.
