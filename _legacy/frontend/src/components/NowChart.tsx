/**
 * NowChart component - Displays the SVG graphic of the current moment chart.
 *
 * This component:
 * 1. Calls the API for the current moment chart (no subject input needed)
 * 2. Renders the SVG in the DOM
 * 3. Optionally auto-refreshes at a configurable interval
 */

import { useState, useEffect, useCallback } from 'react';
import { fetchNowChart } from '@/lib/api';
import { cn, t } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Loader } from './ui/Loader';
import { Button } from './ui/Button';

interface NowChartProps {
    /** Additional CSS class */
    className?: string;
    /** Auto-refresh interval in seconds (0 to disable) */
    autoRefresh?: number;
    /** Show refresh button */
    showRefreshButton?: boolean;
    /** Allow any additional props from ComponentMounter */
    [key: string]: unknown;
}

/**
 * Component to display the SVG graphic of the current moment (now) chart.
 *
 * Automatically loads the chart on mount.
 */
export function NowChart({
    className,
    autoRefresh = 0,
    showRefreshButton = true,
}: NowChartProps) {
    const [svg, setSvg] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [lastUpdate, setLastUpdate] = useState<Date | null>(null);

    // Load chart function
    const loadChart = useCallback(async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetchNowChart();
            // The API can return either svg or chart
            setSvg(response.svg || response.chart || null);
            setLastUpdate(new Date());
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Unknown error');
            console.error('[NowChart] Error:', err);
        } finally {
            setLoading(false);
        }
    }, []);

    // Initial load on mount
    useEffect(() => {
        loadChart();
    }, [loadChart]);

    // Auto-refresh if enabled
    useEffect(() => {
        if (autoRefresh > 0) {
            const interval = setInterval(() => {
                loadChart();
            }, autoRefresh * 1000);

            return () => clearInterval(interval);
        }
    }, [autoRefresh, loadChart]);

    return (
        <Card className={cn('astrologer-now-chart', className)}>
            <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle>{t('nowChart', 'Current Moment Chart')}</CardTitle>
                {showRefreshButton && (
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={loadChart}
                        disabled={loading}
                    >
                        {loading
                            ? t('refreshing', 'Refreshing...')
                            : t('refresh', 'Refresh')}
                    </Button>
                )}
            </CardHeader>
            <CardContent>
                {/* State: Loading */}
                {loading && !svg && (
                    <div className="flex items-center justify-center py-12">
                        <Loader />
                        <span className="ml-2 text-muted-foreground">
                            {t('loading', 'Loading...')}
                        </span>
                    </div>
                )}

                {/* State: Error */}
                {error && !loading && (
                    <div className="p-4 bg-red-50 border border-red-200 rounded-md text-red-700">
                        <p className="font-medium">Error loading chart</p>
                        <p className="text-sm mt-1">{error}</p>
                    </div>
                )}

                {/* State: Chart loaded */}
                {svg && (
                    <>
                        <div
                            className="astrologer-chart-svg"
                            dangerouslySetInnerHTML={{ __html: svg }}
                        />
                        {lastUpdate && (
                            <p className="text-xs text-muted-foreground mt-2 text-center">
                                {t('lastUpdated', 'Last updated')}:{' '}
                                {lastUpdate.toLocaleTimeString()}
                            </p>
                        )}
                    </>
                )}
            </CardContent>
        </Card>
    );
}
