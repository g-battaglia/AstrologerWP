import { useState, useEffect } from 'react';
import { fetchTransitChart } from '@/lib/api';
import { useAstrologerEvent } from '@/lib/events';
import { cn, t } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Loader } from './ui/Loader';
import type { SubjectProps } from '../ComponentMounter';

interface TransitChartProps {
    subject?: SubjectProps;
    transitDate?: {
        year: number;
        month: number;
        day: number;
        hour: number;
        minute: number;
        location: {
            city: string;
            nation: string;
            latitude: number;
            longitude: number;
            timezone: string;
        };
    };
    className?: string;
}

export function TransitChart({
    className,
    subject: initialSubject,
    transitDate: initialTransit,
}: TransitChartProps) {
    const [svg, setSvg] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const [subject, setSubject] = useState<SubjectProps | undefined>(
        initialSubject,
    );
    const [transitDate, setTransitDate] = useState<
        TransitChartProps['transitDate'] | undefined
    >(initialTransit);
    const [hasData, setHasData] = useState(
        !!(initialSubject?.year && initialTransit?.year),
    );

    // Listen for updates
    useAstrologerEvent('astrologer:transit-data-updated', (data: any) => {
        setSubject(data.subject);
        setTransitDate(data.transitDate);
        setHasData(true);
        loadChart(data.subject, data.transitDate);
    });

    const loadChart = async (
        subj: SubjectProps,
        trans: NonNullable<TransitChartProps['transitDate']>,
    ) => {
        setLoading(true);
        setError(null);
        try {
            const response = await fetchTransitChart({
                subject: {
                    ...subj,
                } as any,
                transit_date_time: {
                    year: trans.year,
                    month: trans.month,
                    day: trans.day,
                    hour: trans.hour,
                    minute: trans.minute,
                },
                transit_location: trans.location,
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
        if (!svg && hasData && subject && transitDate && !loading) {
            loadChart(subject, transitDate);
        }
    }, []);

    return (
        <Card className={cn('astrologer-transit-chart', className)}>
            <CardHeader>
                <CardTitle>{t('transitChart', 'Transit Chart')}</CardTitle>
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
                {error && !loading && (
                    <div className="p-4 bg-red-50 border border-red-200 rounded-md text-red-700">
                        <p>{error}</p>
                    </div>
                )}

                {!hasData && !loading && !svg && (
                    <div className="p-4 bg-muted border border-muted-foreground/20 rounded-md text-muted-foreground text-center">
                        <p>
                            {t(
                                'enterTransitData',
                                'Enter data to view Transit Chart.',
                            )}
                        </p>
                    </div>
                )}

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
