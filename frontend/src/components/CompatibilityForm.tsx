import { useState } from 'react';
import { cn, t } from '@/lib/utils';
import { fetchCompatibilityScore } from '@/lib/api';
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

interface CompatibilityFormProps extends SubjectProps {
    className?: string;
}

export function CompatibilityForm({ className }: CompatibilityFormProps) {
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
        setData(null);

        const body = {
            first_subject: buildSubject(first),
            second_subject: buildSubject(second),
        };

        try {
            const res = await fetchCompatibilityScore(body);
            setData(res);
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
        <Card className={cn('astrologer-compatibility-form', className)}>
            <CardHeader>
                <CardTitle>
                    {t('compatibilityTitle', 'Compatibility score')}
                </CardTitle>
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
                                idPrefix="comp-first"
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
                                idPrefix="comp-second"
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
                            t('compatibilitySubmit', 'Calculate compatibility')
                        )}
                    </Button>
                </form>

                {error && (
                    <div className="mt-4 p-3 rounded-md border border-red-200 bg-red-50 text-red-700 text-sm">
                        {error}
                    </div>
                )}

                {data && (
                    <div className="mt-6 space-y-3">
                        <h3 className="font-semibold mb-1">
                            {t('resultTitle', 'Result')}
                        </h3>
                        {typeof data.score !== 'undefined' && (
                            <p className="text-sm">
                                {t('scoreLabel', 'Score:')}{' '}
                                <span className="font-semibold">
                                    {data.score}
                                </span>{' '}
                                {data.score_description
                                    ? `(${data.score_description})`
                                    : ''}
                            </p>
                        )}
                        {typeof data.is_destiny_sign !== 'undefined' && (
                            <p className="text-sm">
                                {t('destinyLabel', 'Destiny:')}{' '}
                                {data.is_destiny_sign
                                    ? t('yes', 'Yes')
                                    : t('no', 'No')}
                            </p>
                        )}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
