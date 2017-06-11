---
title: Constructing Pages
horizontal: true
styles:
  - background: "#60b3ab"
  - background: "#28878d"
  - background: "#11737c"
  - background: "#478c87"
  - background: "#71a193"
  - background: "#1c737d"
  - background: "#760b01"
  - background: "#980b01"
  - background: "#b20312"
  - background: "#d35837"
  - background: "#4c1405"
---

## Constructing Pages

---

The page-structure used in Fullpage is essentially the same as normally in Grav, with a few notable exceptions: Any horizontal rule, `---` in Markdown and `<hr />` in HTML, is treated as a _thematic break_, as it is defined in HTML5. This means that if you separate content with a horizontal rule within a page, the plugin treats this as a new section. This is equivalent to using child-pages for new sections, which work recursively: You can have as many pages below the root-page as you want, each of them will be treated as a section. Further, these methods can be mixed by some pages using horizontal rules, and some not.

---

### Nomenclature: What are sections and slides

With fullPage.js there is a distinction between sections and slides. Sections are single fullscreen pages listed vertically, and slides are single fullscreen pages listed horizontally. That is, if a page contains slides these are navigated horizontally rather than vertically. In the plugin, you define this by setting `horizontal: true` in the page's FrontMatter, which treats all content within it as slides.

---

### Example structure:

```
/user/pages/book
├── fullpage.md
├── 01.down-the-rabbit-hole
│   └── default.md
├── 02.advice-from-a-caterpillar
│   └── default.md
├── 03.were-all-mad-here
│   └── default.md
├── 04.a-mad-tea-party
│   └── default.md
├── 05.the-queens-crocquet-ground
│   └── default.md
├── 06.postscript
└───└── default.md
```

---

As seen in this example structure, only the initial page uses the `fullpage.html.twig`-template. The template used for child-pages is irrelevant, as only the content of these pages are processed. The plugin defines the `fullpage.html.twig`-template, but you can override it through your theme.