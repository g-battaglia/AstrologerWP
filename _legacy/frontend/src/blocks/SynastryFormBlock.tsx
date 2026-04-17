/**
 * Block: Synastry Form
 */

import {
    registerBlockType,
    InspectorControls,
    PanelBody,
    ToggleControl,
    HeartIcon,
    birthDataAttributes,
    __,
} from './wp-utils';

const synastryFormAttributes = {
    // Config
    show_chart: { type: 'string', default: 'true' }, // 'true' | 'false'
    collapse_on_submit: { type: 'string', default: 'false' },

    // Preload First Subject (prefix 'first_')
    ...Object.keys(birthDataAttributes).reduce((acc, key) => {
        // @ts-ignore
        acc[`first_${key}`] = { ...birthDataAttributes[key] };
        return acc;
    }, {} as any),

    // Preload Second Subject (prefix 'second_')
    ...Object.keys(birthDataAttributes).reduce((acc, key) => {
        // @ts-ignore
        acc[`second_${key}`] = { ...birthDataAttributes[key] };
        return acc;
    }, {} as any),
};

registerBlockType('astrologer-api/synastry-form', {
    apiVersion: 2,
    title: __('Synastry Form', 'astrologer-api'),
    description: __(
        'A form to calculate Synastry Chart between two subjects.',
        'astrologer-api'
    ),
    category: 'astrology',
    icon: HeartIcon,
    attributes: synastryFormAttributes,
    edit: ({ attributes, setAttributes }: any) => {
        // Helper to sync 'show_chart'
        const toggleShowChart = (val: boolean) => {
            setAttributes({ show_chart: val ? 'true' : 'false' });
        };

        const toggleCollapse = (val: boolean) => {
            setAttributes({ collapse_on_submit: val ? 'true' : 'false' });
        };

        // Helper for nested attributes (first_*, second_*)
        // Not fully implementing a dual inspector for brevity,
        // but we can reuse BirthDataInspector logic if we refactored it to accept prefixes.
        // For now, let's just allow configuring the display options.

        return (
            <div className="astrologer-block-wrapper border p-4 rounded bg-white">
                <InspectorControls>
                    <PanelBody title={__('Form Settings', 'astrologer-api')}>
                        <ToggleControl
                            label={__(
                                'Show Chart internally',
                                'astrologer-api'
                            )}
                            help={__(
                                'If enabled, the chart will be shown below the form. Disable if you want to use a separate Synastry Chart block.',
                                'astrologer-api'
                            )}
                            checked={attributes.show_chart === 'true'}
                            onChange={toggleShowChart}
                        />
                        <ToggleControl
                            label={__('Collapse on Submit', 'astrologer-api')}
                            checked={attributes.collapse_on_submit === 'true'}
                            onChange={toggleCollapse}
                        />
                    </PanelBody>
                </InspectorControls>

                <div className="text-center p-4 bg-muted/20 border border-dashed rounded">
                    {HeartIcon}
                    <p className="font-semibold">
                        {__('Synastry Form', 'astrologer-api')}
                    </p>
                    <p className="text-sm text-muted-foreground">
                        {__(
                            'Frontend preview not available. This block renders the input form.',
                            'astrologer-api'
                        )}
                    </p>
                </div>
            </div>
        );
    },
    save: () => null,
});
