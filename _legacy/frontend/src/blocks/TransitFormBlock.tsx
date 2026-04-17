/**
 * Block: Transit Form
 */

import {
    registerBlockType,
    InspectorControls,
    PanelBody,
    ToggleControl,
    StarIcon, // Using StarIcon instead of specific Transit icon if generic
    __,
} from './wp-utils';

const transitFormAttributes = {
    // Config
    show_chart: { type: 'string', default: 'true' }, // 'true' | 'false'

    // Preload Natal Subject (prefix '') - actually let's use 'natal_' to avoid collision?
    // No, the component TransitForm uses default or internal state.
    // If we want to allow pre-filling from block attributes, we would need to pass them prop-mapped.
    // For now, let's keep it simple and just allow config.
};

registerBlockType('astrologer-api/transit-form', {
    apiVersion: 2,
    title: __('Transit Form', 'astrologer-api'),
    description: __(
        'A form to calculate Transits on a Natal Chart.',
        'astrologer-api'
    ),
    category: 'astrology',
    icon: StarIcon,
    attributes: transitFormAttributes,
    edit: ({ attributes, setAttributes }: any) => {
        const toggleShowChart = (val: boolean) => {
            setAttributes({ show_chart: val ? 'true' : 'false' });
        };

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
                                'If enabled, the chart will be shown below the form.',
                                'astrologer-api'
                            )}
                            checked={attributes.show_chart === 'true'}
                            onChange={toggleShowChart}
                        />
                    </PanelBody>
                </InspectorControls>

                <div className="text-center p-4 bg-muted/20 border border-dashed rounded">
                    {StarIcon}
                    <p className="font-semibold">
                        {__('Transit Form', 'astrologer-api')}
                    </p>
                    <p className="text-sm text-muted-foreground">
                        {__(
                            'Frontend preview not available.',
                            'astrologer-api'
                        )}
                    </p>
                </div>
            </div>
        );
    },
    save: () => null,
});
