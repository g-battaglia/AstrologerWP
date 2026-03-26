import { useState } from 'react';
import { cn, t } from '@/lib/utils';
import { fetchTransitChart, fetchTransitData } from '@/lib/api';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Button } from './ui/Button';
import { Loader } from './ui/Loader';
import { Label } from './ui/Label';
import { SubjectFormFields } from './SubjectFormFields';
import {
    DEFAULT_SUBJECT,
    buildSubject,
    validateSubjectForm,
    isFormValid,
} from '@/lib/types';
import type { SubjectFormData, SubjectFormErrors } from '@/lib/types';
import type { SubjectProps } from '../ComponentMounter';
import { dispatchAstrologerEvent } from '@/lib/events';

interface TransitFormProps extends SubjectProps {
    className?: string;
    showChart?: boolean;
}

const DEFAULT_TRANSIT: SubjectFormData = {
    name: 'Transit',
    year: new Date().getFullYear().toString(),
    month: (new Date().getMonth() + 1).toString(),
    day: new Date().getDate().toString(),
    hour: new Date().getHours().toString(),
    minute: '0',
    timezone: '',
    latitude: '',
    longitude: '',
    city: '',
    nation: '',
};

export function TransitForm({ className, showChart = true }: TransitFormProps) {
    const [natal, setNatal] = useState<SubjectFormData>({ ...DEFAULT_SUBJECT });
    const [transit, setTransit] = useState<SubjectFormData>({
        ...DEFAULT_TRANSIT,
    });

    const [natalErrors, setNatalErrors] = useState<SubjectFormErrors>({});
    const [transitErrors, setTransitErrors] = useState<SubjectFormErrors>({});
    const [submitted, setSubmitted] = useState(false);

    const [includeHouseComparison, setIncludeHouseComparison] = useState(true);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Local state for unified mode
    const [chartSvg, setChartSvg] = useState<string | null>(null);
    const [data, setData] = useState<any | null>(null);

    const updateNatal = (field: keyof SubjectFormData, value: string) => {
        const next = { ...natal, [field]: value };
        setNatal(next);
        if (submitted) setNatalErrors(validateSubjectForm(next));
    };

    const updateTransit = (field: keyof SubjectFormData, value: string) => {
        const next = { ...transit, [field]: value };
        setTransit(next);
        if (submitted) setTransitErrors(validateSubjectForm(next));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitted(true);

        const e1 = validateSubjectForm(natal);
        const e2 = validateSubjectForm(transit);
        setNatalErrors(e1);
        setTransitErrors(e2);
        if (!isFormValid(e1) || !isFormValid(e2)) return;

        setLoading(true);
        setError(null);
        setChartSvg(null);
        setData(null);

        const natalSubject = buildSubject(natal);
        const transitSubject = buildSubject(transit);

        // Construct payload for event
        const transitEventData = {
            subject: natalSubject,
            transitDate: {
                year: transitSubject.year!,
                month: transitSubject.month!,
                day: transitSubject.day!,
                hour: transitSubject.hour!,
                minute: transitSubject.minute!,
                location: {
                    city: transitSubject.city!,
                    nation: transitSubject.nation!,
                    latitude: transitSubject.latitude!,
                    longitude: transitSubject.longitude!,
                    timezone: transitSubject.timezone!,
                },
            },
        };

        dispatchAstrologerEvent(
            'astrologer:transit-data-updated',
            transitEventData,
        );

        const body: Record<string, unknown> = {
            natal_subject: natalSubject,
            transit_subject: transitSubject,
            include_house_comparison: includeHouseComparison,
        };

        if (showChart) {
            try {
                const [chartRes, dataRes] = await Promise.all([
                    fetchTransitChart(body).catch(() => null),
                    fetchTransitData(body).catch(() => null),
                ]);

                if (chartRes) {
                    const anyChart: any = chartRes;
                    setChartSvg(anyChart.chart || anyChart.svg || null);
                }

                if (dataRes) {
                    setData(dataRes);
                }
            } catch (err) {
                setError(
                    err instanceof Error
                        ? err.message
                        : t('errorCalculating', 'Error while calculating'),
                );
            }
        } else {
            await new Promise((r) => setTimeout(r, 500));
        }

        setLoading(false);
    };

    return (
        <Card className={cn('astrologer-transit-form', className)}>
            <CardHeader>
                <CardTitle>
                    {t('transitTitle', 'Transits on natal chart')}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid md:grid-cols-2 gap-6">
                        <div className="space-y-3 border p-3 rounded-md">
                            <h3 className="font-semibold text-sm uppercase text-muted-foreground">
                                {t('natalChartHeading', 'Natal chart')}
                            </h3>
                            <SubjectFormFields
                                data={natal}
                                onChange={updateNatal}
                                idPrefix="transit-natal"
                                errors={submitted ? natalErrors : undefined}
                            />
                        </div>

                        <div className="space-y-3 border p-3 rounded-md">
                            <h3 className="font-semibold text-sm uppercase text-muted-foreground">
                                {t('transitHeading', 'Transit')}
                            </h3>
                            <SubjectFormFields
                                data={transit}
                                onChange={updateTransit}
                                idPrefix="transit"
                                errors={submitted ? transitErrors : undefined}
                            />
                        </div>
                    </div>

                    <div className="flex items-center space-x-2">
                        <input
                            id="include-house-comparison"
                            type="checkbox"
                            className="h-4 w-4"
                            checked={includeHouseComparison}
                            onChange={(e) =>
                                setIncludeHouseComparison(e.target.checked)
                            }
                        />
                        <Label
                            htmlFor="include-house-comparison"
                            className="text-sm"
                        >
                            {t(
                                'includeHouseComparison',
                                'Include houses comparison',
                            )}
                        </Label>
                    </div>

                    <Button type="submit" disabled={loading} className="w-full">
                        {loading ? (
                            <>
                                <Loader size="sm" />
                                <span className="ml-2">
                                    {t('calculating', 'Calculating...')}
                                </span>
                            </>
                        ) : (
                            t('transitSubmit', 'Calculate transits')
                        )}
                    </Button>
                </form>

                {error && (
                    <div className="mt-4 p-3 rounded-md border border-red-200 bg-red-50 text-red-700 text-sm">
                        {error}
                    </div>
                )}

                {showChart && chartSvg && (
                    <div className="mt-6">
                        <h3 className="font-semibold mb-2">
                            {t('chartTitle', 'Chart')}
                        </h3>
                        <div
                            className="astrologer-chart-svg"
                            dangerouslySetInnerHTML={{ __html: chartSvg }}
                        />
                    </div>
                )}

                {showChart && data && (
                    <div className="mt-6">
                        <div className="text-xs text-muted-foreground">
                            {t('dataLoadedFor', 'Data loaded for')}{' '}
                            {data.subjects?.natal?.name}
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
