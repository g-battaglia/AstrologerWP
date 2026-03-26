import { useState } from 'react';
import { cn, t } from '@/lib/utils';
import { fetchSynastryChart, fetchSynastryData } from '@/lib/api';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Button } from './ui/Button';
import { Loader } from './ui/Loader';
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

interface SynastryFormProps extends SubjectProps {
    className?: string;
    showChart?: boolean;
}

export function SynastryForm({
    className,
    showChart = true,
}: SynastryFormProps) {
    const [first, setFirst] = useState<SubjectFormData>({ ...DEFAULT_SUBJECT });
    const [second, setSecond] = useState<SubjectFormData>({
        ...DEFAULT_SUBJECT,
        name: 'Partner',
        year: '1992',
    });

    const [firstErrors, setFirstErrors] = useState<SubjectFormErrors>({});
    const [secondErrors, setSecondErrors] = useState<SubjectFormErrors>({});
    const [submitted, setSubmitted] = useState(false);

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Local state for unified mode
    const [chartSvg, setChartSvg] = useState<string | null>(null);
    const [data, setData] = useState<any | null>(null);

    const updateFirst = (field: keyof SubjectFormData, value: string) => {
        const next = { ...first, [field]: value };
        setFirst(next);
        if (submitted) setFirstErrors(validateSubjectForm(next));
    };

    const updateSecond = (field: keyof SubjectFormData, value: string) => {
        const next = { ...second, [field]: value };
        setSecond(next);
        if (submitted) setSecondErrors(validateSubjectForm(next));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitted(true);

        const e1 = validateSubjectForm(first);
        const e2 = validateSubjectForm(second);
        setFirstErrors(e1);
        setSecondErrors(e2);
        if (!isFormValid(e1) || !isFormValid(e2)) return;

        setLoading(true);
        setError(null);
        setChartSvg(null);
        setData(null);

        const s1 = buildSubject(first);
        const s2 = buildSubject(second);

        // Broadcast event for decoupled components
        dispatchAstrologerEvent('astrologer:synastry-data-updated', {
            firstSubject: s1,
            secondSubject: s2,
        });

        const body = {
            first_subject: s1,
            second_subject: s2,
        };

        // If unified mode is enabled (showChart=true), we also fetch locally
        if (showChart) {
            try {
                const [chartRes, dataRes] = await Promise.all([
                    fetchSynastryChart(body).catch(() => null),
                    fetchSynastryData(body),
                ]);

                if (chartRes) {
                    const anyChart: any = chartRes;
                    setChartSvg(anyChart.chart || anyChart.svg || null);
                }

                setData(dataRes);
            } catch (err) {
                setError(
                    err instanceof Error
                        ? err.message
                        : t('errorCalculating', 'Error while calculating'),
                );
            }
        } else {
            // Just a small delay for UX
            await new Promise((r) => setTimeout(r, 500));
        }

        setLoading(false);
    };

    return (
        <Card className={cn('astrologer-synastry-form', className)}>
            <CardHeader>
                <CardTitle>{t('synastryTitle', 'Synastry')}</CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid md:grid-cols-2 gap-6">
                        <div className="space-y-3 border p-3 rounded-md">
                            <h3 className="font-semibold text-sm uppercase text-muted-foreground">
                                {t('firstSubject', 'First subject')}
                            </h3>
                            <SubjectFormFields
                                data={first}
                                onChange={updateFirst}
                                idPrefix="syn-first"
                                errors={submitted ? firstErrors : undefined}
                            />
                        </div>

                        <div className="space-y-3 border p-3 rounded-md">
                            <h3 className="font-semibold text-sm uppercase text-muted-foreground">
                                {t('secondSubject', 'Second subject')}
                            </h3>
                            <SubjectFormFields
                                data={second}
                                onChange={updateSecond}
                                idPrefix="syn-second"
                                errors={submitted ? secondErrors : undefined}
                            />
                        </div>
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
                            t('synastrySubmit', 'Calculate synastry')
                        )}
                    </Button>
                </form>

                {error && (
                    <div className="mt-4 p-3 rounded-md border border-red-200 bg-red-50 text-red-700 text-sm">
                        {error}
                    </div>
                )}

                {/* Display results only if in specific unified mode, otherwise rely on decoupled components */}
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

                {/* Only show raw data to debug if chart is present */}
                {showChart && data && (
                    <div className="mt-6">
                        <div className="text-xs text-muted-foreground">
                            {t('dataLoadedFor', 'Data loaded for')}{' '}
                            {data.subjects?.first?.name} &{' '}
                            {data.subjects?.second?.name}
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
