/**
 * NatalChart component - Displays the SVG graphic of the natal chart.
 *
 * This component:
 * 1. Receives birth data as props
 * 2. Calls the API to obtain the SVG chart
 * 3. Renders the SVG in the DOM
 */

import { useState, useEffect } from 'react';
import { fetchNatalChart } from '@/lib/api';
import { useAstrologerEvent } from '@/lib/events';
import { cn, t } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Loader } from './ui/Loader';
import type { SubjectProps } from '../ComponentMounter';

interface NatalChartProps extends SubjectProps {
    /** Additional CSS class */
    className?: string;
}

/**
 * Component to display the SVG graphic of the natal chart.
 *
 * If the props contain valid birth data, the chart is automatically loaded.
 * Otherwise a placeholder is shown.
 */
export function NatalChart({
    className,
    ...initialSubjectProps
}: NatalChartProps) {
    const [svg, setSvg] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Keep track of current subject
    const [subjectProps, setSubjectProps] =
        useState<SubjectProps>(initialSubjectProps);
    const [hasData, setHasData] = useState(
        !!initialSubjectProps.year && !!initialSubjectProps.day
    );

    // Listen for updates from BirthForm
    useAstrologerEvent<SubjectProps>(
        'astrologer:birth-data-updated',
        (newSubject) => {
            setSubjectProps(newSubject);
            setHasData(true);
            loadChart(newSubject);
        }
    );

    // Load chart function
    const loadChart = async (props: SubjectProps) => {
        setLoading(true);
        setError(null);
        setSvg(null);

        try {
            const response = await fetchNatalChart(props);
            // The API can return either svg or chart
            setSvg(response.svg || response.chart || null);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Unknown error');
            console.error('[NatalChart] Error:', err);
        } finally {
            setLoading(false);
        }
    };

    // Initial load if props provided directly (not via event)
    useEffect(() => {
        if (hasData && !svg && !loading) {
            loadChart(subjectProps);
        }
    }, []); // Run once on mount if data available

    return (
        <Card className={cn('astrologer-natal-chart', className)}>
            <CardHeader>
                <CardTitle>
                    {subjectProps.name || t('natalChart', 'Natal Chart')}
                </CardTitle>
            </CardHeader>
            <CardContent>
                {/* State: Loading */}
                {loading && (
                    <div className="flex items-center justify-center py-12">
                        <Loader />
                        <span className="ml-2 text-muted-foreground">
                            {t('loading', 'Loading...')}
                        </span>
                    </div>
                )}

                {/* State: Error */}
                {error && !loading && (
                    <div className="p-4 bg-red-50 border border-red-200 rounded-md text-red-700">
                        <p className="font-medium">Error loading chart</p>
                        <p className="text-sm mt-1">{error}</p>
                    </div>
                )}

                {/* State: No data */}
                {!hasData && !loading && !error && (
                    <div className="p-4 bg-amber-50 border border-amber-200 rounded-md text-amber-700">
                        <p>Enter birth data to view the natal chart.</p>
                    </div>
                )}

                {/* State: Chart loaded */}
                {svg && !loading && !error && (
                    <div
                        className="astrologer-chart-svg"
                        dangerouslySetInnerHTML={{ __html: svg }}
                    />
                )}
            </CardContent>
        </Card>
    );
}
