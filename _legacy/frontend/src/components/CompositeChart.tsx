/**
 * CompositeChart component - Displays the SVG graphic of a composite chart.
 *
 * This component:
 * 1. Receives two subjects as props
 * 2. Calls the API to obtain the composite chart SVG
 * 3. Renders the SVG in the DOM
 */

import { useState, useEffect } from 'react';
import { fetchCompositeChart } from '@/lib/api';
import { useAstrologerEvent } from '@/lib/events';
import { cn, t } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Loader } from './ui/Loader';
import type { SubjectProps } from '../ComponentMounter';

interface CompositeChartProps {
    firstSubject?: SubjectProps;
    secondSubject?: SubjectProps;
    className?: string;
    /** Allow any additional props from ComponentMounter */
    [key: string]: unknown;
}

export function CompositeChart({
    className,
    firstSubject: initialFirst,
    secondSubject: initialSecond,
}: CompositeChartProps) {
    const [svg, setSvg] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const [firstSubject, setFirstSubject] = useState<SubjectProps | undefined>(
        initialFirst
    );
    const [secondSubject, setSecondSubject] = useState<
        SubjectProps | undefined
    >(initialSecond);
    const [hasData, setHasData] = useState(
        !!(initialFirst?.year && initialSecond?.year)
    );

    // Listen for updates from CompositeForm
    useAstrologerEvent<{
        firstSubject: SubjectProps;
        secondSubject: SubjectProps;
    }>('astrologer:composite-data-updated', (data) => {
        setFirstSubject(data.firstSubject);
        setSecondSubject(data.secondSubject);
        setHasData(true);
        loadChart(data.firstSubject, data.secondSubject);
    });

    const loadChart = async (s1: SubjectProps, s2: SubjectProps) => {
        setLoading(true);
        setError(null);
        try {
            const response = await fetchCompositeChart({
                first_subject: { ...s1 } as any,
                second_subject: { ...s2 } as any,
            });
            const anyRes = response as any;
            setSvg(anyRes.chart || anyRes.svg || null);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Unknown error');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (!svg && hasData && firstSubject && secondSubject && !loading) {
            loadChart(firstSubject, secondSubject);
        }
    }, []); // Only initial load if data is present

    return (
        <Card className={cn('astrologer-composite-chart', className)}>
            <CardHeader>
                <CardTitle>{t('compositeChart', 'Composite Chart')}</CardTitle>
            </CardHeader>
            <CardContent>
                {loading && (
                    <div className="flex items-center justify-center py-12">
                        <Loader />
                        <span className="ml-2 text-muted-foreground">
                            {t('loading', 'Loading...')}
                        </span>
                    </div>
                )}

                {/* Error State */}
                {error && !loading && (
                    <div className="p-4 bg-red-50 border border-red-200 rounded-md text-red-700">
                        <p>{error}</p>
                    </div>
                )}

                {/* Empty State */}
                {!hasData && !loading && !svg && (
                    <div className="p-4 bg-muted border border-muted-foreground/20 rounded-md text-muted-foreground text-center">
                        <p>
                            {t(
                                'enterCompositeData',
                                'Enter data for two subjects to view the Composite Chart.'
                            )}
                        </p>
                    </div>
                )}

                {/* Chart State */}
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
