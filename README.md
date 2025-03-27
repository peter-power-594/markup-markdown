# WP Markup Markdown
[![WP compatibility](https://plugintests.com/plugins/wporg/markup-markdown/wp-badge.svg?ver=3.9.1)](https://plugintests.com/plugins/wporg/markup-markdown/latest)
[![PHP compatibility](https://plugintests.com/plugins/wporg/markup-markdown/php-badge.svg?ver=3.9.1)](https://plugintests.com/plugins/wporg/markup-markdown/latest) 
[![Code Climate Quality](https://codeclimate.com/github/peter-power-594/markup-markdown.png)](https://codeclimate.com/github/peter-power-594/markup-markdown)  
The [Markup Markdown](https://wordpress.org/plugins/markup-markdown/) WordPress Plugin !  
Write your blog posts and pages directly in Markdown from the WordPress's admin screen 

Issues are not enabled, only sharing the source code.

Need help? Ask me and share the community with the Wordpress forum:  
https://wordpress.org/support/plugin/markup-markdown/

Business or commercial use? Request professional support here:  
[https://www.markup-markdown.com/contact/](https://www.markup-markdown.com/contact/)

Thank you for your understanding, and stay tune for the markdown journey!  
It's just the beginning, and if you can feel free to do a gesture to support me:

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/peterpower594)

Thank you very much in advance, and enjoy the rideeeeee~

## Quick ChangeLog

- v3.14: Adding responsive attributes for assets coming from the media library and disable media iframe converter for text links
- v3.13: Adding a new filter to toggle on/off Gutenberg in the admin screen and new autoplug for Code Snippets
- v3.12: Adding support for LaTeX via Katex or MathJax
- v3.11: Bug fix with code block fences, compatible with plugins like Prismatic and new autoplug for CodeMirror Blocks (Syntax Highlighter)
- v3.10: Adding compatibility with the BuddyPress and BuddyPress Docs plugins via an autoplug, possible to disable autoplugs from the settings panel
- v3.9: Adding compatibility with the bbPress plugin via an autoplug 
- v3.8: Adding compatibility with the O2 plugin via an autoplug, support for using # signs as ordered list
- v3.7: Adding support for selective heading levels
- v3.6: Performance improvements with spellchecker and suggestions
- v3.5: Adding support for right-to-left alphabets like Arabic, Hebrew, or Persian
- v3.4: Adding support for categories, tags and taxonomies description field (Woocommerce and REST API compatible)
- v3.3: Support for multiple html attributes, compatibility with acf_form added for the frontend, basic compatibility with block styles
- v3.2: Support to enable markdown only for custom fields
- v3.1: Side preview panel fixed
- v3.0: Choose and sort the default toolbar buttons
- v2.6: Sticky toolbar with the editor
- v2.6: Possible to disable OP Cache
- v2.5: Video playlist support added
- v2.3: New beta interface
- v2.2: Possible to enable or disable specific addons
- v2.1: Gallery shortcode support
- v2.0: ACF markdown field support
- v1.9: Multilingual spell checking support
- v1.7: Disable markdown for specific custom post type
- v1.4: Extra markdown syntax added
- v1.3: Static cache files with OP Cache enabled by default
- v1.2: Autoconvert Youtube & Vimeo links to iframes
- v1.1: Support with lightbox and masonry for the gallery layout


## Developers

### Install

Bundle using Grunt. You need nodejs first then install the dependencies:

```shell
npm install --save-dev
```

### Build

You can easily trigger a build from the source code:

```shell
grunt.cmd
```

### Testing

Checkout this repository as "markup-markdown" inside your local Wordpress plugins directory.

Then you can easily test live modifications by running:

```shell
grunt.cmd dev
```
