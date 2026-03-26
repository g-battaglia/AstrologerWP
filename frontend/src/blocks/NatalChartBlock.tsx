import {
    registerBlockType,
    InspectorControls,
    useBlockProps,
    PanelBody,
    TextControl,
    NumberControl,
    // createElement removed
    Fragment,
    // AstroIcon removed
    StarIcon,
    birthDataAttributes,
} from './wp-utils';

import { NatalChart } from '../components/NatalChart';
import { Card } from '../components/ui/Card';

const BirthDataInspector = ({ attributes, setAttributes }: any) => {
    return (
        <Fragment>
            <InspectorControls>
                <PanelBody title="Birth Data" initialOpen={true}>
                    <TextControl
                        label="Name"
                        value={attributes.name}
                        onChange={(value: string) =>
                            setAttributes({ name: value })
                        }
                    />
                    <NumberControl
                        label="Year"
                        value={attributes.year}
                        onChange={(value: number) =>
                            setAttributes({ year: Number(value) })
                        }
                        min={100}
                        max={3000}
                    />
                    <NumberControl
                        label="Month"
                        value={attributes.month}
                        onChange={(value: number) =>
                            setAttributes({ month: Number(value) })
                        }
                        min={1}
                        max={12}
                    />
                    <NumberControl
                        label="Day"
                        value={attributes.day}
                        onChange={(value: number) =>
                            setAttributes({ day: Number(value) })
                        }
                        min={1}
                        max={31}
                    />
                    <NumberControl
                        label="Hour"
                        value={attributes.hour}
                        onChange={(value: number) =>
                            setAttributes({ hour: Number(value) })
                        }
                        min={0}
                        max={23}
                    />
                    <NumberControl
                        label="Minutes"
                        value={attributes.minute}
                        onChange={(value: number) =>
                            setAttributes({ minute: Number(value) })
                        }
                        min={0}
                        max={59}
                    />
                </PanelBody>
                <PanelBody title="Location" initialOpen={false}>
                    <TextControl
                        label="City"
                        value={attributes.city}
                        onChange={(value: string) =>
                            setAttributes({ city: value })
                        }
                    />
                    <TextControl
                        label="Country (code)"
                        value={attributes.nation}
                        onChange={(value: string) =>
                            setAttributes({ nation: value })
                        }
                    />
                    <NumberControl
                        label="Latitude"
                        value={attributes.latitude}
                        onChange={(value: number) =>
                            setAttributes({ latitude: Number(value) })
                        }
                        step={0.0001}
                    />
                    <NumberControl
                        label="Longitude"
                        value={attributes.longitude}
                        onChange={(value: number) =>
                            setAttributes({ longitude: Number(value) })
                        }
                        step={0.0001}
                    />
                    <TextControl
                        label="Time Zone"
                        value={attributes.timezone}
                        onChange={(value: string) =>
                            setAttributes({ timezone: value })
                        }
                    />
                </PanelBody>
            </InspectorControls>
        </Fragment>
    );
};

registerBlockType('astrologer-api/natal-chart', {
    title: 'Natal Chart',
    description: 'Displays the SVG graphic of the natal chart.',
    icon: StarIcon,
    category: 'astrology',
    attributes: birthDataAttributes,
    edit: (props: any) => {
        const blockProps = useBlockProps({
            className: 'astrologer-block-preview',
            style: { minHeight: '400px' },
        });

        // Transform block attributes to component props
        const chartProps = {
            name: props.attributes.name,
            year: props.attributes.year,
            month: props.attributes.month,
            day: props.attributes.day,
            hour: props.attributes.hour,
            minute: props.attributes.minute,
            latitude: props.attributes.latitude,
            longitude: props.attributes.longitude,
            timezone: props.attributes.timezone,
            city: props.attributes.city,
            nation: props.attributes.nation,
        };

        return (
            <div {...blockProps}>
                <BirthDataInspector
                    attributes={props.attributes}
                    setAttributes={props.setAttributes}
                />
                <Card className="p-4 bg-background">
                    <h3 className="text-center text-sm font-medium mb-4 text-muted-foreground">
                        Live Preview
                    </h3>
                    <NatalChart {...chartProps} />
                </Card>
            </div>
        );
    },
    save: () => null,
});
