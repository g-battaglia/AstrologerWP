/**
 * Minimal mock for `@wordpress/block-editor` used in Jest tests.
 *
 * The real package does not ship a root-level TypeScript declaration entry
 * and pulls in heavy DOM dependencies we do not need when unit-testing
 * block `edit` components in isolation.
 *
 * @package Astrologer\Api
 */

import type { ReactNode } from 'react';

export function useBlockProps(): Record< string, unknown > {
	return { className: 'wp-block mock-block' };
}

useBlockProps.save = function save(): Record< string, unknown > {
	return {};
};

export function InspectorControls( {
	children,
}: {
	children?: ReactNode;
} ): ReactNode {
	return children ?? null;
}
