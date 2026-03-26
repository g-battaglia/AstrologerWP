import {
    registerBlockType,
    useBlockProps,
    // AstroIcon removed
    TableIcon,
    birthDataAttributes,
    BirthDataInspector,
} from './wp-utils';

import { AspectsTable } from '../components/AspectsTable';
import { Card } from '../components/ui/Card';

registerBlockType('astrologer-api/aspects-table', {
    title: 'Aspects Table',
    description: 'Displays the planetary aspects table.',
    icon: TableIcon,
    category: 'astrology',
    attributes: birthDataAttributes,
    edit: (props: any) => {
        const blockProps = useBlockProps({
            className: 'astrologer-block-preview',
        });

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
                    <AspectsTable {...chartProps} />
                </Card>
            </div>
        );
    },
    save: () => null,
});
