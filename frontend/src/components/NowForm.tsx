import { useState } from 'react';
import { cn, t } from '@/lib/utils';
import { fetchNowChart } from '@/lib/api';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Input } from './ui/Input';
import { Label } from './ui/Label';
import { Button } from './ui/Button';
import { Loader } from './ui/Loader';
import type { SubjectProps } from '../ComponentMounter';

interface NowFormProps extends SubjectProps {
    className?: string;
}

export function NowForm({ className }: NowFormProps) {
    const [name, setName] = useState('Now');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [chartSvg, setChartSvg] = useState<string | null>(null);
    const [data, setData] = useState<any | null>(null);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        setChartSvg(null);
        setData(null);

        try {
            const body: Record<string, unknown> = {};
            if (name.trim() !== '') {
                body.name = name.trim();
            }

            const res = await fetchNowChart(body);
            const anyRes: any = res;
            setChartSvg(anyRes.chart || anyRes.svg || null);
            setData(anyRes);
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
        <Card className={cn('astrologer-now-form', className)}>
            <CardHeader>
                <CardTitle>{t('nowTitle', 'Current moment chart')}</CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="now-name">
                            {t('nowSubjectName', 'Subject name')}
                        </Label>
                        <Input
                            id="now-name"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            placeholder={t('nowPlaceholder', 'Now')}
                        />
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
                            t('nowSubmit', 'Calculate current chart')
                        )}
                    </Button>
                </form>

                {error && (
                    <div className="mt-4 p-3 rounded-md border border-red-200 bg-red-50 text-red-700 text-sm">
                        {error}
                    </div>
                )}

                {chartSvg && (
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

                {data && !chartSvg && (
                    <div className="mt-4 p-3 rounded-md border border-green-200 bg-green-50 text-green-700 text-sm">
                        {t('nowSuccess', 'Chart calculated successfully.')}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
