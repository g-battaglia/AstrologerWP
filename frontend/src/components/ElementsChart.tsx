/**
 * ElementsChart component - Displays the distribution of the elements.
 *
 * Shows a horizontal bar chart for:
 * - Fire
 * - Earth
 * - Air
 * - Water
 */

import { useState, useEffect } from 'react';
import { fetchNatalChartData, type Distribution } from '@/lib/api';
import { useAstrologerEvent } from '@/lib/events';
import { cn, t } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Loader } from './ui/Loader';
import type { SubjectProps } from '../ComponentMounter';

interface ElementsChartProps extends SubjectProps {
    /** Additional CSS class */
    className?: string;
    /** Preloaded distribution (optional) */
    preloadedDistribution?: Distribution[];
}

/**
 * Colors for each element.
 */
const ELEMENT_COLORS: Record<string, string> = {
    fire: 'astrologer-bar-fire',
    earth: 'astrologer-bar-earth',
    air: 'astrologer-bar-air',
    water: 'astrologer-bar-water',
};

/**
 * Display names for the elements.
 */
const ELEMENT_NAMES: Record<string, string> = {
    fire: 'Fire',
    earth: 'Earth',
    air: 'Air',
    water: 'Water',
};

/**
 * Component for the elements chart.
 */
export function ElementsChart({
    className,
    preloadedDistribution,
    ...initialSubjectProps
}: ElementsChartProps) {
    const [distribution, setDistribution] = useState<Distribution[]>(
        preloadedDistribution || []
    );
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Keep track of current subject
    const [subjectProps, setSubjectProps] =
        useState<SubjectProps>(initialSubjectProps);
    const [hasData, setHasData] = useState(
        !!preloadedDistribution ||
            (!!initialSubjectProps.year && !!initialSubjectProps.day)
    );

    // Listen for updates from BirthForm
    useAstrologerEvent<SubjectProps>(
        'astrologer:birth-data-updated',
        (newSubject) => {
            setSubjectProps(newSubject);
            setHasData(true);
            loadData(newSubject);
        }
    );

    const loadData = async (props: SubjectProps) => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetchNatalChartData(props);
            setDistribution(response.elements_distribution || []);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Unknown error');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        const needsLoading =
            !preloadedDistribution &&
            hasData &&
            distribution.length === 0 &&
            !loading;
        if (needsLoading) {
            loadData(subjectProps);
        }
    }, []);

    // Compute the maximum value for the bar scale
    const maxValue = Math.max(
        ...distribution.map((d) => d.percentage || d.value || 0),
        1
    );

    return (
        <Card className={cn('astrologer-elements-chart', className)}>
            <CardHeader>
                <CardTitle>{t('elements', 'Elements')}</CardTitle>
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
                {!hasData && !loading && distribution.length === 0 && (
                    <div className="p-4 bg-muted border border-muted-foreground/20 rounded-md text-muted-foreground">
                        <p>Enter birth data to view the elements.</p>
                    </div>
                )}

                {/* State: Chart */}
                {distribution.length > 0 && !loading && (
                    <div className="space-y-4">
                        {distribution.map((element) => {
                            const key = element.name.toLowerCase();
                            const percentage =
                                element.percentage ??
                                (element.value / maxValue) * 100;
                            const colorClass =
                                ELEMENT_COLORS[key] || 'bg-muted-foreground';
                            const displayName =
                                ELEMENT_NAMES[key] || element.name;

                            return (
                                <div key={element.name} className="space-y-1">
                                    <div className="flex justify-between text-sm">
                                        <span className="font-medium">
                                            {displayName}
                                        </span>
                                        <span className="text-muted-foreground">
                                            {element.value?.toFixed(1)} (
                                            {percentage.toFixed(0)}%)
                                        </span>
                                    </div>
                                    <div className="w-full bg-secondary rounded-full h-6 overflow-hidden">
                                        <div
                                            className={cn(
                                                'astrologer-bar',
                                                colorClass
                                            )}
                                            style={{ width: `${percentage}%` }}
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
