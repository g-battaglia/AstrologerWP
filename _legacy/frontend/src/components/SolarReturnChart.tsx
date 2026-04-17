/**
 * SolarReturnChart component - Displays the Solar Return chart.
 *
 * This component:
 * 1. Receives subject birth data and return year
 * 2. Calls the Solar Return API
 * 3. Renders the SVG chart
 */

import { useState, useEffect } from 'react';
import { fetchSolarReturnChart } from '@/lib/api';
import { useAstrologerEvent } from '@/lib/events';
import { cn, t } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Loader } from './ui/Loader';
import type { SubjectProps } from '../ComponentMounter';

interface SolarReturnChartProps {
    subject?: SubjectProps;
    returnYear?: number;
    className?: string;
    /** Allow any additional props from ComponentMounter */
    [key: string]: unknown;
}

export function SolarReturnChart({
    className,
    subject: initialSubject,
    returnYear: initialYear,
}: SolarReturnChartProps) {
    const [svg, setSvg] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const [subject, setSubject] = useState<SubjectProps | undefined>(
        initialSubject
    );
    const [returnYear, setReturnYear] = useState<number | undefined>(
        initialYear || new Date().getFullYear()
    );
    const [hasData, setHasData] = useState(!!initialSubject?.year);

    // Listen for updates from SolarReturnForm
    useAstrologerEvent<{
        subject: SubjectProps;
        returnYear: number;
    }>('astrologer:solar-return-data-updated', (data) => {
        setSubject(data.subject);
        setReturnYear(data.returnYear);
        setHasData(true);
        loadChart(data.subject, data.returnYear);
    });

    const loadChart = async (subj: SubjectProps, year: number) => {
        setLoading(true);
        setError(null);
        try {
            const response = await fetchSolarReturnChart({
                subject: { ...subj },
                year: year,
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
        if (!svg && hasData && subject && returnYear && !loading) {
            loadChart(subject, returnYear);
        }
    }, []);

    return (
        <Card className={cn('astrologer-solar-return-chart', className)}>
            <CardHeader>
                <CardTitle>
                    {t('solarReturnChart', 'Solar Return Chart')}
                    {returnYear && ` - ${returnYear}`}
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
                                'enterSolarReturnData',
                                'Enter birth data and year to view the Solar Return Chart.'
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
