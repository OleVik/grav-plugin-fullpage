---
title: Configuration
horizontal: true
styles:
  - background: "#a12e27"
    color: white
  - background: "#643325"
  - background: "#a75830"
  - background: "#bb7b3b"
  - background: "#ce914e"
---

## Configuration

---

As with other plugins, the main configuration is changed by copying `user/plugins/fullpage/fullpage.yaml` into `user/config/plugins/fullpage.yaml` and editing it. Configuration can of course also be edited via the plugin's page in the Admin-plugin.

---

### Plugin configuration

In addition to options for the fullPage.js-library, you can define the order of the of how the pages are rendered through `order.by` and `order.dir`, and whether to use the plugin's built-in CSS and JS with `builtin_css` and `builtin_js`. Further, you can define inline-styles for each section or slide through `styles`. This last property is a list of CSS-properties that will be applied to the page, or pages if using horizontal rules, in the order they appear.

---

### Example styles

```
styles:
  - background: "#F0A202"
  - background: "#F18805"
  - background: "#D95D39"
  - background: "#202C59"
  - background: "#581F18"
```

Styles are defined as `property: value` and processed by the plugin. If the amount of pages exceed the amount of styles, they will be reused in the order they are defined. If the `background`-property is defined, but `color` is not, the plugin tries to estimate a suitable text-color to apply. The equations available to estimate this color is either `50` or `YIQ`, set by `color_function`.

---

### Page-specific configuration

Any configuration set in `fullpage.yaml` can be overridden through a page's FrontMatter, like this:

```
---
title: Alice’s Adventures in Wonderland
fullpage:
  order:
    by: date
    dir: desc
  options:
    navigation: true
---
```

---

### Configuring fullPage.js

All options available to the fullPage.js-library can be configured, see its [documentation for available options](https://github.com/alvarotrigo/fullPage.js#options). You can of course also style the plugin using your theme's CSS-files, by targeting the `.fullpage`-selector which wraps around all of the plugin's content.

---

### Creating a menu

The plugin makes a `fullpage_menu`-variable available through Twig on pages which use the fullscreen-template, which can be used to construct an overall menu of pages. It is an array with anchors and titles for each page, and a list of them with links to sections can be constructed like this:

```
<ul id="menu" class="menu">
{% for anchor, title in fullpage_menu %}
  <li>
    <a href="#{{ anchor }}">{{ title }}</a>
  </li>
{% endfor %}
</ul>
```