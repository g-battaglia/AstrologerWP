/**
 * SolarReturnForm component - Interactive form for calculating Solar Return charts.
 */

import { useState } from 'react';
import { cn, t } from '@/lib/utils';
import { fetchSolarReturnChart } from '@/lib/api';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Input } from './ui/Input';
import { Label } from './ui/Label';
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

interface SolarReturnFormProps extends SubjectProps {
    className?: string;
    showChart?: boolean;
    [key: string]: unknown;
}

export function SolarReturnForm({
    className,
    showChart = true,
}: SolarReturnFormProps) {
    const [subject, setSubject] = useState<SubjectFormData>({
        ...DEFAULT_SUBJECT,
    });
    const [returnYear, setReturnYear] = useState<string>(
        String(new Date().getFullYear()),
    );

    const [subjectErrors, setSubjectErrors] = useState<SubjectFormErrors>({});
    const [submitted, setSubmitted] = useState(false);

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [chartSvg, setChartSvg] = useState<string | null>(null);

    const updateSubject = (field: keyof SubjectFormData, value: string) => {
        const next = { ...subject, [field]: value };
        setSubject(next);
        if (submitted) setSubjectErrors(validateSubjectForm(next));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitted(true);

        const errors = validateSubjectForm(subject);
        setSubjectErrors(errors);
        if (!isFormValid(errors)) return;

        setLoading(true);
        setError(null);
        setChartSvg(null);

        const subj = buildSubject(subject);
        const year = parseInt(returnYear, 10);

        // Broadcast event for decoupled components
        dispatchAstrologerEvent('astrologer:solar-return-data-updated', {
            subject: subj,
            returnYear: year,
        });

        if (showChart) {
            try {
                const chartRes = await fetchSolarReturnChart({
                    subject: subj,
                    year: year,
                });
                const anyChart = chartRes as any;
                setChartSvg(anyChart.chart || anyChart.svg || null);
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
        <Card className={cn('astrologer-solar-return-form', className)}>
            <CardHeader>
                <CardTitle>
                    {t('solarReturnTitle', 'Solar Return Chart')}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-3 border p-3 rounded-md">
                        <h3 className="font-semibold text-sm uppercase text-muted-foreground">
                            {t('birthDataHeading', 'Birth Data')}
                        </h3>
                        <SubjectFormFields
                            data={subject}
                            onChange={updateSubject}
                            idPrefix="solar"
                            errors={submitted ? subjectErrors : undefined}
                        />
                    </div>

                    <div className="space-y-2 border p-3 rounded-md">
                        <h3 className="font-semibold text-sm uppercase text-muted-foreground">
                            {t('returnYearHeading', 'Return Year')}
                        </h3>
                        <div className="space-y-1">
                            <Label htmlFor="solar-return-year">
                                {t('labelYear', 'Year')}
                            </Label>
                            <Input
                                id="solar-return-year"
                                type="number"
                                value={returnYear}
                                onChange={(e) => setReturnYear(e.target.value)}
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
                            t('solarReturnSubmit', 'Calculate Solar Return')
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
            </CardContent>
        </Card>
    );
}
