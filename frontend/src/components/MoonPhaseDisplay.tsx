/**
 * MoonPhaseDisplay component - Shows the current lunar phase.
 *
 * This component:
 * 1. Fetches natal chart data for the given subject (or current moment)
 * 2. Extracts the lunar_phase from the response
 * 3. Displays the moon emoji, phase name, and Sun-Moon angle
 *
 * It also listens for `astrologer:birth-data-updated` events so it can
 * update automatically when placed alongside a BirthForm.
 */

import { useState, useEffect } from 'react';
import { fetchNatalChartData, fetchNowChart, type LunarPhase } from '@/lib/api';
import { useAstrologerEvent } from '@/lib/events';
import { cn, t } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Loader } from './ui/Loader';
import type { SubjectProps } from '../ComponentMounter';

interface MoonPhaseDisplayProps extends SubjectProps {
    /** Additional CSS class */
    className?: string;
    /** Preloaded lunar phase data (optional) */
    preloadedPhase?: LunarPhase;
    /** If true, fetches the current moment instead of requiring subject data */
    useNow?: boolean;
    /** Allow any additional props from ComponentMounter */
    [key: string]: unknown;
}

/**
 * Component to display the lunar phase.
 */
export function MoonPhaseDisplay({
    className,
    preloadedPhase,
    useNow = false,
    ...initialSubjectProps
}: MoonPhaseDisplayProps) {
    const [phase, setPhase] = useState<LunarPhase | null>(
        preloadedPhase ?? null,
    );
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const [subjectProps, setSubjectProps] =
        useState<SubjectProps>(initialSubjectProps);
    const hasSubjectData =
        !!initialSubjectProps.year && !!initialSubjectProps.day;

    // Listen for updates from BirthForm
    useAstrologerEvent<SubjectProps>(
        'astrologer:birth-data-updated',
        (newSubject) => {
            setSubjectProps(newSubject);
            loadFromSubject(newSubject);
        },
    );

    /**
     * Loads lunar phase from natal-chart-data for a given subject.
     */
    const loadFromSubject = async (props: SubjectProps) => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetchNatalChartData(props);
            setPhase(response.lunar_phase ?? null);
        } catch (err) {
            setError(
                err instanceof Error
                    ? err.message
                    : t('errorCalculating', 'Error while calculating'),
            );
        } finally {
            setLoading(false);
        }
    };

    /**
     * Loads lunar phase from the "now" endpoint.
     */
    const loadFromNow = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetchNowChart();
            const chartData = (response as any)?.chart_data ?? response;
            const lunarPhase: LunarPhase | null =
                chartData?.lunar_phase ?? null;
            setPhase(lunarPhase);
        } catch (err) {
            setError(
                err instanceof Error
                    ? err.message
                    : t('errorCalculating', 'Error while calculating'),
            );
        } finally {
            setLoading(false);
        }
    };

    // Initial load
    useEffect(() => {
        if (preloadedPhase) return;

        if (useNow) {
            loadFromNow();
        } else if (hasSubjectData) {
            loadFromSubject(subjectProps);
        }
    }, []); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <Card className={cn('astrologer-moon-phase', className)}>
            <CardHeader>
                <CardTitle>{t('moonPhase', 'Moon Phase')}</CardTitle>
            </CardHeader>
            <CardContent>
                {/* State: Loading */}
                {loading && (
                    <div className="flex items-center justify-center py-8">
                        <Loader />
                        <span className="ml-2 text-muted-foreground">
                            {t('loading', 'Loading...')}
                        </span>
                    </div>
                )}

                {/* State: Error */}
                {error && !loading && (
                    <div className="p-4 bg-destructive/10 border border-destructive/20 rounded-md text-destructive">
                        <p>{error}</p>
                    </div>
                )}

                {/* State: No data */}
                {!phase && !loading && !error && (
                    <div className="p-4 bg-muted border border-muted-foreground/20 rounded-md text-muted-foreground">
                        <p>
                            {t(
                                'moonPhaseNoData',
                                'Enter birth data to view the moon phase.',
                            )}
                        </p>
                    </div>
                )}

                {/* State: Phase loaded */}
                {phase && !loading && (
                    <div className="flex flex-col items-center gap-3 py-4">
                        {/* Moon emoji - large */}
                        <span
                            className="text-6xl leading-none"
                            role="img"
                            aria-label={phase.moon_phase_name}
                        >
                            {phase.moon_emoji}
                        </span>

                        {/* Phase name */}
                        <p className="text-lg font-semibold text-foreground">
                            {t(
                                `moonPhase_${phase.moon_phase_name.replace(/\s+/g, '_').toLowerCase()}`,
                                phase.moon_phase_name,
                            )}
                        </p>

                        {/* Sun-Moon angle */}
                        <p className="text-sm text-muted-foreground">
                            {t('sunMoonAngle', 'Sun-Moon angle')}:{' '}
                            {phase.degrees_between_s_m.toFixed(1)}°
                        </p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
