# CLIR Widgets Bundle

A bundle of WordPress widgets for CLIR + DLF sites.

## Development Setup

Use a local WordPress installation such as MAMP, and link this repository into
its `wp-content/plugins` directory:

```
$ cd /Applications/MAMP/htdocs/wordpress/wp-content/plugins
$ ln -s ~/projects/clir-widgets-bundle
```

Then activate the plugin in the WordPress admin panel.


Development requires PHP 8.3+, Composer 2, and WP-CLI for packaging. PHP's ZIP
extension must be enabled. No Gulp, Node.js, or Python build step is required.

## Review and GitHub workflows

See [the PHP 8.3 and WordPress 7.1.2 audit](docs/compatibility-audit.md) for bugs,
style issues, removal candidates, test limits, and the cleanup order.
The plugin ships its JavaScript directly and does not compile frontend assets.

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
