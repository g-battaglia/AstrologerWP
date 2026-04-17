# F6 — Frontend Interactivity API

> **Theme:** Frontend completamente senza React runtime. Tutti i form e chart display guidati da stores Interactivity API. State machine per validazione, submit, rendering.
> **Effort:** L (6-8 giorni) — riducibile a M (4g) se Checkpoint F0.5 dà esito NOK.
> **Dipendenze:** F0.5 (spike validato), F3 (REST endpoints), F5 (template markup)

---

## Obiettivo

Zero bundle React nel frontend. Solo `@wordpress/interactivity` core + gli store dei block.

**7 form stores** + **1 chart display store** + **1 city autocomplete store** + **1 moon phase store**.

Ogni store implementa state machine:
```
idle → validating → submitting → success | error
              ↑            ↓
              ←──── retry ──
```

---

## Prerequisiti

- F0.5 spike passato (o fallback documentato).
- F5 blocks renderizzano markup con attributi `data-wp-*` corretti.
- F3 REST endpoints con nonce funzionanti.

---

## Tasks

### F6.1 — Struttura directory interactivity-src/

```
interactivity-src/
├── stores/
│   ├── birth-form.ts
│   ├── synastry-form.ts
│   ├── transit-form.ts
│   ├── composite-form.ts
│   ├── solar-return-form.ts
│   ├── lunar-return-form.ts
│   ├── now-form.ts
│   ├── compatibility-form.ts
│   ├── chart-display.ts
│   ├── city-autocomplete.ts
│   └── moon-phase.ts
├── lib/
│   ├── api.ts          # wrap apiFetch con nonce + rate limit header parse
│   ├── validation.ts   # zod-lite schema per ogni form
│   ├── svg-processor.ts # extract <g>/<title>/<desc> per tooltips
│   └── bus.ts          # event bus cross-store (form → chart-display)
└── index.ts            # re-exports (rimosso dal build, solo per types)
```

### F6.2 — Bus event cross-store

**File:** `interactivity-src/lib/bus.ts`

```ts
type ChartPayload = { blockId: string; svg: string; data: unknown };
type BusEvents = {
  'chart:rendered': ChartPayload;
  'form:submit:start': { blockId: string; formType: string };
  'form:submit:end': { blockId: string; formType: string; success: boolean };
};
class Bus {
  private listeners = new Map<string, Set<Function>>();
  on<K extends keyof BusEvents>(e: K, h: (p: BusEvents[K]) => void) { /* ... */ }
  emit<K extends keyof BusEvents>(e: K, p: BusEvents[K]) { /* ... */ }
}
export const bus = new Bus();
```

Serve a disaccoppiare form store dal chart-display store: quando form ha successo emit `chart:rendered`, chart-display listener aggiorna innerHTML del proprio container `[data-astrologer-chart-id="{targetBlockId}"]`.

### F6.3 — Birth form store

**File:** `interactivity-src/stores/birth-form.ts`

```ts
import { store, getContext } from '@wordpress/interactivity';
import { apiFetch } from '../lib/api';
import { validateBirth } from '../lib/validation';
import { bus } from '../lib/bus';

type State = {
  status: 'idle' | 'validating' | 'submitting' | 'success' | 'error';
  fields: {
    name: string; year: number; month: number; day: number;
    hour: number; minute: number;
    city: string; nation: string; latitude: number; longitude: number; timezone: string;
  };
  citySuggestions: GeonameHit[];
  errors: Record<string, string>;
  remoteError: string;
};

const { state, actions } = store('astrologer/birth-form', {
  state: {
    status: 'idle',
    fields: { name: '', year: 1990, month: 1, day: 1, hour: 12, minute: 0, city: '', nation: '', latitude: 0, longitude: 0, timezone: 'UTC' },
    citySuggestions: [],
    errors: {},
    remoteError: '',
  } as State,
  actions: {
    *onCityInput(e: InputEvent) {
      const value = (e.target as HTMLInputElement).value;
      state.fields.city = value;
      if (value.length < 3) { state.citySuggestions = []; return; }
      yield new Promise(r => setTimeout(r, 300)); // debounce
      if (state.fields.city !== value) return; // stale
      const hits = yield apiFetch('/astrologer/v1/geonames/search?q=' + encodeURIComponent(value));
      state.citySuggestions = hits;
    },
    selectCity(e: Event) {
      const ctx = getContext<{ city: GeonameHit }>();
      state.fields.city = ctx.city.name;
      state.fields.nation = ctx.city.country;
      state.fields.latitude = ctx.city.lat;
      state.fields.longitude = ctx.city.lng;
      state.fields.timezone = ctx.city.timezone;
      state.citySuggestions = [];
    },
    *submit(e: SubmitEvent) {
      e.preventDefault();
      state.status = 'validating';
      state.errors = validateBirth(state.fields);
      if (Object.keys(state.errors).length > 0) { state.status = 'idle'; return; }
      state.status = 'submitting';
      const el = (e.currentTarget as HTMLFormElement);
      const blockId = el.dataset.blockId ?? '';
      bus.emit('form:submit:start', { blockId, formType: 'birth' });
      try {
        const { chart, svg, data } = yield apiFetch('/astrologer/v1/natal-chart', {
          method: 'POST',
          body: JSON.stringify({ subject: state.fields }),
        });
        bus.emit('chart:rendered', { blockId: el.dataset.targetBlock ?? '', svg, data });
        state.status = 'success';
      } catch (err: any) {
        state.remoteError = err.message;
        state.status = 'error';
      } finally {
        bus.emit('form:submit:end', { blockId, formType: 'birth', success: state.status === 'success' });
      }
    },
    reset() {
      Object.assign(state.fields, { name: '', year: 1990, /* ... */ });
      state.status = 'idle';
      state.errors = {};
      state.remoteError = '';
    },
  },
});
```

### F6.4 — Synastry/Transit/Composite/Return/Compatibility stores

Stessa struttura di `birth-form` ma con:
- `synastry-form`: `state.fields = { subject1: BirthFields, subject2: BirthFields }`.
- `transit-form`: `state.fields = { subject: BirthFields, transitDate: Partial<BirthFields> }` con toggle "use now".
- `composite-form`: simile a synastry.
- `solar-return-form` / `lunar-return-form`: subject + target year.
- `now-form`: `navigator.geolocation` opt-in con permission prompt + fallback manual lat/lng.
- `compatibility-form`: subject1 + subject2 minimal (nome + data + luogo).

Ogni store chiama il rispettivo endpoint REST.

### F6.5 — Chart display store

**File:** `interactivity-src/stores/chart-display.ts`

Listener su bus `chart:rendered` aggiorna:
- `state.svg` (sanitized lato client con DOMPurify? No — già sanitizzato server-side via `SvgSanitizer` in F2.7).
- `state.data` per tabelle posizioni/aspetti nello stesso block tree (se presenti).
- `state.status = 'success'`.

Markup template renderizzato in F5 ha `data-wp-bind--inner-html="state.svg"` + `data-wp-class--loading="state.status === 'loading'"`.

### F6.6 — City autocomplete store

**File:** `interactivity-src/stores/city-autocomplete.ts`

Decoupled dallo store del form, usa `getContext()` per isolare state per dropdown. Riutilizzato da tutti i form che hanno campo city.

Debounce 300ms. Cache in memory (Map) dei query degli ultimi 10 min.

### F6.7 — Moon phase store

**File:** `interactivity-src/stores/moon-phase.ts`

Per block `moon-phase`. Se attributo `mode === 'current'` → fetch `GET /moon-phase/current` on init. Se `mode === 'at-date'` → fetch al date change. Rendering di phase name + illumination % + next events list.

Se WP option `astrologer_daily_moon_phase_cache` contiene fresh data → skip fetch (pre-hydrated via data attribute da PHP render).

### F6.8 — API lib wrapper

**File:** `interactivity-src/lib/api.ts`

```ts
export async function apiFetch(path: string, options: RequestInit = {}): Promise<any> {
  const base = (window as any).astrologerFrontend?.restRoot ?? '/wp-json';
  const nonce = (window as any).astrologerFrontend?.nonce ?? '';
  const res = await fetch(base + path, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce,
      ...(options.headers ?? {}),
    },
    credentials: 'same-origin',
  });
  const remaining = res.headers.get('X-Astrologer-Rate-Remaining');
  if (remaining != null) bus.emit('rate:update' as any, { remaining: parseInt(remaining) });
  if (!res.ok) {
    const payload = await res.json().catch(() => ({ message: res.statusText }));
    throw new Error(payload.message || 'Request failed');
  }
  return res.json();
}
```

Wiring nonce da `AssetEnqueuer` (F6.10).

### F6.9 — Validation lib

**File:** `interactivity-src/lib/validation.ts`

Validazione shallow (no zod runtime per ridurre bundle):
```ts
export function validateBirth(f: BirthFields): Record<string, string> {
  const errors: Record<string, string> = {};
  if (!f.name.trim()) errors.name = 'Name is required';
  if (f.year < 1800 || f.year > 2100) errors.year = 'Year out of range';
  if (f.latitude === 0 && f.longitude === 0) errors.city = 'Select a city';
  // ...
  return errors;
}
```

Messaggi tradotti via `__()` da @wordpress/i18n caricato in window.

### F6.10 — AssetEnqueuer

**File:** `src/Frontend/AssetEnqueuer.php`

`Bootable`. Su `wp_enqueue_scripts`:
- Enqueue `@wordpress/interactivity` via core (`wp_interactivity` module).
- Localize `astrologerFrontend = { restRoot, nonce, uiLevel, capabilities, preset }`.
- **Non enqueue** React o wp-element nel frontend (verifica via Network tab).

### F6.11 — Test interactivity

**File:** `tests/Jest/interactivity/birth-form-store.test.ts` — mocka apiFetch, invoca `actions.submit`, expect bus emit + state transition.
**File:** `tests/e2e/natal-form-submit.spec.ts` — Playwright: apri pagina demo con birth-form + natal-chart, compila, submit, expect SVG appare + no React error in console + Network no React chunk.

---

## Criterio di demoable

1. **Network tab**: pagina frontend con 1 form + 1 chart mostra: `interactivity.min.js` + `view.js` del block + `store-birth-form.js`. **Zero** chunk React.
2. **Submit flow**: compilo form, submit → status cambia `idle → submitting → success` visibile via class toggle → SVG appare dentro chart display block.
3. **Validation**: submit vuoto → errors mostrati inline, no fetch.
4. **City autocomplete**: typing 3+ chars → dropdown suggestions → click → lat/lng/tz popolati.
5. **Rate limit**: submit 61 volte in un minuto → errore "Too many requests" dalla 61esima.

---

## Hooks introdotti

- Filter `astrologer_api/frontend_config` — modifica `astrologerFrontend` object localizzato.
- Action `astrologer_api/frontend_assets_enqueued` — dopo enqueue per add custom.

---

## Accessibilità

- Form campi: `aria-invalid="true"` se error, `aria-describedby` linked a `.error-message`.
- Submit button: `aria-busy="true"` durante submission, `aria-live="polite"` region per status changes.
- City autocomplete: combobox pattern (`role="combobox"`, `aria-expanded`, `aria-controls`, `aria-activedescendant`).
- Focus management: submit success → focus on chart SVG `<title>`.

---

## Fallback se checkpoint F0.5 era NOK

Se spike ha fallito, sostituire F6 con:
- `frontend-src/` React entry per i 7 form (simile agli admin tabs).
- Chart display ancora Interactivity (più semplici, display-only).
- Bundle +100KB ma DX molto più familiare.
- Effort ridotto da L (6-8g) a M (4g).
