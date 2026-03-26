import {
    registerBlockType,
    useBlockProps,
    // AstroIcon removed
    PieIcon,
    birthDataAttributes,
    BirthDataInspector,
} from './wp-utils';

import { ElementsChart } from '../components/ElementsChart';
import { Card } from '../components/ui/Card';

registerBlockType('astrologer-api/elements-chart', {
    title: 'Elements Chart',
    description: 'Displays the distribution of elements.',
    icon: PieIcon,
    category: 'astrology',
    attributes: birthDataAttributes,
    edit: (props: any) => {
        const blockProps = useBlockProps({
            className: 'astrologer-block-preview',
        });

        // ElementsChart expects same props as others
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
                        Live Preview (Elements)
                    </h3>
                    <ElementsChart {...chartProps} />
                </Card>
            </div>
        );
    },
    save: () => null,
});
