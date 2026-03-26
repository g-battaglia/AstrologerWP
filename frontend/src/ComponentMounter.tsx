/**
 * Component that dynamically mounts Astrologer components.
 *
 * It receives the component type to mount and its props,
 * and renders the appropriate component.
 */

import { NatalChart } from './components/NatalChart';
import { AspectsTable } from './components/AspectsTable';
import { PositionsTable } from './components/PositionsTable';
import { ElementsChart } from './components/ElementsChart';
import { ModalitiesChart } from './components/ModalitiesChart';
import { BirthForm } from './components/BirthForm';
import { SynastryForm } from './components/SynastryForm';
import { SynastryChart } from './components/SynastryChart';
import { TransitChart } from './components/TransitChart';
import { RelationshipScore } from './components/RelationshipScore';
import { NowForm } from './components/NowForm';
import { NowChart } from './components/NowChart';
import { CompatibilityForm } from './components/CompatibilityForm';
import { CompositeChart } from './components/CompositeChart';
import { CompositeForm } from './components/CompositeForm';
import { TransitForm } from './components/TransitForm';
import { SolarReturnChart } from './components/SolarReturnChart';
import { SolarReturnForm } from './components/SolarReturnForm';
import { LunarReturnChart } from './components/LunarReturnChart';
import { LunarReturnForm } from './components/LunarReturnForm';
import { MoonPhaseDisplay } from './components/MoonPhaseDisplay';
import { ErrorBoundary } from './components/ErrorBoundary';
import { SettingsPage } from './components/SettingsPage';

/**
 * Props for the astrological subject.
 */
export interface SubjectProps {
    name?: string;
    year?: number;
    month?: number;
    day?: number;
    hour?: number;
    minute?: number;
    latitude?: number;
    longitude?: number;
    timezone?: string;
    city?: string;
    nation?: string;
}

/**
 * Props for the ComponentMounter.
 */
interface ComponentMounterProps {
    /** Component type to mount */
    type: string;
    /** Props to pass to the component */
    props: SubjectProps & Record<string, unknown>;
}

/**
 * Component that dynamically mounts Astrologer components.
 *
 * Supports the following types:
 * - natal-chart: SVG graphic of the natal chart
 * - aspects-table: Planetary aspects table
 * - elements-chart: Elements distribution chart
 * - modalities-chart: Modalities distribution chart
 * - birth-form: Full birth data input form
 */
export function ComponentMounter({ type, props }: ComponentMounterProps) {
    /**
     * Renders the appropriate component based on the type.
     */
    const renderComponent = () => {
        switch (type) {
            case 'admin-settings':
                return <SettingsPage />;

            case 'natal-chart':
                return <NatalChart {...props} />;

            case 'aspects-table':
                return <AspectsTable {...props} />;

            case 'elements-chart':
                return <ElementsChart {...props} />;

            case 'modalities-chart':
                return <ModalitiesChart {...props} />;

            case 'birth-form':
                return <BirthForm {...props} />;

            case 'synastry-chart': {
                // EASIER: ComponentMounter adapts. // Let's assume we pass them as flat props and the component adapts, OR adapt here. // We need to reconstruct them here or in the component. // The props coming from PHP (render_react_container -> clean_props) are flat. // BUT SynastryChart expects nested {firstSubject, secondSubject}. // Mapped from flat props (first_name, second_name) to nested SubjectProps if needed
                const firstSubject = extractSubject(props, 'first_');
                const secondSubject = extractSubject(props, 'second_');
                return (
                    <SynastryChart
                        firstSubject={firstSubject}
                        secondSubject={secondSubject}
                    />
                );
            }

            case 'transit-chart': {
                const subject = extractSubject(props, '');
                const transitDate = {
                    year: Number(props.transit_year),
                    month: Number(props.transit_month),
                    day: Number(props.transit_day),
                    hour: Number(props.transit_hour),
                    minute: Number(props.transit_minute),
                    location: {
                        city: String(props.transit_city),
                        nation: String(props.transit_nation),
                        latitude: Number(props.transit_latitude),
                        longitude: Number(props.transit_longitude),
                        timezone: String(props.transit_timezone),
                    },
                };
                return (
                    <TransitChart subject={subject} transitDate={transitDate} />
                );
            }

            case 'compatibility-chart': {
                const firstSubject = extractSubject(props, 'first_');
                const secondSubject = extractSubject(props, 'second_');
                return (
                    <RelationshipScore
                        firstSubject={firstSubject}
                        secondSubject={secondSubject}
                    />
                );
            }

            case 'synastry-form':
                return <SynastryForm {...props} />;

            case 'now-form':
                return <NowForm {...props} />;

            case 'now-chart':
                return <NowChart {...props} />;

            case 'compatibility-form':
                return <CompatibilityForm {...props} />;

            case 'composite-chart': {
                const firstSubject = extractSubject(props, 'first_');
                const secondSubject = extractSubject(props, 'second_');
                return (
                    <CompositeChart
                        firstSubject={firstSubject}
                        secondSubject={secondSubject}
                    />
                );
            }

            case 'composite-form':
                return <CompositeForm {...props} />;

            case 'transit-form':
                return <TransitForm {...props} />;

            case 'positions-table':
                return <PositionsTable {...props} />;

            case 'solar-return-chart': {
                const subject = extractSubject(props, '');
                const returnYear =
                    Number(props.return_year) || new Date().getFullYear();
                return (
                    <SolarReturnChart
                        subject={subject}
                        returnYear={returnYear}
                    />
                );
            }

            case 'solar-return-form':
                return <SolarReturnForm {...props} />;

            case 'lunar-return-chart': {
                const subject = extractSubject(props, '');
                const returnYear =
                    Number(props.return_year) || new Date().getFullYear();
                const returnMonth =
                    Number(props.return_month) || new Date().getMonth() + 1;
                return (
                    <LunarReturnChart
                        subject={subject}
                        returnYear={returnYear}
                        returnMonth={returnMonth}
                    />
                );
            }

            case 'lunar-return-form':
                return <LunarReturnForm {...props} />;

            case 'moon-phase':
                return <MoonPhaseDisplay {...props} />;

            default:
                return (
                    <div className="p-4 text-red-600 bg-red-50 border border-red-200 rounded-md">
                        Unknown component: <code>{type}</code>
                    </div>
                );
        }
    };

    return (
        <ErrorBoundary componentName={type}>{renderComponent()}</ErrorBoundary>
    );
}

// Helper to extract subject data from flat props with optional prefix
function extractSubject(props: any, prefix: string): SubjectProps {
    // If prefix is empty, use keys directly. If prefix is set, look for prefix+key
    const get = (key: string) => props[prefix + key] ?? props[key];

    return {
        name: get('name'),
        year: Number(get('year')),
        month: Number(get('month')),
        day: Number(get('day')),
        hour: Number(get('hour')),
        minute: Number(get('minute')),
        latitude: Number(get('latitude')),
        longitude: Number(get('longitude')),
        timezone: get('timezone'),
        city: get('city'),
        nation: get('nation'),
    };
}
