/**
 * Interactivity store for chart-display blocks (natal-chart, synastry-chart,
 * transit-chart, composite-chart, solar-return-chart, lunar-return-chart,
 * now-chart, positions-table, aspects-table, elements-chart, modalities-chart).
 *
 * Subscribes to the shared event bus and renders the last calculated chart
 * payload into reactive state consumed by block templates.
 *
 * @package
 */

import { store } from '@wordpress/interactivity';
import { on } from '../lib/bus';

interface ChartDisplayState {
	chartSvg: string;
	positions: unknown[];
	aspects: unknown[];
	hasResult: boolean;
	hasData: boolean;
	chartHtml: string;
	positionsHtml: string;
	aspectsHtml: string;
	chartType: string;
}

interface ChartCalculatedPayload {
	chartType?: string;
	svg?: string;
	positions?: unknown[];
	aspects?: unknown[];
	raw?: {
		html?: string;
		positionsHtml?: string;
		aspectsHtml?: string;
	};
}

const initialState: ChartDisplayState = {
	chartSvg: '',
	positions: [],
	aspects: [],
	hasResult: false,
	hasData: false,
	chartHtml: '',
	positionsHtml: '',
	aspectsHtml: '',
	chartType: '',
};

const { state } = store< { state: ChartDisplayState } >(
	'astrologer/chart-display',
	{
		state: initialState,
	}
);

on( 'astrologer:chart-calculated', ( payload: unknown ) => {
	const data = ( payload ?? {} ) as ChartCalculatedPayload;
	state.chartSvg = data.svg ?? '';
	state.positions = Array.isArray( data.positions ) ? data.positions : [];
	state.aspects = Array.isArray( data.aspects ) ? data.aspects : [];
	state.hasResult = true;
	state.hasData = state.positions.length > 0 || state.aspects.length > 0;
	state.chartType = data.chartType ?? '';
	state.chartHtml = data.raw?.html ?? data.svg ?? '';
	state.positionsHtml = data.raw?.positionsHtml ?? '';
	state.aspectsHtml = data.raw?.aspectsHtml ?? '';
} );

export { state };
