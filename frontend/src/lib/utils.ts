/**
 * Utility functions for the Astrologer API frontend.
 */

import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

/**
 * Combines CSS classes with Tailwind support.
 * Uses clsx for conditional logic and tailwind-merge to resolve conflicts.
 *
 * @example
 * cn('px-2 py-1', isActive && 'bg-blue-500', 'px-4')
 * // => 'py-1 bg-blue-500 px-4' (px-4 overrides px-2)
 */
export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

/**
 * Global configuration passed from WordPress.
 */
declare global {
  interface Window {
    astrologerApiConfig?: {
      restUrl: string
      nonce: string
      settings: {
        language: string
        houseSystem: string
        theme: string
        sidereal: boolean
        uiThemeMode: string
        collapseBirthFormOnSubmit: boolean
        formOutputMode: string
      }
      i18n: Record<string, string>
    }
  }
}

/**
 * Returns the plugin configuration.
 * Falls back to default values if not available.
 */
export function getConfig() {
  return (
    window.astrologerApiConfig ?? {
      restUrl: '/wp-json/astrologer/v1/',
      nonce: '',
      settings: {
        language: 'EN',
        houseSystem: 'P',
        theme: 'classic',
        sidereal: false,
        uiThemeMode: 'light',
        collapseBirthFormOnSubmit: false,
        formOutputMode: 'inline',
      },
      i18n: {},
    }
  )
}

/**
 * Performs a call to the plugin WordPress REST API.
 *
 * @param endpoint - Relative endpoint (e.g. 'natal-chart')
 * @param body - Request body
 * @returns Promise with the JSON response
 */
export async function apiRequest<T>(endpoint: string, body: Record<string, unknown>): Promise<T> {
  const config = getConfig()
  const url = `${config.restUrl}${endpoint}`

  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': config.nonce,
    },
    body: JSON.stringify(body),
  })

  if (!response.ok) {
    const errorText = await response.text()
    throw new Error(`API Error: ${response.status} - ${errorText}`)
  }

  return response.json()
}

/**
 * Translates a string using translations from WordPress.
 *
 * @param key - Translation key
 * @param fallback - Fallback value
 */
export function t(key: string, fallback?: string): string {
  const config = getConfig()
  return config.i18n[key] ?? fallback ?? key
}
