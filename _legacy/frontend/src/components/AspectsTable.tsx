/**
 * AspectsTable component - Displays the table of planetary aspects.
 *
 * Shows a shadcn-style table with:
 * - Planet 1
 * - Aspect type
 * - Planet 2
 * - Orb
 * - Angle
 */

import { useState, useEffect } from 'react';
import { fetchNatalChartData, type Aspect } from '@/lib/api';
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

interface AspectsTableProps extends SubjectProps {
    /** Classe CSS aggiuntiva */
    className?: string;
    /** Aspetti già caricati (opzionale, evita nuova chiamata API) */
    preloadedAspects?: Aspect[];
}

// Inline constants to avoid build issues
const LOCAL_ASTRO_POINTS = [
    'Sun',
    'Moon',
    'Mercury',
    'Venus',
    'Mars',
    'Jupiter',
    'Saturn',
    'Uranus',
    'Neptune',
    'Pluto',
    'North Node',
    'South Node',
    'Chiron',
    'Lilith',
];

const LOCAL_ASPECTS = [
    { name: 'conjunction', defaultOrb: 8 },
    { name: 'opposition', defaultOrb: 8 },
    { name: 'trine', defaultOrb: 8 },
    { name: 'square', defaultOrb: 8 },
    { name: 'sextile', defaultOrb: 6 },
    { name: 'quintile', defaultOrb: 1 },
    { name: 'septile', defaultOrb: 1 },
    { name: 'octile', defaultOrb: 1 },
    { name: 'novile', defaultOrb: 1 },
    { name: 'decile', defaultOrb: 1 },
    { name: 'undecile', defaultOrb: 1 },
    { name: 'semisextile', defaultOrb: 2 },
    { name: 'quincunx', defaultOrb: 2 },
];

/**
 * Helper to get planet name from ID or name.
 */
function getPlanetName(idOrName: string | number): string {
    // Numeric ID
    if (typeof idOrName === 'number') {
        const name = LOCAL_ASTRO_POINTS[idOrName];
        return name || `Unknown (${idOrName})`;
    }

    // String that is a number
    if (!isNaN(Number(idOrName))) {
        const idx = Number(idOrName);
        const name = LOCAL_ASTRO_POINTS[idx];
        if (name) return name;
    }
    return idOrName;
}

/**
 * Formats the aspect name for display.
 * Handles numeric aspect IDs by mapping to ASPECTS array.
 */
function formatAspectName(name: string | number): string {
    let aspectName = name;

    // Handle numeric ID
    if (typeof name === 'number' || !isNaN(Number(name))) {
        const index = Number(name);
        if (LOCAL_ASPECTS[index]) {
            aspectName = LOCAL_ASPECTS[index].name;
        }
    }

    return String(aspectName)
        .replace(/[-_]/g, ' ')
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

/**
 * Planetary aspects table component.
 */
export function AspectsTable({
    className,
    preloadedAspects,
    ...initialSubjectProps
}: AspectsTableProps) {
    const [aspects, setAspects] = useState<Aspect[]>(preloadedAspects || []);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const [subjectProps, setSubjectProps] =
        useState<SubjectProps>(initialSubjectProps);

    const [hasData, setHasData] = useState(
        !!preloadedAspects ||
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

    const loadData = async (props: SubjectProps) => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetchNatalChartData(props);
            setAspects(response.aspects || []);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Unknown error');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        // Determine if we should load initially
        const needsLoading =
            !preloadedAspects && hasData && aspects.length === 0 && !loading;
        if (needsLoading) {
            loadData(subjectProps);
        }
    }, []);

    return (
        <Card className={cn('astrologer-aspects-table', className)}>
            <CardHeader>
                <CardTitle>{t('aspects', 'Planetary Aspects')}</CardTitle>
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
                {!hasData && !loading && aspects.length === 0 && (
                    <div className="p-4 bg-amber-50 border border-amber-200 rounded-md text-amber-700">
                        <p>Enter birth data to view aspects.</p>
                    </div>
                )}

                {/* State: Aspects table */}
                {aspects.length > 0 && !loading && (
                    <div className="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Planet 1</TableHead>
                                    <TableHead>Aspect</TableHead>
                                    <TableHead>Planet 2</TableHead>
                                    <TableHead className="text-right">
                                        Orb
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Angle
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {aspects.map((aspect, idx) => (
                                    <TableRow key={idx}>
                                        <TableCell className="font-medium">
                                            {getPlanetName(aspect.p1_name)}
                                        </TableCell>
                                        <TableCell>
                                            <div className="font-medium">
                                                {formatAspectName(
                                                    aspect.aspect
                                                )}
                                            </div>
                                            {aspect.aspect_movement && (
                                                <div className="text-xs text-muted-foreground">
                                                    {aspect.aspect_movement}
                                                </div>
                                            )}
                                        </TableCell>
                                        <TableCell className="font-medium">
                                            {getPlanetName(aspect.p2_name)}
                                        </TableCell>
                                        <TableCell className="text-right font-mono text-sm">
                                            {aspect.orbit?.toFixed(2)}°
                                        </TableCell>
                                        <TableCell className="text-right font-mono text-sm">
                                            {aspect.aspect_degrees}°
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                )}

                {/* No aspects found */}
                {hasData && aspects.length === 0 && !loading && !error && (
                    <p className="text-muted-foreground text-center py-4">
                        No aspects found.
                    </p>
                )}
            </CardContent>
        </Card>
    );
}
