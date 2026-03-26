/**
 * Astrological constants used throughout the application.
 */

import astrologyEnums from '../../../astrology-enums.json'

export const ASTROLOGICAL_POINTS = astrologyEnums.active_points as string[]

export const DEFAULT_ACTIVE_POINTS = astrologyEnums.default_active_points as string[]

/**
 * Standard astrological aspects with default orbs.
 */
export const ASPECTS = astrologyEnums.active_aspects as Array<{
  name: string
  defaultOrb: number
}>

export const DEFAULT_ACTIVE_ASPECTS = astrologyEnums.default_active_aspects as Array<{
  name: string
  orb: number
}>

/**
 * Default distribution weights.
 */
export const DEFAULT_DISTRIBUTION_WEIGHTS = {
  sun: 15,
  moon: 15,
  mercury: 10,
  venus: 10,
  mars: 10,
  jupiter: 10,
  saturn: 10,
  uranus: 7,
  neptune: 7,
  pluto: 4,
  asc: 20
}
