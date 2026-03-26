/**
 * API client for calls to the WordPress backend.
 *
 * All calls go through the plugin REST bridge,
 * which adds API credentials on the server side.
 */

import { apiRequest, getConfig } from './utils';
import type { SubjectProps } from '../ComponentMounter';

// ============================================================================
// RESPONSE TYPES
// ============================================================================

/**
 * Response for the SVG chart.
 */
export interface ChartResponse {
    svg?: string;
    chart?: string;
}

/**
 * Planetary aspect.
 */
export interface Aspect {
    p1_name: string;
    p2_name: string;
    aspect: string;
    aspect_degrees: number;
    orbit: number;
    aspect_movement?: string;
    diff?: number;
}

/**
 * Distribution of an element or modality.
 */
export interface Distribution {
    name: string;
    value: number;
    percentage: number;
}

/**
 * Subject data calculated by the API.
 */
export interface SubjectData {
    name: string;
    aspects?: Aspect[];
    elements_distribution?: Distribution[];
    modalities_distribution?: Distribution[];
    points?: Record<string, unknown>[];
}

/**
 * Lunar phase data returned by the API.
 */
export interface LunarPhase {
    degrees_between_s_m: number;
    moon_phase: number;
    moon_emoji: string;
    moon_phase_name: string;
}

/**
 * Response for chart data.
 */
export interface ChartDataResponse {
    aspects?: Aspect[];
    first_subject?: SubjectData;
    second_subject?: SubjectData;
    elements_distribution?: Distribution[];
    modalities_distribution?: Distribution[];
    points?: Record<string, any>[];
    lunar_phase?: LunarPhase | null;
}

// ==========================================================================
// HELPERS TO ADAPT THE ASTROLOGER API v5 RESPONSE
// ==========================================================================

// ============================================================================
// API FUNCTIONS
// ============================================================================

/**
 * Converts component props into the format required by the API.
 */
function buildSubjectPayload(props: SubjectProps): Record<string, unknown> {
    return {
        name: props.name || 'Subject',
        year: props.year || 1990,
        month: props.month || 1,
        day: props.day || 1,
        hour: props.hour || 12,
        minute: props.minute || 0,
        latitude: props.latitude || 0,
        longitude: props.longitude || 0,
        timezone: props.timezone || 'UTC',
        city: props.city || '',
        nation: props.nation || '',
    };
}

/**
 * Requests the SVG natal chart.
 */
export async function fetchNatalChart(
    props: SubjectProps,
): Promise<ChartResponse> {
    return apiRequest<ChartResponse>('natal-chart', buildSubjectPayload(props));
}

/**
 * Maps element distribution from API response to Distribution array
 */
function mapElementDistribution(raw: any): Distribution[] | undefined {
    if (!raw || typeof raw !== 'object') return undefined;

    const result: Distribution[] = [];

    if (raw.fire !== undefined) {
        result.push({
            name: 'Fire',
            value: Number(raw.fire),
            percentage: Number(raw.fire_percentage || 0),
        });
    }
    if (raw.earth !== undefined) {
        result.push({
            name: 'Earth',
            value: Number(raw.earth),
            percentage: Number(raw.earth_percentage || 0),
        });
    }
    if (raw.air !== undefined) {
        result.push({
            name: 'Air',
            value: Number(raw.air),
            percentage: Number(raw.air_percentage || 0),
        });
    }
    if (raw.water !== undefined) {
        result.push({
            name: 'Water',
            value: Number(raw.water),
            percentage: Number(raw.water_percentage || 0),
        });
    }

    return result.length > 0 ? result : undefined;
}

/**
 * Maps quality distribution from API response to Distribution array
 */
function mapQualityDistribution(raw: any): Distribution[] | undefined {
    if (!raw || typeof raw !== 'object') return undefined;

    const result: Distribution[] = [];

    if (raw.cardinal !== undefined) {
        result.push({
            name: 'Cardinal',
            value: Number(raw.cardinal),
            percentage: Number(raw.cardinal_percentage || 0),
        });
    }
    if (raw.fixed !== undefined) {
        result.push({
            name: 'Fixed',
            value: Number(raw.fixed),
            percentage: Number(raw.fixed_percentage || 0),
        });
    }
    if (raw.mutable !== undefined) {
        result.push({
            name: 'Mutable',
            value: Number(raw.mutable),
            percentage: Number(raw.mutable_percentage || 0),
        });
    }

    return result.length > 0 ? result : undefined;
}

/**
 * Requests natal chart data (aspects, elements, modalities).
 */
export async function fetchNatalChartData(
    props: SubjectProps,
): Promise<ChartDataResponse> {
    const raw = await apiRequest<any>(
        'natal-chart-data',
        buildSubjectPayload(props),
    );

    // Astrologer API v5 wraps the useful data inside `chart_data`.
    const chartData = raw?.chart_data ?? raw;

    // The API already returns aspects in the correct format
    const aspects: Aspect[] = Array.isArray(chartData?.aspects)
        ? chartData.aspects
        : [];
    // Map points if available
    const points = Array.isArray(chartData?.points)
        ? chartData.points
        : undefined;

    const elements_distribution = mapElementDistribution(
        chartData?.element_distribution,
    );
    const modalities_distribution = mapQualityDistribution(
        chartData?.quality_distribution,
    );

    // Extract lunar phase if present
    const lunar_phase: LunarPhase | null = chartData?.lunar_phase ?? null;

    const result: ChartDataResponse = {
        aspects,
        elements_distribution,
        modalities_distribution,
        points,
        lunar_phase,
    };

    return result;
}

/**
 * Requests full subject data.
 */
export async function fetchSubjectData(
    props: SubjectProps,
): Promise<SubjectData> {
    return apiRequest<SubjectData>('subject', buildSubjectPayload(props));
}

// ============================================================================
// ADVANCED ENDPOINTS (SYNASTRY, TRANSIT, COMPOSITE, RETURNS, NOW, COMPATIBILITY)
// ============================================================================

/**
 * Requests data and/or chart for a synastry.
 *
 * The payload must contain:
 * {
 *   first_subject: SubjectModel,
 *   second_subject: SubjectModel,
 *   ...options...
 * }
 */
export async function fetchSynastryChart(
    body: Record<string, unknown>,
): Promise<ChartResponse> {
    return apiRequest<ChartResponse>('synastry-chart', body);
}

export async function fetchSynastryData(
    body: Record<string, unknown>,
): Promise<any> {
    return apiRequest<any>('synastry-chart-data', body);
}

/**
 * Requests data/chart for a transit chart.
 */
export async function fetchTransitChart(
    body: Record<string, unknown>,
): Promise<ChartResponse> {
    return apiRequest<ChartResponse>('transit-chart', body);
}

export async function fetchTransitData(
    body: Record<string, unknown>,
): Promise<any> {
    return apiRequest<any>('transit-chart-data', body);
}

/**
 * Requests data/chart for a composite chart.
 */
export async function fetchCompositeChart(
    body: Record<string, unknown>,
): Promise<ChartResponse> {
    return apiRequest<ChartResponse>('composite-chart', body);
}

export async function fetchCompositeData(
    body: Record<string, unknown>,
): Promise<any> {
    return apiRequest<any>('composite-chart-data', body);
}

/**
 * Requests data/chart for the current moment (now).
 */
export async function fetchNowSubject(
    body: Record<string, unknown> = {},
): Promise<any> {
    return apiRequest<any>('now-subject', body);
}

export async function fetchNowChart(
    body: Record<string, unknown> = {},
): Promise<ChartResponse & { chart_data?: any }> {
    return apiRequest<ChartResponse & { chart_data?: any }>('now-chart', body);
}

/**
 * Requests data/chart for Solar Return.
 */
export async function fetchSolarReturnData(
    body: Record<string, unknown>,
): Promise<any> {
    return apiRequest<any>('solar-return-chart-data', body);
}

export async function fetchSolarReturnChart(
    body: Record<string, unknown>,
): Promise<ChartResponse & { chart_data?: any }> {
    return apiRequest<ChartResponse & { chart_data?: any }>(
        'solar-return-chart',
        body,
    );
}

/**
 * Requests data/chart for Lunar Return.
 */
export async function fetchLunarReturnData(
    body: Record<string, unknown>,
): Promise<any> {
    return apiRequest<any>('lunar-return-chart-data', body);
}

export async function fetchLunarReturnChart(
    body: Record<string, unknown>,
): Promise<ChartResponse & { chart_data?: any }> {
    return apiRequest<ChartResponse & { chart_data?: any }>(
        'lunar-return-chart',
        body,
    );
}

/**
 * Requests the compatibility score between two subjects.
 */
export async function fetchCompatibilityScore(
    body: Record<string, unknown>,
): Promise<any> {
    return apiRequest<any>('compatibility-score', body);
}

// ============================================================================
// CITY SEARCH (GEONAMES PROXY)
// ============================================================================

/**
 * A single city result from the GeoNames proxy endpoint.
 */
export interface CitySearchResult {
    name: string;
    country: string;
    latitude: number;
    longitude: number;
    timezone: string;
    admin: string;
}

/**
 * Searches for cities via the WordPress GeoNames proxy endpoint.
 * Uses GET (unlike the other POST-based endpoints).
 */
export async function fetchCitySearch(
    query: string,
): Promise<CitySearchResult[]> {
    const config = getConfig();
    const url = `${config.restUrl}city-search?q=${encodeURIComponent(query)}`;

    const response = await fetch(url, {
        method: 'GET',
        headers: {
            'X-WP-Nonce': config.nonce,
        },
    });

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`City search error: ${response.status} - ${errorText}`);
    }

    const data = await response.json();
    return data.results ?? [];
}
