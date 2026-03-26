/**
 * Gutenberg blocks for Astrologer API.
 *
 * Registers blocks in the WordPress editor.
 * Blocks render a placeholder in the editor and the React component on the frontend.
 */

(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { InspectorControls, useBlockProps } = wp.blockEditor;
  const { PanelBody, TextControl, NumberControl, ToggleControl } = wp.components;
  const { createElement: el, Fragment } = wp.element;

  /**
   * Common icon for astrological blocks.
   */
  const astroIcon = el('svg', {
    xmlns: 'http://www.w3.org/2000/svg',
    viewBox: '0 0 24 24',
    width: 24,
    height: 24,
  }, el('path', {
    d: 'M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm1-13h-2v6l5.25 3.15.75-1.23-4-2.42z',
    fill: 'currentColor',
  }));

  /**
   * Common attributes for birth data.
   */
  const birthDataAttributes = {
    name: { type: 'string', default: '' },
    year: { type: 'number', default: 1990 },
    month: { type: 'number', default: 1 },
    day: { type: 'number', default: 1 },
    hour: { type: 'number', default: 12 },
    minute: { type: 'number', default: 0 },
    latitude: { type: 'number', default: 41.9028 },
    longitude: { type: 'number', default: 12.4964 },
    timezone: { type: 'string', default: 'Europe/Rome' },
    city: { type: 'string', default: 'Rome' },
    nation: { type: 'string', default: 'IT' },
  };

  /**
   * Inspector controls for birth data.
   */
  function BirthDataInspector({ attributes, setAttributes }) {
    return el(Fragment, {},
      el(PanelBody, { title: 'Birth Data', initialOpen: true },
        el(TextControl, {
          label: 'Name',
          value: attributes.name,
          onChange: (value) => setAttributes({ name: value }),
        }),
        el(NumberControl, {
          label: 'Year',
          value: attributes.year,
          onChange: (value) => setAttributes({ year: parseInt(value, 10) }),
          min: 1900,
          max: 2100,
        }),
        el(NumberControl, {
          label: 'Month',
          value: attributes.month,
          onChange: (value) => setAttributes({ month: parseInt(value, 10) }),
          min: 1,
          max: 12,
        }),
        el(NumberControl, {
          label: 'Day',
          value: attributes.day,
          onChange: (value) => setAttributes({ day: parseInt(value, 10) }),
          min: 1,
          max: 31,
        }),
        el(NumberControl, {
          label: 'Hour',
          value: attributes.hour,
          onChange: (value) => setAttributes({ hour: parseInt(value, 10) }),
          min: 0,
          max: 23,
        }),
        el(NumberControl, {
          label: 'Minutes',
          value: attributes.minute,
          onChange: (value) => setAttributes({ minute: parseInt(value, 10) }),
          min: 0,
          max: 59,
        })
      ),
      el(PanelBody, { title: 'Location', initialOpen: false },
        el(TextControl, {
          label: 'City',
          value: attributes.city,
          onChange: (value) => setAttributes({ city: value }),
        }),
        el(TextControl, {
          label: 'Country (code)',
          value: attributes.nation,
          onChange: (value) => setAttributes({ nation: value }),
        }),
        el(NumberControl, {
          label: 'Latitude',
          value: attributes.latitude,
          onChange: (value) => setAttributes({ latitude: parseFloat(value) }),
          step: 0.0001,
        }),
        el(NumberControl, {
          label: 'Longitude',
          value: attributes.longitude,
          onChange: (value) => setAttributes({ longitude: parseFloat(value) }),
          step: 0.0001,
        }),
        el(TextControl, {
          label: 'Time Zone',
          value: attributes.timezone,
          onChange: (value) => setAttributes({ timezone: value }),
        })
      )
    );
  }

  // =========================================================================
  // BLOCK: Natal Chart
  // =========================================================================
  registerBlockType('astrologer-api/natal-chart', {
    title: 'Natal Chart',
    description: 'Displays the SVG graphic of the natal chart.',
    icon: astroIcon,
    category: 'widgets',
    attributes: birthDataAttributes,
    edit: function (props) {
      const blockProps = useBlockProps({
        className: 'astrologer-block-preview',
      });

      return el(Fragment, {},
        el(InspectorControls, {},
          el(BirthDataInspector, {
            attributes: props.attributes,
            setAttributes: props.setAttributes,
          })
        ),
        el('div', blockProps,
          el('div', {
            style: {
              padding: '20px',
              background: '#f0f0f0',
              border: '2px dashed #ccc',
              borderRadius: '8px',
              textAlign: 'center',
            },
          },
            el('span', { style: { fontSize: '24px' } }, '☿'),
            el('p', { style: { margin: '10px 0 0' } },
              'Natal Chart: ' + (props.attributes.name || 'Subject')
            ),
            el('small', {},
              props.attributes.day + '/' + props.attributes.month + '/' + props.attributes.year +
              ' at ' + props.attributes.hour + ':' + String(props.attributes.minute).padStart(2, '0')
            )
          )
        )
      );
    },
    save: function () {
      // Server-side rendering
      return null;
    },
  });

  // =========================================================================
  // BLOCK: Aspects Table
  // =========================================================================
  registerBlockType('astrologer-api/aspects-table', {
    title: 'Aspects Table',
    description: 'Displays the planetary aspects table.',
    icon: astroIcon,
    category: 'widgets',
    attributes: birthDataAttributes,
    edit: function (props) {
      const blockProps = useBlockProps({
        className: 'astrologer-block-preview',
      });

      return el(Fragment, {},
        el(InspectorControls, {},
          el(BirthDataInspector, {
            attributes: props.attributes,
            setAttributes: props.setAttributes,
          })
        ),
        el('div', blockProps,
          el('div', {
            style: {
              padding: '20px',
              background: '#f0f0f0',
              border: '2px dashed #ccc',
              borderRadius: '8px',
              textAlign: 'center',
            },
          },
            el('span', { style: { fontSize: '24px' } }, '⚹'),
            el('p', { style: { margin: '10px 0 0' } }, 'Planetary Aspects Table')
          )
        )
      );
    },
    save: function () {
      return null;
    },
  });

  // =========================================================================
  // BLOCK: Elements Chart
  // =========================================================================
  registerBlockType('astrologer-api/elements-chart', {
    title: 'Elements Chart',
    description: 'Displays the distribution of elements (Fire, Earth, Air, Water).',
    icon: astroIcon,
    category: 'widgets',
    attributes: birthDataAttributes,
    edit: function (props) {
      const blockProps = useBlockProps({
        className: 'astrologer-block-preview',
      });

      return el(Fragment, {},
        el(InspectorControls, {},
          el(BirthDataInspector, {
            attributes: props.attributes,
            setAttributes: props.setAttributes,
          })
        ),
        el('div', blockProps,
          el('div', {
            style: {
              padding: '20px',
              background: '#f0f0f0',
              border: '2px dashed #ccc',
              borderRadius: '8px',
              textAlign: 'center',
            },
          },
            el('span', { style: { fontSize: '24px' } }, '🔥💧🌍💨'),
            el('p', { style: { margin: '10px 0 0' } }, 'Elements Distribution')
          )
        )
      );
    },
    save: function () {
      return null;
    },
  });

  // =========================================================================
  // BLOCK: Modalities Chart
  // =========================================================================
  registerBlockType('astrologer-api/modalities-chart', {
    title: 'Modalities Chart',
    description: 'Displays the distribution of modalities (Cardinal, Fixed, Mutable).',
    icon: astroIcon,
    category: 'widgets',
    attributes: birthDataAttributes,
    edit: function (props) {
      const blockProps = useBlockProps({
        className: 'astrologer-block-preview',
      });

      return el(Fragment, {},
        el(InspectorControls, {},
          el(BirthDataInspector, {
            attributes: props.attributes,
            setAttributes: props.setAttributes,
          })
        ),
        el('div', blockProps,
          el('div', {
            style: {
              padding: '20px',
              background: '#f0f0f0',
              border: '2px dashed #ccc',
              borderRadius: '8px',
              textAlign: 'center',
            },
          },
            el('span', { style: { fontSize: '24px' } }, '♈♉♊'),
            el('p', { style: { margin: '10px 0 0' } }, 'Modalities Distribution')
          )
        )
      );
    },
    save: function () {
      return null;
    },
  });

  // =========================================================================
  // BLOCK: Full Birth Form
  // =========================================================================
  registerBlockType('astrologer-api/birth-form', {
    title: 'Natal Chart Form',
    description: 'Interactive form to enter birth data and display the full natal chart.',
    icon: astroIcon,
    category: 'widgets',
    attributes: {
      showChart: { type: 'boolean', default: true },
      showAspects: { type: 'boolean', default: true },
      showElements: { type: 'boolean', default: true },
      showModalities: { type: 'boolean', default: true },
    },
    edit: function (props) {
      const blockProps = useBlockProps({
        className: 'astrologer-block-preview',
      });

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Display Options', initialOpen: true },
            el(ToggleControl, {
              label: 'Show Chart',
              checked: props.attributes.showChart,
              onChange: (value) => props.setAttributes({ showChart: value }),
            }),
            el(ToggleControl, {
              label: 'Show Aspects Table',
              checked: props.attributes.showAspects,
              onChange: (value) => props.setAttributes({ showAspects: value }),
            }),
            el(ToggleControl, {
              label: 'Show Elements Chart',
              checked: props.attributes.showElements,
              onChange: (value) => props.setAttributes({ showElements: value }),
            }),
            el(ToggleControl, {
              label: 'Show Modalities Chart',
              checked: props.attributes.showModalities,
              onChange: (value) => props.setAttributes({ showModalities: value }),
            })
          )
        ),
        el('div', blockProps,
          el('div', {
            style: {
              padding: '20px',
              background: '#e8f4e8',
              border: '2px dashed #4caf50',
              borderRadius: '8px',
              textAlign: 'center',
            },
          },
            el('span', { style: { fontSize: '24px' } }, '📝'),
            el('p', { style: { margin: '10px 0 0', fontWeight: 'bold' } },
              'Interactive Natal Chart Form'
            ),
            el('small', {},
              'The user will be able to enter their birth data'
            )
          )
        )
      );
    },
    save: function () {
      return null;
    },
  });

  console.log('[Astrologer API] Gutenberg blocks registered.');

})(window.wp);
