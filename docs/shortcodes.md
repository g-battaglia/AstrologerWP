# Shortcodes

Astrologer API ships **no legacy shortcodes**. Every user-facing rendering is implemented as a first-class Gutenberg block registered through `block.json` metadata. This decision keeps markup WYSIWYG-editable in the editor, lets the Interactivity API wire client state automatically, and removes the `[astrologer_*]` string-parsing surface that used to complicate sanitisation and escaping.

## Why blocks instead of shortcodes

- **Editor parity** — block previews use the same render path as the frontend; authors see the chart the visitor will see.
- **Attribute schema** — block attributes are strongly typed in `block.json` and validated by Gutenberg. Shortcodes, by contrast, require hand-rolled attribute coercion.
- **Interactivity API** — forms, charts and moon-phase widgets share one reactive store per namespace (`astrologer/birth-form`, `astrologer/chart-display`, …), which would be noisy to reproduce over shortcodes.
- **i18n** — block titles and descriptions are extracted automatically by `wp i18n make-pot`, so translations stay in sync with the UI.

## Using blocks from Classic content

If you still maintain Classic editor pages, insert a block programmatically with the built-in WP helpers:

```php
echo do_blocks(
    '<!-- wp:astrologer-api/birth-form {"uiLevel":"basic"} /-->'
);
```

`do_blocks()` serves the same HTML the Gutenberg editor produces, so the rendered form keeps all its Interactivity bindings.

## Migrating from a shortcode-based setup

Earlier prototypes exposed a `[astrologer_birth_form]` shortcode. It has been removed. If you need to keep old markup alive during a migration window, wire a thin adapter in your theme's `functions.php`:

```php
add_shortcode( 'astrologer_birth_form', static function ( array $atts ): string {
    $json = wp_json_encode( (object) $atts ) ?: '{}';
    return do_blocks( '<!-- wp:astrologer-api/birth-form ' . $json . ' /-->' );
} );
```

The adapter forwards the shortcode attributes to the block JSON payload and renders the block server-side. Once content has been migrated, remove the adapter to drop the legacy dependency.

See [blocks.md](blocks.md) for the full catalogue of blocks and their attributes.
