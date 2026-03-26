import { useState, useEffect } from 'react';
import { fetchSynastryChart } from '@/lib/api';
import { useAstrologerEvent } from '@/lib/events';
import { cn, t } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Loader } from './ui/Loader';
import type { SubjectProps } from '../ComponentMounter';

interface SynastryChartProps {
    firstSubject?: SubjectProps;
    secondSubject?: SubjectProps;
    className?: string;
}

export function SynastryChart({
    className,
    firstSubject: initialFirst,
    secondSubject: initialSecond,
}: SynastryChartProps) {
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

    // Listen for updates
    useAstrologerEvent<{
        firstSubject: SubjectProps;
        secondSubject: SubjectProps;
    }>('astrologer:synastry-data-updated', (data) => {
        setFirstSubject(data.firstSubject);
        setSecondSubject(data.secondSubject);
        setHasData(true);
        loadChart(data.firstSubject, data.secondSubject);
    });

    const loadChart = async (s1: SubjectProps, s2: SubjectProps) => {
        setLoading(true);
        setError(null);
        try {
            const response = await fetchSynastryChart({
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
        <Card className={cn('astrologer-synastry-chart', className)}>
            <CardHeader>
                <CardTitle>{t('synastryChart', 'Synastry Chart')}</CardTitle>
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
                                'enterSynastryData',
                                'Enter data for two subjects to view Synastry Chart.'
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
