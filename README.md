# CLIR Widgets Bundle

A bundle of WordPress widgets for CLIR + DLF sites.

## Development Setup

I'm assuming you're using [MAMP](https://www.mamp.info). If you're doing
something else, you'll need to update the `dev_url` in `gulpfile.js` to
whatever your connection is (and probably `port` too).

```
$ cd /Applications/MAMP/htdocs/wordpress/wp-content/plugins
$ ln -s ~/projects/clir-widgets-bundle
```

Then activate the theme in the Wordpress admin panel.


Install the `node` dependencies:

```
$ cd ~/projects/clir-widgets-bundle
$ npm install
```

Open the project in your favorite editor, then start the `gulp` proxy do browser
refreshes when you save a file.

```
$ cd ~/projects/clir-widgets-bundle
$ gulp
```
