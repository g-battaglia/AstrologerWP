/**
 * Shared types for astrologer form components.
 *
 * @module lib/types
 */

import type { SubjectProps } from '../ComponentMounter';

/**
 * Form-level representation of a subject's data.
 * All values are strings because they come from HTML inputs.
 */
export interface SubjectFormData {
    name: string;
    year: string;
    month: string;
    day: string;
    hour: string;
    minute: string;
    timezone: string;
    latitude: string;
    longitude: string;
    city: string;
    nation: string;
}

/**
 * Converts a SubjectFormData (string values) to a SubjectProps (typed values)
 * suitable for API calls.
 */
export function buildSubject(f: SubjectFormData): SubjectProps {
    return {
        name: f.name || 'Subject',
        year: parseInt(f.year, 10),
        month: parseInt(f.month, 10),
        day: parseInt(f.day, 10),
        hour: parseInt(f.hour, 10),
        minute: parseInt(f.minute, 10),
        city: f.city,
        nation: f.nation,
        latitude: parseFloat(f.latitude),
        longitude: parseFloat(f.longitude),
        timezone: f.timezone,
    };
}

/**
 * Default values for a subject form — location fields left empty.
 */
export const DEFAULT_SUBJECT: SubjectFormData = {
    name: '',
    year: '1990',
    month: '1',
    day: '1',
    hour: '12',
    minute: '0',
    timezone: '',
    latitude: '',
    longitude: '',
    city: '',
    nation: '',
};

/**
 * Map of field names to error messages. Empty object means no errors.
 */
export type SubjectFormErrors = Partial<Record<keyof SubjectFormData, string>>;

/**
 * Validates a SubjectFormData and returns field-level error messages.
 * Returns an empty object when all fields are valid.
 */
export function validateSubjectForm(data: SubjectFormData): SubjectFormErrors {
    const errors: SubjectFormErrors = {};

    // Day: required, integer 1–31
    const day = parseInt(data.day, 10);
    if (!data.day.trim() || isNaN(day) || day < 1 || day > 31) {
        errors.day = 'Day must be between 1 and 31';
    }

    // Month: required, integer 1–12
    const month = parseInt(data.month, 10);
    if (!data.month.trim() || isNaN(month) || month < 1 || month > 12) {
        errors.month = 'Month must be between 1 and 12';
    }

    // Year: required, integer 1–2200
    const year = parseInt(data.year, 10);
    if (!data.year.trim() || isNaN(year) || year < 1 || year > 2200) {
        errors.year = 'Year must be between 1 and 2200';
    }

    // Cross-field: validate day against month/year
    if (!errors.day && !errors.month && !errors.year) {
        const maxDay = new Date(year, month, 0).getDate();
        if (day > maxDay) {
            errors.day = `Day must be between 1 and ${maxDay} for this month`;
        }
    }

    // Hour: required, integer 0–23
    const hour = parseInt(data.hour, 10);
    if (!data.hour.trim() || isNaN(hour) || hour < 0 || hour > 23) {
        errors.hour = 'Hour must be between 0 and 23';
    }

    // Minute: required, integer 0–59
    const minute = parseInt(data.minute, 10);
    if (!data.minute.trim() || isNaN(minute) || minute < 0 || minute > 59) {
        errors.minute = 'Minutes must be between 0 and 59';
    }

    // Latitude: required, number -90 to 90
    const lat = parseFloat(data.latitude);
    if (!data.latitude.trim() || isNaN(lat) || lat < -90 || lat > 90) {
        errors.latitude = 'Latitude must be between -90 and 90';
    }

    // Longitude: required, number -180 to 180
    const lng = parseFloat(data.longitude);
    if (!data.longitude.trim() || isNaN(lng) || lng < -180 || lng > 180) {
        errors.longitude = 'Longitude must be between -180 and 180';
    }

    // City: required
    if (!data.city.trim()) {
        errors.city = 'City is required';
    }

    // Nation: required
    if (!data.nation.trim()) {
        errors.nation = 'Country code is required';
    }

    // Timezone: required
    if (!data.timezone.trim()) {
        errors.timezone = 'Time zone is required';
    }

    return errors;
}

/**
 * Returns true when the errors object has no entries.
 */
export function isFormValid(errors: SubjectFormErrors): boolean {
    return Object.keys(errors).length === 0;
}
