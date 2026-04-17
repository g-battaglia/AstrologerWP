import {
    registerBlockType,
    InspectorControls,
    useBlockProps,
    PanelBody,
    TextControl,
    NumberControl,
    // AstroIcon removed
    ClockIcon,
    birthDataAttributes,
    BirthDataInspector,
} from './wp-utils';

import { TransitChart } from '../components/TransitChart';
import { Card } from '../components/ui/Card';

const transitAttributes = {
    // Birth Data (standard)
    ...birthDataAttributes,

    // Transit Date
    transit_year: { type: 'number', default: new Date().getFullYear() },
    transit_month: { type: 'number', default: new Date().getMonth() + 1 },
    transit_day: { type: 'number', default: new Date().getDate() },
    transit_hour: { type: 'number', default: 12 },
    transit_minute: { type: 'number', default: 0 },

    // Transit Location (defaults to empty — user must fill in)
    transit_city: { type: 'string', default: '' },
    transit_nation: { type: 'string', default: '' },
    transit_latitude: { type: 'number', default: 0 },
    transit_longitude: { type: 'number', default: 0 },
    transit_timezone: { type: 'string', default: 'UTC' },
};

registerBlockType('astrologer-api/transit-chart', {
    title: 'Transit Chart',
    description: 'Displays a transit chart for a specific date and location.',
    icon: ClockIcon,
    category: 'astrology',
    attributes: transitAttributes,
    edit: (props: any) => {
        const blockProps = useBlockProps({
            className: 'astrologer-block-preview',
            style: { minHeight: '500px' },
        });

        const subject = {
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

        const transitDate = {
            year: props.attributes.transit_year,
            month: props.attributes.transit_month,
            day: props.attributes.transit_day,
            hour: props.attributes.transit_hour,
            minute: props.attributes.transit_minute,
            location: {
                city: props.attributes.transit_city,
                nation: props.attributes.transit_nation,
                latitude: props.attributes.transit_latitude,
                longitude: props.attributes.transit_longitude,
                timezone: props.attributes.transit_timezone,
            },
        };

        return (
            <div {...blockProps}>
                {/* Subject Inspector (Standard) */}
                <BirthDataInspector
                    attributes={props.attributes}
                    setAttributes={props.setAttributes}
                />

                <InspectorControls>
                    <PanelBody
                        title="Transit Date & Location"
                        initialOpen={false}
                    >
                        <NumberControl
                            label="Tr Years"
                            value={props.attributes.transit_year}
                            onChange={(val: number) =>
                                props.setAttributes({
                                    transit_year: Number(val),
                                })
                            }
                        />
                        <NumberControl
                            label="Tr Month"
                            value={props.attributes.transit_month}
                            onChange={(val: number) =>
                                props.setAttributes({
                                    transit_month: Number(val),
                                })
                            }
                        />
                        <NumberControl
                            label="Tr Day"
                            value={props.attributes.transit_day}
                            onChange={(val: number) =>
                                props.setAttributes({
                                    transit_day: Number(val),
                                })
                            }
                        />
                        <NumberControl
                            label="Tr Hour"
                            value={props.attributes.transit_hour}
                            onChange={(val: number) =>
                                props.setAttributes({
                                    transit_hour: Number(val),
                                })
                            }
                        />
                        <TextControl
                            label="Tr City"
                            value={props.attributes.transit_city}
                            onChange={(val: string) =>
                                props.setAttributes({ transit_city: val })
                            }
                        />
                    </PanelBody>
                </InspectorControls>

                <Card className="p-4 bg-background">
                    <h3 className="text-center text-sm font-medium mb-4 text-muted-foreground">
                        Live Preview (Transits)
                    </h3>
                    <TransitChart subject={subject} transitDate={transitDate} />
                </Card>
            </div>
        );
    },
    save: () => null,
});
