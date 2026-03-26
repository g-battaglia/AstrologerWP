/**
 * PositionsTable component - Displays the table of active points positions.
 *
 * Shows:
 * - Planet/Point Name
 * - Sign
 * - Degree
 * - House
 * - Retrograde status
 */

import { useState, useEffect } from 'react';
import { fetchNatalChartData } from '@/lib/api';
import { useAstrologerEvent } from '@/lib/events';
import { cn, t } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from './ui/Table';
import { Loader } from './ui/Loader';
import type { SubjectProps } from '../ComponentMounter';

interface PositionsTableProps extends SubjectProps {
    /** Additional CSS class */
    className?: string;
    /** Preloaded points (optional) */
    preloadedPoints?: Record<string, any>[];
}

/**
 * Component to display planetary positions.
 */
export function PositionsTable({
    className,
    preloadedPoints,
    ...initialSubjectProps
}: PositionsTableProps) {
    const [points, setPoints] = useState<Record<string, any>[]>(
        preloadedPoints || []
    );
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Keep track of current subject
    const [subjectProps, setSubjectProps] =
        useState<SubjectProps>(initialSubjectProps);
    const [hasData, setHasData] = useState(
        !!preloadedPoints ||
            (!!initialSubjectProps.year && !!initialSubjectProps.day)
    );

    // Listen for updates from BirthForm
    useAstrologerEvent<SubjectProps>(
        'astrologer:birth-data-updated',
        (newSubject) => {
            setSubjectProps(newSubject);
            setHasData(true);
            loadData(newSubject);
        }
    );

    // Load data from API
    const loadData = async (props: SubjectProps) => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetchNatalChartData(props);
            setPoints(response.points || []);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Unknown error');
        } finally {
            setLoading(false);
        }
    };

    // Initial load if props provided directly (not via event)
    useEffect(() => {
        if (!preloadedPoints && hasData && points.length === 0 && !loading) {
            loadData(subjectProps);
        }
    }, []); // Run once on mount if data available

    return (
        <Card className={cn('astrologer-positions-table', className)}>
            <CardHeader>
                <CardTitle>{t('positions', 'Planetary Positions')}</CardTitle>
            </CardHeader>
            <CardContent>
                {/* State: Loading */}
                {loading && (
                    <div className="flex items-center justify-center py-8">
                        <Loader />
                        <span className="ml-2 text-muted-foreground">
                            {t('loading', 'Loading...')}
                        </span>
                    </div>
                )}

                {/* State: Error */}
                {error && !loading && (
                    <div className="p-4 bg-red-50 border border-red-200 rounded-md text-red-700">
                        <p>{error}</p>
                    </div>
                )}

                {/* State: No data */}
                {!hasData && !loading && points.length === 0 && (
                    <div className="p-4 bg-amber-50 border border-amber-200 rounded-md text-amber-700">
                        <p>Enter birth data to view positions.</p>
                    </div>
                )}

                {/* State: Points table */}
                {points.length > 0 && !loading && (
                    <div className="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Point</TableHead>
                                    <TableHead>Sign</TableHead>
                                    <TableHead className="text-right">
                                        Degree
                                    </TableHead>
                                    <TableHead className="text-right">
                                        House
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {points.map((point, idx) => (
                                    <TableRow key={idx}>
                                        <TableCell className="font-medium">
                                            {point.name}
                                        </TableCell>
                                        <TableCell>{point.sign}</TableCell>
                                        <TableCell className="text-right font-mono text-sm">
                                            {typeof point.position === 'number'
                                                ? point.position.toFixed(2)
                                                : point.position}
                                            °
                                            {point.is_retrograde && (
                                                <span className="ml-1 text-xs text-red-500">
                                                    R
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {point.house}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
