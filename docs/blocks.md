# Blocks

Astrologer API registers 22 blocks under the `astrologer-api/*` namespace. Every block lives in its own directory under `blocks/` with a `block.json`, a server-side `render.php`, an `edit.tsx` for the editor, and — where needed — a `view.ts` that provides the Interactivity store.

The blocks form two families: **input forms** that collect birth data and **chart renderers** that display the results. Most chart renderers read from a sibling form via a `sourceBlockId` attribute so a single birth form can feed multiple visualisations on the same page.

## Form blocks

### `astrologer-api/birth-form`
Collects birth data for a natal chart. Attributes: `uiLevel` (`basic`/`advanced`/`expert`), `preset`, `showSaveOption`, `targetBlockId`, `redirectAfterSubmit`. Emits an event consumed by any `natal-chart`, `positions-table`, `aspects-table`, `elements-chart`, or `modalities-chart` sibling.

### `astrologer-api/synastry-form`
Two-subject form for relationship astrology. Adds `twoSubjectLayout` (`stacked`/`side-by-side`).

### `astrologer-api/transit-form`
Natal subject plus a separate transit datetime fieldset.

### `astrologer-api/composite-form`
Two-subject form for midpoint/Davison composite calculations.

### `astrologer-api/solar-return-form`
Natal subject plus a `returnYear` input.

### `astrologer-api/lunar-return-form`
Natal subject plus a target return datetime.

### `astrologer-api/now-form`
Location-only form; used to compute the chart for the current moment at the requested geography.

### `astrologer-api/compatibility-form`
Two-subject form that returns a numeric compatibility score instead of a chart.

## Chart blocks

### `astrologer-api/natal-chart`
Renders a natal wheel SVG, positions table, and aspect grid. Attributes: `sourceBlockId`, `showSvg`, `showPositions`, `showAspects`, `chartTheme`, `theme`.

### `astrologer-api/synastry-chart`
Bi-wheel SVG overlaying both subjects' charts plus cross-aspect grid.

### `astrologer-api/transit-chart`
Natal ring with transiting planets drawn over it.

### `astrologer-api/composite-chart`
Midpoint or Davison composite wheel.

### `astrologer-api/solar-return-chart`
Dual or single display of natal + solar return wheels.

### `astrologer-api/lunar-return-chart`
Dual or single display of natal + lunar return wheels.

### `astrologer-api/now-chart`
Live sky chart for the current moment; respects `refreshInterval` attribute.

## Data blocks

### `astrologer-api/moon-phase`
Self-contained widget for the current moon phase. Attributes: `displayMode`, `refreshInterval` (capped at 86400 seconds).

### `astrologer-api/positions-table`
Table of planetary positions. Derives data from a bound form block via `sourceBlockId`.

### `astrologer-api/aspects-table`
Table of aspects (planet A, planet B, aspect, orb).

### `astrologer-api/elements-chart`
Bar chart of fire/earth/air/water distribution.

### `astrologer-api/modalities-chart`
Bar chart of cardinal/fixed/mutable distribution.

### `astrologer-api/compatibility-score`
Score card displaying the numeric output of a compatibility form.

### `astrologer-api/relationship-score`
Heuristic relationship score (Bolich-style), with breakdown into strength categories.

## Usage example

```html
<!-- wp:astrologer-api/birth-form {"uiLevel":"basic"} /-->
<!-- wp:astrologer-api/natal-chart {"showSvg":true,"showPositions":true,"showAspects":false} /-->
```

The natal-chart block discovers the sibling birth-form via the shared Interactivity namespace and re-renders when the form submits.
