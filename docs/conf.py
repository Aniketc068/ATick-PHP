# Sphinx configuration for the ATick for PHP documentation site.
# Build locally:   pip install -r docs/requirements.txt && sphinx-build -b html docs docs/_build

project = "ATick for PHP"
author = "Axonate Tech"
copyright = "2026, Axonate Tech"
release = "1.0.6"
version = "1.0.6"

extensions = [
    "myst_parser",
    "sphinx_copybutton",
    "sphinx_design",
    "sphinxext.opengraph",
    "sphinx_sitemap",
]

html_baseurl = "https://atick-php.readthedocs.io/en/latest/"
sitemap_url_scheme = "{link}"

_SEO_DESCRIPTION = (
    "ATick for PHP is a standalone library for PDF digital signatures — PAdES (B-B/B-T/B-LT/B-LTA) "
    "and CMS signing with a PFX/PEM file, deferred / remote-key (eSign / HSM / token) signing, "
    "RFC-3161 timestamps, long-term validation, and a green-tick verified-signature appearance that "
    "Adobe shows as valid. Bundled engine via PHP FFI, cross-platform, composer require aniketc068/atick."
)
_SEO_KEYWORDS = (
    "PDF digital signature PHP, sign PDF PHP, PAdES, CAdES, CMS, eSign, LTV, "
    "RFC-3161 timestamp, Adobe valid signature, green tick signature, Composer PDF signing, Laravel PDF sign"
)
html_meta = {"description": _SEO_DESCRIPTION, "keywords": _SEO_KEYWORDS}

ogp_site_url = html_baseurl
ogp_site_name = "ATick for PHP — PDF digital-signature library"
ogp_description_length = 300
ogp_type = "website"
ogp_image = "https://atick-php.readthedocs.io/en/latest/_static/atick_logo.png"
ogp_enable_meta_description = True
ogp_custom_meta_tags = ['<meta name="twitter:card" content="summary_large_image" />']

source_suffix = {".md": "markdown", ".rst": "restructuredtext"}
master_doc = "index"
exclude_patterns = ["_build", "Thumbs.db", ".DS_Store"]
myst_enable_extensions = ["colon_fence", "deflist", "tasklist"]
myst_heading_anchors = 3

html_theme = "pydata_sphinx_theme"
html_title = "ATick for PHP Docs"
html_static_path = ["_static"]
html_extra_path = ["_extra"]
html_css_files = ["custom.css"]
html_favicon = "_static/favicon.png"
html_show_sourcelink = False
pygments_style = "friendly"
pygments_dark_style = "monokai"

html_theme_options = {
    "logo": {"image_light": "_static/atick_logo.png", "image_dark": "_static/atick_logo.png", "text": "ATick for PHP"},
    "navbar_start": ["navbar-logo"],
    "navbar_center": ["navbar-nav"],
    "navbar_end": ["theme-switcher", "navbar-icon-links"],
    "navbar_persistent": ["search-button"],
    "secondary_sidebar_items": ["page-toc"],
    "show_prev_next": True,
    "show_nav_level": 1,
    "navigation_depth": 3,
    "header_links_before_dropdown": 6,
    "pygments_light_style": "friendly",
    "pygments_dark_style": "github-dark",
    "icon_links": [
        {"name": "Packagist", "url": "https://packagist.org/packages/aniketc068/atick", "icon": "fa-solid fa-cube"},
        {"name": "GitHub", "url": "https://github.com/Aniketc068/ATick-PHP", "icon": "fa-brands fa-github"},
    ],
    "footer_start": ["copyright"],
    "footer_end": [],
}
html_sidebars = {"index": [], "**": ["sidebar-nav-bs"]}
html_context = {"default_mode": "light"}
