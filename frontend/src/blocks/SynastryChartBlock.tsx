import {
    registerBlockType,
    InspectorControls,
    useBlockProps,
    SubjectInspector,
    HeartIcon,
} from './wp-utils';

import { SynastryChart } from '../components/SynastryChart';
import { Card } from '../components/ui/Card';

const synastryAttributes = {
    // First Subject (prefix 'first_')
    first_name: { type: 'string', default: 'Person A' },
    first_year: { type: 'number', default: 1988 },
    first_month: { type: 'number', default: 3 },
    first_day: { type: 'number', default: 15 },
    first_hour: { type: 'number', default: 14 },
    first_minute: { type: 'number', default: 30 },
    first_latitude: { type: 'number', default: 51.5074 },
    first_longitude: { type: 'number', default: -0.1278 },
    first_timezone: { type: 'string', default: 'Europe/London' },
    first_city: { type: 'string', default: 'London' },
    first_nation: { type: 'string', default: 'GB' },

    // Second Subject (prefix 'second_')
    second_name: { type: 'string', default: 'Person B' },
    second_year: { type: 'number', default: 1992 },
    second_month: { type: 'number', default: 6 },
    second_day: { type: 'number', default: 2 },
    second_hour: { type: 'number', default: 9 },
    second_minute: { type: 'number', default: 0 },
    second_latitude: { type: 'number', default: 40.7128 },
    second_longitude: { type: 'number', default: -74.006 },
    second_timezone: { type: 'string', default: 'America/New_York' },
    second_city: { type: 'string', default: 'New York' },
    second_nation: { type: 'string', default: 'US' },
};

registerBlockType('astrologer-api/synastry-chart', {
    title: 'Synastry Chart',
    description: 'Displays a synastry chart between two subjects.',
    icon: HeartIcon,
    category: 'astrology',
    attributes: synastryAttributes,
    edit: (props: any) => {
        const blockProps = useBlockProps({
            className: 'astrologer-block-preview',
            style: { minHeight: '500px' },
        });

        const firstSubject = {
            name: props.attributes.first_name,
            year: props.attributes.first_year,
            month: props.attributes.first_month,
            day: props.attributes.first_day,
            hour: props.attributes.first_hour,
            minute: props.attributes.first_minute,
            latitude: props.attributes.first_latitude,
            longitude: props.attributes.first_longitude,
            timezone: props.attributes.first_timezone,
            city: props.attributes.first_city,
            nation: props.attributes.first_nation,
        };

        const secondSubject = {
            name: props.attributes.second_name,
            year: props.attributes.second_year,
            month: props.attributes.second_month,
            day: props.attributes.second_day,
            hour: props.attributes.second_hour,
            minute: props.attributes.second_minute,
            latitude: props.attributes.second_latitude,
            longitude: props.attributes.second_longitude,
            timezone: props.attributes.second_timezone,
            city: props.attributes.second_city,
            nation: props.attributes.second_nation,
        };

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <SubjectInspector
                        title="First Subject"
                        prefix="first_"
                        attributes={props.attributes}
                        setAttributes={props.setAttributes}
                    />
                    <SubjectInspector
                        title="Second Subject"
                        prefix="second_"
                        attributes={props.attributes}
                        setAttributes={props.setAttributes}
                    />
                </InspectorControls>

                <Card className="p-4 bg-background">
                    <h3 className="text-center text-sm font-medium mb-4 text-muted-foreground">
                        Live Preview (Synastry)
                    </h3>
                    <SynastryChart
                        firstSubject={firstSubject}
                        secondSubject={secondSubject}
                    />
                </Card>
            </div>
        );
    },
    save: () => null,
});
