/**
 * Event bus for cross-component communication.
 *
 * Used to synchronize separate React roots (components mounted in different blocks).
 */

import { useEffect } from 'react';

/**
 * Event types for the Astrologer API plugin.
 */
export type AstrologerEvent = 
  | 'astrologer:birth-data-updated'
  | 'astrologer:synastry-data-updated'
  | 'astrologer:transit-data-updated'
  | 'astrologer:composite-data-updated'
  | 'astrologer:solar-return-data-updated'
  | 'astrologer:lunar-return-data-updated';

/**
 * Dispatches a custom event with the given data.
 */
export function dispatchAstrologerEvent<T>(event: AstrologerEvent, data: T) {
  // Use CustomEvent to broadcast to window
  const customEvent = new CustomEvent(event, { detail: data });
  window.dispatchEvent(customEvent);
}

/**
 * Hook to listen for Astrologer events.
 *
 * @param eventName Name of the event to listen for
 * @param handler Callback function receiving the data
 */
export function useAstrologerEvent<T>(eventName: AstrologerEvent, handler: (data: T) => void) {
  useEffect(() => {
    const eventHandler = (e: Event) => {
      const customEvent = e as CustomEvent<T>;
      handler(customEvent.detail);
    };

    window.addEventListener(eventName, eventHandler);

    return () => {
      window.removeEventListener(eventName, eventHandler);
    };
  }, [eventName, handler]);
}
