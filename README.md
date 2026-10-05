# CLIR Widgets Bundle

A bundle of WordPress widgets for CLIR + DLF sites.

## Development Setup

Use DDEV for a disposable local WordPress installation. Install a supported
[Docker provider](https://docs.ddev.com/en/stable/users/install/docker-installation/)
and [DDEV](https://docs.ddev.com/en/stable/users/install/ddev-installation/), then
start Docker. DDEV supplies PHP, Composer, WP-CLI, and the database; host PHP and
WP-CLI are optional for the integration tests.

### Configure DDEV and install WordPress

Run these commands from the repository root. They configure PHP 8.5 and MySQL 8.0,
matching one CI target, and pin WordPress to 7.1.2. WordPress and uploads live in
`temp/wordpress/`, which is already ignored by Git and excluded from the ZIP.
The repository is mounted at `/var/www/html` inside DDEV.
The project command in `.ddev/commands/web/wp` runs `/usr/local/bin/wp` explicitly:
DDEV otherwise finds Composer's framework-only `vendor/bin/wp` first, which lacks
`core` and other installation commands. Keep that project command in your checkout.
Its `ExecRaw: true` annotation preserves quoted arguments such as site titles;
`MutagenSync: true` synchronizes files before and after WP-CLI runs.

```sh
mkdir -p temp/wordpress
ddev config --project-name=clir-widgets-test --project-type=wordpress \
  --docroot=temp/wordpress --php-version=8.5 --database=mysql:8.0 \
  --web-working-dir=/var/www/html
ddev start
ddev wp core download --version=7.1.2 --path=/var/www/html/temp/wordpress
ddev wp core install --path=/var/www/html/temp/wordpress \
  --url=https://clir-widgets-test.ddev.site --title='CLIR local tests' \
  --admin_user=ci --admin_password=clir-local-test-only \
  --admin_email=ci@example.org --skip-email
ddev launch wp-admin/
```

DDEV manages the local database connection in the WordPress configuration.
The sample login is `ci` / `clir-local-test-only`, for this disposable installation.
These provisioning steps follow the [DDEV WordPress quickstart](https://docs.ddev.com/en/stable/users/quickstart/#wordpress).

If `ddev wp` reports that `core` is not registered, confirm the project command
above is present. You can also bypass command lookup directly:

```sh
ddev exec /usr/local/bin/wp core download --version=7.1.2 \
  --path=/var/www/html/temp/wordpress
```

### Build, install, and test the plugin

Install the release ZIP so local integration tests exercise the same distribution
as GitHub Actions. Run these commands again after changing plugin code; the
installed ZIP is a copy and does not update automatically.

```sh
ddev composer install
ddev composer check
ddev composer package
ddev wp plugin install /var/www/html/build/clir-widgets-bundle.zip \
  --force --activate --path=/var/www/html/temp/wordpress
ddev exec env CLIR_TEST_ENV=1 composer test:wordpress -- \
  --path=/var/www/html/temp/wordpress
```

The tests require a disposable database: they create and delete posts and uploads.
`CLIR_TEST_ENV=1` explicitly enables them. DDEV configuration under `.ddev/` is
excluded from the release ZIP. Keep generated WordPress files and local credentials
out of Git. The setup requires no Gulp, Node.js, or Python build step.

### Run multisite tests

After the single-site checks, convert this disposable installation to a
subdirectory network and activate the plugin for the network:

```sh
ddev wp plugin deactivate clir-widgets-bundle --path=/var/www/html/temp/wordpress
ddev wp core multisite-convert --title='CLIR local test network' \
  --path=/var/www/html/temp/wordpress
ddev wp plugin activate clir-widgets-bundle --network \
  --path=/var/www/html/temp/wordpress
ddev exec env CLIR_TEST_ENV=1 composer test:multisite -- \
  --path=/var/www/html/temp/wordpress
```

The suite creates two temporary subsites, checks shortcode/excerpt rendering and
attachment isolation, then deletes them. Run the conversion once; on subsequent
runs, leave the plugin network activated. After rebuilding the ZIP, reinstall it
with `--force` and use `plugin activate --network` before rerunning network tests.

### Check both PHP versions

Switch DDEV's PHP version and rerun the checks. This changes the container's PHP,
not the host's PHP or Composer's configured platform value:

```sh
ddev config --php-version=8.3
ddev restart
ddev composer check
ddev exec env CLIR_TEST_ENV=1 composer test:multisite -- \
  --path=/var/www/html/temp/wordpress

# Return to the default local version.
ddev config --php-version=8.5
ddev restart
```

This example assumes the installation has already been converted to multisite.
Use `test:wordpress` instead if it is still single-site. Version options are
documented in [DDEV configuration](https://docs.ddev.com/en/stable/users/configuration/config/).

### Run browser checks

Generate the browser fixture inside DDEV, then run the browser test on the host.
The generated files are shared through `build/browser/`. The existing browser
runner uses host PHP and Chrome and starts its own server on `127.0.0.1:8081`;
leave that port free. It does not need a connection to DDEV's database.

```sh
ddev exec env CLIR_TEST_ENV=1 composer test:browser-fixture -- \
  --path=/var/www/html/temp/wordpress

# macOS: requires host PHP and Google Chrome.
CHROME_BIN='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' \
  php tests/browser.php

# Linux: use Chrome's executable on PATH.
CHROME_BIN=google-chrome php tests/browser.php
```

Run the browser command for your operating system. These checks cover a controlled
page with local image/iframe assets; install the actual DLF theme separately to
review production layouts. Logs and rendered HTML are saved in `build/browser/`.

### Stop and reset

```sh
ddev stop
```

To rebuild the disposable installation from scratch, `ddev delete` removes this
project's containers and database (with a database snapshot by default). After
confirming you are in this test repository, remove only `temp/wordpress/` and
repeat the setup steps. Deleting the DDEV project does not remove those files.

### GitHub Actions

Pushes and pull requests run the existing workflow on PHP 8.3 and 8.5 with MySQL
8.0 and WordPress 7.1.2. CI installs the ZIP, runs the same integration suites,
generates the browser fixture, and checks multisite. It provisions its own services
and does not require DDEV. Local DDEV setup remains a separate developer step.

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
on PHP 8.3 and 8.5. Integration checks cover shortcode parsing, real media,
excerpts, network activation, two subsites, and a controlled headless Chrome page.
It uploads a plugin ZIP, browser results, and full static reports. Manually dispatch
**Prepare draft plugin release** to rerun checks and attach the tested ZIP to a draft
release. The plugin header version determines the tag; increment it before a new
release. Publish the draft and install on staging manually. Server deployment needs
a destination and credentials; it is not configured here.

### WordPress integration tests

Use a **disposable** WordPress installation with this plugin activated. These
tests create posts, uploads, and subsites, then clean them up. They require PHP's
GD extension, the full WP-CLI bundle, and explicit `CLIR_TEST_ENV=1`. They convert
unsuppressed PHP warnings and deprecations to exceptions during test execution.
`composer test` remains the database-free baseline-checker suite.

```sh
CLIR_TEST_ENV=1 composer test:wordpress -- --path=/path/to/disposable-wordpress

# On a disposable subdirectory network with the plugin network activated:
CLIR_TEST_ENV=1 composer test:multisite -- --path=/path/to/disposable-network

# Generate actual shortcode HTML, then check it in headless Chrome:
CLIR_TEST_ENV=1 composer test:browser-fixture -- --path=/path/to/disposable-wordpress
CHROME_BIN=google-chrome composer test:browser
```

The Composer runner uses the installed WP-CLI bundle rather than the development
framework under `vendor/bin`. Set `WP_CLI_BIN` if the bundle is not on `PATH`.
Set `CHROME_BIN` to Chrome's executable path on macOS or other systems. Browser
checks start a temporary server on `127.0.0.1:8081` and use an isolated profile;
results and logs are saved under `build/browser/`. They verify image loading and
sizing, caption text, iframe loading/dimensions, and email links. Actual DLF theme
appearance and production pages still require staging review.

## Server installation and updates

Deploy the `clir-widgets-bundle.zip` asset from a specific GitHub release. Use the
same ZIP on staging and production after reviewing affected pages on staging.
The existing release workflow prepares a draft release after CI passes; server
deployment is still manual. Publish the reviewed release before distributing it.

Run server commands over SSH as the account permitted to update WordPress plugin
files. The server needs the full WP-CLI bundle and working WordPress/database
access; the backup example also uses the `zip` command. CI currently tests
WordPress 7.1.2 on PHP 8.3 and 8.5. Confirm the server's CLI and web PHP versions
are appropriate before rollout. Composer, DDEV, and development dependencies are
not needed on the server to install the release ZIP.

For multisite, replace plugin files once per WordPress installation. Record
whether the plugin is network activated or active only on selected subsites, and
preserve that scope. Subsites share the installed plugin directory.

### Transfer and back up

Download the release asset, then upload it to the target server. Replace the
example SSH account, host, and local download path with your own:

```sh
# Run on your workstation; transfer to staging first.
scp /path/to/downloaded/clir-widgets-bundle.zip deploy@wordpress.clir.org:~/
ssh deploy@wordpress.clir.org
```

The following commands run in that server SSH session. Adjust the WordPress path
and release identifier to match the installation and downloaded release. Keep
backups outside the web document root:

```sh
CLIR_WP_PATH=/var/www/wordpress.clir.org
CLIR_RELEASE=v2.0.0
CLIR_RELEASE_DIR="$HOME/clir-plugin-releases/$CLIR_RELEASE"
mkdir -p "$CLIR_RELEASE_DIR"
mv "$HOME/clir-widgets-bundle.zip" "$CLIR_RELEASE_DIR/clir-widgets-bundle.zip"

# For an update, record the current plugin version and activation status.
wp plugin get clir-widgets-bundle --fields=name,status,version \
  --path="$CLIR_WP_PATH" > "$CLIR_RELEASE_DIR/before.txt"

# Archive the complete currently installed plugin, including retired files.
(cd "$CLIR_WP_PATH/wp-content/plugins" && \
  zip -r "$CLIR_RELEASE_DIR/previous.zip" clir-widgets-bundle)
```

For a first installation, skip the existing-plugin status and archive commands.
For per-site activation in multisite, also record `plugin get` output with
`--url=https://the-subsite.example` for each affected subsite. These examples assume
the standard `wp-content/plugins` location; adapt the backup path if customized.
Use a new release directory for each deployment so previous backups are retained.

### Install the release ZIP

```sh
wp plugin install "$CLIR_RELEASE_DIR/clir-widgets-bundle.zip" \
  --force --skip-plugins=clir-widgets-bundle --path="$CLIR_WP_PATH"
```

`--force` replaces the installed plugin files. `--skip-plugins` avoids loading this
plugin in the installation command; it does not change activation settings.
See the [WP-CLI installation reference](https://developer.wordpress.org/cli/commands/plugin/install/).
The replacement writes to the live plugin directory, so use an appropriate
deployment window. Confirm the intended activation state afterward.

If this is a first installation, or activation needs restoring, run only the
command matching the intended scope:

```sh
# Single site, or one selected subsite in a network.
wp plugin activate clir-widgets-bundle --path="$CLIR_WP_PATH" \
  --url=https://the-site.example

# Entire network: use only when network activation is intended.
wp plugin activate clir-widgets-bundle --network --path="$CLIR_WP_PATH"
```

### Verify the deployment

```sh
wp plugin get clir-widgets-bundle --fields=name,status,version \
  --path="$CLIR_WP_PATH"
```

Confirm the version matches the release and the activation scope matches the
recorded state. For per-site activation, verify each affected subsite with
`--url`. Review representative pages using `image_frame`, `iframe`, `email`, and
automatic excerpts, and inspect PHP logs. Purge affected page/CDN caches through
the site's normal process. For this cleanup release, also replace the retired
`[clir_map]` reference on the DLF Map page (post 14088).

The integration suites create and delete posts, uploads, and subsites. Run them
on disposable test installations; production verification should use existing
pages and read-only checks.

### Roll back

In the same SSH session, reinstall the saved pre-deployment ZIP:

```sh
wp plugin install "$CLIR_RELEASE_DIR/previous.zip" \
  --force --skip-plugins=clir-widgets-bundle --path="$CLIR_WP_PATH"
wp plugin get clir-widgets-bundle --fields=name,status,version \
  --path="$CLIR_WP_PATH"
```

Restore the recorded activation scope if necessary, purge affected caches, and
check pages and logs again. This restores plugin code only; it does not undo
content, option, or database changes. Keep the pre-deployment archive until the
new release has been verified across the network.

## Inventory shortcode usage before cleanup

The read-more customization runs on `get_the_excerpt` using the requested post's
ID. It escapes the link URL and translated label, and replaces only the default
automatic-excerpt suffix. Manual excerpts, short excerpts, and other custom
suffixes are preserved. Themes that call `wp_trim_excerpt()` directly bypass this
customization; check active templates before deployment.

Retained shortcodes escape URLs, attributes, and caption text. Iframes accept
HTTP/HTTPS URLs and dimensions of 1–10,000 pixels or 1–100%; invalid dimensions
fall back to 800 by 600. Image dimensions accept pixels only, and invalid values
are omitted. `image_frame` resolves registered attachment thumbnails, falling
back to the original image URL; captions display plain text. Review existing
caption markup and image sizing on staging. Missing or invalid image/email
content produces empty output.

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
