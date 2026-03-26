/**
 * Block: Positions Table
 */

import {
    registerBlockType,
    InspectorControls,
    PanelBody,
    birthDataAttributes,
    BirthDataInspector,
    __,
} from './wp-utils';

registerBlockType('astrologer-api/positions-table', {
    apiVersion: 2,
    title: __('Planetary Positions', 'astrologer-api'),
    description: __(
        'A table showing positions of planets and points.',
        'astrologer-api'
    ),
    category: 'astrology',
    icon: 'clipboard',
    supports: {
        html: false,
    },
    attributes: {
        ...birthDataAttributes,
    },
    edit: ({ attributes, setAttributes }: any) => {
        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Birth Data', 'astrologer-api')}>
                        <BirthDataInspector
                            attributes={attributes}
                            setAttributes={setAttributes}
                        />
                    </PanelBody>
                </InspectorControls>

                <div className="astrologer-block-preview">
                    <div className="astrologer-component-header">
                        {__('Planetary Positions', 'astrologer-api')}
                    </div>
                    <div className="astrologer-positions-table-placeholder">
                        {/* The actual component will be mounted by our frontend script */}
                        <div
                            data-astrologer-component="positions-table"
                            data-props={JSON.stringify(attributes)}
                        >
                            Loading React Component...
                        </div>
                    </div>
                </div>
            </>
        );
    },
    save: () => null, // Dynamic block
});
