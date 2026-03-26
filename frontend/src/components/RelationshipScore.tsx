import { useState, useEffect } from 'react';
import { fetchCompatibilityScore } from '@/lib/api';
import { cn, t } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Loader } from './ui/Loader';
import type { SubjectProps } from '../ComponentMounter';

interface RelationshipScoreProps {
    firstSubject: SubjectProps;
    secondSubject: SubjectProps;
    className?: string;
}

export function RelationshipScore({
    className,
    firstSubject,
    secondSubject,
}: RelationshipScoreProps) {
    const [data, setData] = useState<any | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const hasData = (s: SubjectProps) => s.year && s.month && s.day;

    useEffect(() => {
        if (!hasData(firstSubject) || !hasData(secondSubject)) return;

        const loadData = async () => {
            setLoading(true);
            setError(null);
            try {
                const response = await fetchCompatibilityScore({
                    first_subject: {
                        ...firstSubject,
                    } as any,
                    second_subject: {
                        ...secondSubject,
                    } as any,
                });
                setData(response);
            } catch (err) {
                setError(err instanceof Error ? err.message : 'Unknown error');
            } finally {
                setLoading(false);
            }
        };

        loadData();
    }, [JSON.stringify(firstSubject), JSON.stringify(secondSubject)]);

    return (
        <Card className={cn('astrologer-relationship-score', className)}>
            <CardHeader>
                <CardTitle>
                    {t('compatibilityScore', 'Compatibility Score')}
                </CardTitle>
            </CardHeader>
            <CardContent>
                {loading && (
                    <div className="flex items-center justify-center py-6">
                        <Loader size="sm" />
                        <span className="ml-2 text-sm text-muted-foreground">
                            {t('calculating', 'Calculating...')}
                        </span>
                    </div>
                )}

                {error && !loading && (
                    <div className="p-3 bg-red-50 border border-red-200 rounded-md text-red-700 text-sm">
                        <p>{error}</p>
                    </div>
                )}

                {data && !loading && !error && (
                    <div className="space-y-4 text-center">
                        <div className="flex flex-col items-center">
                            <span className="text-4xl font-bold text-primary">
                                {data.score}
                            </span>
                            <span className="text-sm text-muted-foreground uppercase tracking-wide mt-1">
                                Total Score
                            </span>
                        </div>

                        {data.score_description && (
                            <div className="p-3 bg-muted/20 rounded-md">
                                <p className="font-medium text-lg">
                                    {data.score_description}
                                </p>
                            </div>
                        )}

                        {typeof data.is_destiny_sign !== 'undefined' &&
                            data.is_destiny_sign && (
                                <div className="inline-block px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-semibold">
                                    Destiny Sign
                                </div>
                            )}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
