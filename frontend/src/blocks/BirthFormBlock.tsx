import {
    registerBlockType,
    useBlockProps,
    InspectorControls,
    PanelBody,
    ToggleControl,
    // AstroIcon removed
} from './wp-utils';
// Fragment removed
import { BirthForm } from '../components/BirthForm';
import { Card } from '../components/ui/Card';

registerBlockType('astrologer-api/birth-form', {
    title: 'Natal Chart Form',
    description:
        'Displays a form for users to input birth data and generate a chart.',
    icon: 'id-alt',
    category: 'astrology', // Changed from 'widgets' to 'astrology'
    attributes: {
        showChart: { type: 'boolean', default: true },
        showAspects: { type: 'boolean', default: true },
        showElements: { type: 'boolean', default: true },
        showModalities: { type: 'boolean', default: true },
    },
    edit: (props: any) => {
        const blockProps = useBlockProps({
            className: 'astrologer-block-preview',
        });

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title="Display Options" initialOpen={true}>
                        <ToggleControl
                            label="Show Chart"
                            checked={props.attributes.showChart}
                            onChange={(value: boolean) =>
                                props.setAttributes({ showChart: value })
                            }
                        />
                        <ToggleControl
                            label="Show Aspects Table"
                            checked={props.attributes.showAspects}
                            onChange={(value: boolean) =>
                                props.setAttributes({ showAspects: value })
                            }
                        />
                        <ToggleControl
                            label="Show Elements Chart"
                            checked={props.attributes.showElements}
                            onChange={(value: boolean) =>
                                props.setAttributes({ showElements: value })
                            }
                        />
                        <ToggleControl
                            label="Show Modalities Chart"
                            checked={props.attributes.showModalities}
                            onChange={(value: boolean) =>
                                props.setAttributes({ showModalities: value })
                            }
                        />
                    </PanelBody>
                </InspectorControls>

                <Card className="p-4 bg-background">
                    <h3 className="text-center text-sm font-medium mb-4 text-muted-foreground">
                        Preview (Form)
                    </h3>
                    {/* BirthForm might be interactive even in editor, or we can mock it */}
                    <BirthForm
                        show_chart={props.attributes.showChart}
                        show_aspects={props.attributes.showAspects}
                        show_elements={props.attributes.showElements}
                        show_modalities={props.attributes.showModalities}
                    />
                </Card>
            </div>
        );
    },
    save: () => null,
});
