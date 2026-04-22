/**
 * Ambient module declarations for packages that don't ship TypeScript types.
 *
 * These are scoped to the Jest test environment — they let ts-jest compile
 * block `edit.tsx` files (which import from `@wordpress/block-editor`)
 * without forcing us to pull in `@types/wordpress__block-editor`.
 *
 * Also pulls in the jest-console matcher declarations (`toHaveWarned`, etc.)
 * so individual test files can acknowledge expected console output.
 *
 * @package Astrologer\Api
 */

/// <reference path="../../node_modules/@wordpress/jest-console/declarations.d.ts" />

declare module '@wordpress/block-editor';
