/**
 * LunarReturnChart component - Displays the Lunar Return chart.
 *
 * This component:
 * 1. Receives subject birth data and return month/year
 * 2. Calls the Lunar Return API
 * 3. Renders the SVG chart
 */

import { useState, useEffect } from 'react';
import { fetchLunarReturnChart } from '@/lib/api';
import { useAstrologerEvent } from '@/lib/events';
import { cn, t } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Loader } from './ui/Loader';
import type { SubjectProps } from '../ComponentMounter';

interface LunarReturnChartProps {
    subject?: SubjectProps;
    returnYear?: number;
    returnMonth?: number;
    className?: string;
    [key: string]: unknown;
}

export function LunarReturnChart({
    className,
    subject: initialSubject,
    returnYear: initialYear,
    returnMonth: initialMonth,
}: LunarReturnChartProps) {
    const [svg, setSvg] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const [subject, setSubject] = useState<SubjectProps | undefined>(
        initialSubject
    );
    const [returnYear, setReturnYear] = useState<number>(
        initialYear || new Date().getFullYear()
    );
    const [returnMonth, setReturnMonth] = useState<number>(
        initialMonth || new Date().getMonth() + 1
    );
    const [hasData, setHasData] = useState(!!initialSubject?.year);

    useAstrologerEvent<{
        subject: SubjectProps;
        returnYear: number;
        returnMonth: number;
    }>('astrologer:lunar-return-data-updated', (data) => {
        setSubject(data.subject);
        setReturnYear(data.returnYear);
        setReturnMonth(data.returnMonth);
        setHasData(true);
        loadChart(data.subject, data.returnYear, data.returnMonth);
    });

    const loadChart = async (
        subj: SubjectProps,
        year: number,
        month: number
    ) => {
        setLoading(true);
        setError(null);
        try {
            const response = await fetchLunarReturnChart({
                subject: { ...subj },
                year: year,
                month: month,
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
        if (
            !svg &&
            hasData &&
            subject &&
            returnYear &&
            returnMonth &&
            !loading
        ) {
            loadChart(subject, returnYear, returnMonth);
        }
    }, []);

    return (
        <Card className={cn('astrologer-lunar-return-chart', className)}>
            <CardHeader>
                <CardTitle>
                    {t('lunarReturnChart', 'Lunar Return Chart')}
                    {returnYear &&
                        returnMonth &&
                        ` - ${returnMonth}/${returnYear}`}
                </CardTitle>
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
                                'enterLunarReturnData',
                                'Enter birth data, month and year to view the Lunar Return Chart.'
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
