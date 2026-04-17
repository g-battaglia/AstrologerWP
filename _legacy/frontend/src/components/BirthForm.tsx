/**
 * BirthForm component - Complete form to enter birth data.
 *
 * This component:
 * 1. Allows the user to enter birth date, time and place
 * 2. Calculates the natal chart via API
 * 3. Displays chart, aspects, elements and modalities
 */

import { useState } from 'react';
import {
    fetchNatalChart,
    fetchNatalChartData,
    type ChartDataResponse,
} from '@/lib/api';
import { cn, t, getConfig } from '@/lib/utils';
import { dispatchAstrologerEvent } from '@/lib/events';
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
import { AspectsTable } from './AspectsTable';
import { ElementsChart } from './ElementsChart';
import { ModalitiesChart } from './ModalitiesChart';
import type { SubjectProps } from '../ComponentMounter';

interface BirthFormProps extends SubjectProps {
    /** Show SVG chart */
    show_chart?: string;
    showChart?: boolean;
    /** Show aspects table */
    show_aspects?: string;
    showAspects?: boolean;
    /** Show elements chart */
    show_elements?: string;
    showElements?: boolean;
    /** Show modalities chart */
    show_modalities?: string;
    showModalities?: boolean;
    /** Additional CSS class */
    className?: string;
}

/**
 * Complete form to calculate the natal chart.
 */
export function BirthForm({
    show_chart,
    showChart = true,
    show_aspects,
    showAspects = true,
    show_elements,
    showElements = true,
    show_modalities,
    showModalities = true,
    className,
}: BirthFormProps) {
    const config = getConfig();
    const collapseOnSubmit = config.settings.collapseBirthFormOnSubmit ?? false;
    const formOutputMode = config.settings.formOutputMode ?? 'inline';
    const isSeparatedMode = formOutputMode === 'separated';

    // Convert string values to booleans (for shortcodes)
    // In separated mode, these are forced to false to avoid inline display
    const displayChart =
        !isSeparatedMode && show_chart !== 'false' && showChart;
    const displayAspects =
        !isSeparatedMode && show_aspects !== 'false' && showAspects;
    const displayElements =
        !isSeparatedMode && show_elements !== 'false' && showElements;
    const displayModalities =
        !isSeparatedMode && show_modalities !== 'false' && showModalities;

    // Form state
    const [formData, setFormData] = useState<SubjectFormData>({
        ...DEFAULT_SUBJECT,
    });
    const [formErrors, setFormErrors] = useState<SubjectFormErrors>({});
    const [submitted, setSubmitted] = useState(false);

    // Results state
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [chartSvg, setChartSvg] = useState<string | null>(null);
    const [chartData, setChartData] = useState<ChartDataResponse | null>(null);
    const [collapsed, setCollapsed] = useState(false);

    /**
     * Updates a form field. Re-validates live after the first submit attempt.
     */
    const updateField = (field: keyof SubjectFormData, value: string) => {
        const next = { ...formData, [field]: value };
        setFormData(next);
        if (submitted) {
            setFormErrors(validateSubjectForm(next));
        }
    };

    /**
     * Submits the form and calculates the natal chart.
     */
    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitted(true);

        const errors = validateSubjectForm(formData);
        setFormErrors(errors);
        if (!isFormValid(errors)) return;

        setLoading(true);
        setError(null);
        setChartSvg(null);
        setChartData(null);

        const subjectData = buildSubject(formData);

        try {
            // Broadcast event so other components update themselves
            dispatchAstrologerEvent(
                'astrologer:birth-data-updated',
                subjectData,
            );

            const shouldFetchLocally =
                displayChart ||
                displayAspects ||
                displayElements ||
                displayModalities;

            if (shouldFetchLocally) {
                // Load data in parallel
                const [chartResponse, dataResponse] = await Promise.all([
                    displayChart
                        ? fetchNatalChart(subjectData)
                        : Promise.resolve(null),
                    fetchNatalChartData(subjectData),
                ]);

                if (chartResponse) {
                    setChartSvg(
                        chartResponse.svg || chartResponse.chart || null,
                    );
                }
                setChartData(dataResponse);
            } else {
                // Just simulate loading delay for UX if no local fetch
                await new Promise((resolve) => setTimeout(resolve, 500));
            }

            if (collapseOnSubmit) {
                setCollapsed(true);
            }
        } catch (err) {
            setError(
                err instanceof Error
                    ? err.message
                    : t('errorCalculating', 'Error while calculating'),
            );
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className={cn('astrologer-birth-form space-y-6', className)}>
            {/* Input form */}
            <Card>
                <CardHeader className="flex items-center justify-between">
                    <CardTitle>{t('birthData', 'Birth Data')}</CardTitle>
                    {collapseOnSubmit && chartData && !loading && (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => setCollapsed((prev) => !prev)}
                        >
                            {collapsed
                                ? t('showForm', 'Show form')
                                : t('hideForm', 'Hide form')}
                        </Button>
                    )}
                </CardHeader>
                {(!collapseOnSubmit || !chartData || !collapsed) && (
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <SubjectFormFields
                                data={formData}
                                onChange={updateField}
                                idPrefix="birth"
                                errors={submitted ? formErrors : undefined}
                            />

                            {/* Submit button */}
                            <Button
                                type="submit"
                                disabled={loading}
                                className="w-full"
                            >
                                {loading ? (
                                    <>
                                        <Loader size="sm" />
                                        <span className="ml-2">
                                            {t('calculating', 'Calculating...')}
                                        </span>
                                    </>
                                ) : (
                                    t('submit', 'Calculate Natal Chart')
                                )}
                            </Button>
                        </form>
                    </CardContent>
                )}
            </Card>

            {/* Error */}
            {error && (
                <div className="p-4 bg-red-50 border border-red-200 rounded-md text-red-700">
                    <p className="font-medium">{t('errorTitle', 'Error')}</p>
                    <p className="text-sm">{error}</p>
                </div>
            )}

            {/* Results */}
            {chartData && !loading && (
                <div className="space-y-6">
                    {/* SVG chart */}
                    {displayChart && chartSvg && (
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    {formData.name ||
                                        t('natalChart', 'Natal Chart')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div
                                    className="astrologer-chart-svg"
                                    dangerouslySetInnerHTML={{
                                        __html: chartSvg,
                                    }}
                                />
                            </CardContent>
                        </Card>
                    )}

                    {/* Aspects table */}
                    {displayAspects && chartData.aspects && (
                        <AspectsTable preloadedAspects={chartData.aspects} />
                    )}

                    {/* Elements and Modalities side by side */}
                    <div className="grid md:grid-cols-2 gap-6">
                        {displayElements && chartData.elements_distribution && (
                            <ElementsChart
                                preloadedDistribution={
                                    chartData.elements_distribution
                                }
                            />
                        )}
                        {displayModalities &&
                            chartData.modalities_distribution && (
                                <ModalitiesChart
                                    preloadedDistribution={
                                        chartData.modalities_distribution
                                    }
                                />
                            )}
                    </div>
                </div>
            )}
        </div>
    );
}
