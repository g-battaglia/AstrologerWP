/**
 * SubjectFormFields — Reusable form fields for astrological subject input.
 *
 * Renders: Name, Day/Month/Year, Hour/Minutes, City/Country, Lat/Lng, Timezone.
 * Used by BirthForm, SynastryForm, TransitForm, CompositeForm, SolarReturnForm,
 * LunarReturnForm, and CompatibilityForm.
 */

import type { SubjectFormData, SubjectFormErrors } from '@/lib/types';
import type { CitySearchResult } from '@/lib/api';
import { t } from '@/lib/utils';
import { Input } from './ui/Input';
import { Label } from './ui/Label';
import { CityAutocomplete } from './CityAutocomplete';

interface SubjectFormFieldsProps {
    /** Current form values */
    data: SubjectFormData;
    /** Called when any field changes */
    onChange: (field: keyof SubjectFormData, value: string) => void;
    /** Prefix for HTML id attributes to avoid collisions */
    idPrefix: string;
    /** Field-level validation errors (shown after first submit attempt) */
    errors?: SubjectFormErrors;
}

/**
 * Inline error message displayed below a form field.
 */
function FieldError({ message }: { message?: string }) {
    if (!message) return null;
    return (
        <p className="text-xs text-destructive mt-0.5" role="alert">
            {message}
        </p>
    );
}

export function SubjectFormFields({
    data,
    onChange,
    idPrefix,
    errors = {},
}: SubjectFormFieldsProps) {
    const id = (field: string) => `${idPrefix}-${field}`;

    return (
        <>
            {/* Name */}
            <div className="space-y-1">
                <Label htmlFor={id('name')}>{t('labelName', 'Name')}</Label>
                <Input
                    id={id('name')}
                    value={data.name}
                    onChange={(e) => onChange('name', e.target.value)}
                />
            </div>

            {/* Day / Month / Year */}
            <div className="grid grid-cols-3 gap-2">
                <div className="space-y-1">
                    <Label htmlFor={id('day')}>{t('labelDay', 'Day')}</Label>
                    <Input
                        id={id('day')}
                        type="number"
                        min={1}
                        max={31}
                        value={data.day}
                        onChange={(e) => onChange('day', e.target.value)}
                        aria-invalid={!!errors.day}
                        required
                    />
                    <FieldError message={errors.day} />
                </div>
                <div className="space-y-1">
                    <Label htmlFor={id('month')}>
                        {t('labelMonth', 'Month')}
                    </Label>
                    <Input
                        id={id('month')}
                        type="number"
                        min={1}
                        max={12}
                        value={data.month}
                        onChange={(e) => onChange('month', e.target.value)}
                        aria-invalid={!!errors.month}
                        required
                    />
                    <FieldError message={errors.month} />
                </div>
                <div className="space-y-1">
                    <Label htmlFor={id('year')}>{t('labelYear', 'Year')}</Label>
                    <Input
                        id={id('year')}
                        type="number"
                        min={1}
                        max={2200}
                        value={data.year}
                        onChange={(e) => onChange('year', e.target.value)}
                        aria-invalid={!!errors.year}
                        required
                    />
                    <FieldError message={errors.year} />
                </div>
            </div>

            {/* Hour / Minutes */}
            <div className="grid grid-cols-2 gap-2">
                <div className="space-y-1">
                    <Label htmlFor={id('hour')}>{t('labelHour', 'Hour')}</Label>
                    <Input
                        id={id('hour')}
                        type="number"
                        min={0}
                        max={23}
                        value={data.hour}
                        onChange={(e) => onChange('hour', e.target.value)}
                        aria-invalid={!!errors.hour}
                        required
                    />
                    <FieldError message={errors.hour} />
                </div>
                <div className="space-y-1">
                    <Label htmlFor={id('minute')}>
                        {t('labelMinutes', 'Minutes')}
                    </Label>
                    <Input
                        id={id('minute')}
                        type="number"
                        min={0}
                        max={59}
                        value={data.minute}
                        onChange={(e) => onChange('minute', e.target.value)}
                        aria-invalid={!!errors.minute}
                        required
                    />
                    <FieldError message={errors.minute} />
                </div>
            </div>

            {/* City / Country */}
            <div className="grid grid-cols-2 gap-2">
                <div className="space-y-1">
                    <Label htmlFor={id('city')}>{t('labelCity', 'City')}</Label>
                    <CityAutocomplete
                        id={id('city')}
                        value={data.city}
                        onInputChange={(val) => onChange('city', val)}
                        onSelect={(city: CitySearchResult) => {
                            onChange('city', city.name);
                            onChange('nation', city.country);
                            onChange('latitude', String(city.latitude));
                            onChange('longitude', String(city.longitude));
                            if (city.timezone) {
                                onChange('timezone', city.timezone);
                            }
                        }}
                        hasError={!!errors.city}
                    />
                    <FieldError message={errors.city} />
                </div>
                <div className="space-y-1">
                    <Label htmlFor={id('nation')}>
                        {t('labelCountryCode', 'Country (code)')}
                    </Label>
                    <Input
                        id={id('nation')}
                        value={data.nation}
                        onChange={(e) => onChange('nation', e.target.value)}
                        placeholder={t(
                            'placeholderCountryCode',
                            'IT, US, UK...',
                        )}
                        aria-invalid={!!errors.nation}
                        required
                    />
                    <FieldError message={errors.nation} />
                </div>
            </div>

            {/* Latitude / Longitude */}
            <div className="grid grid-cols-2 gap-2">
                <div className="space-y-1">
                    <Label htmlFor={id('lat')}>
                        {t('labelLatitude', 'Lat')}
                    </Label>
                    <Input
                        id={id('lat')}
                        type="number"
                        step="0.0001"
                        value={data.latitude}
                        onChange={(e) => onChange('latitude', e.target.value)}
                        aria-invalid={!!errors.latitude}
                        required
                    />
                    <FieldError message={errors.latitude} />
                </div>
                <div className="space-y-1">
                    <Label htmlFor={id('lng')}>
                        {t('labelLongitude', 'Lng')}
                    </Label>
                    <Input
                        id={id('lng')}
                        type="number"
                        step="0.0001"
                        value={data.longitude}
                        onChange={(e) => onChange('longitude', e.target.value)}
                        aria-invalid={!!errors.longitude}
                        required
                    />
                    <FieldError message={errors.longitude} />
                </div>
            </div>

            {/* Timezone */}
            <div className="space-y-1">
                <Label htmlFor={id('timezone')}>
                    {t('labelTimezone', 'Time zone')}
                </Label>
                <Input
                    id={id('timezone')}
                    value={data.timezone}
                    onChange={(e) => onChange('timezone', e.target.value)}
                    placeholder={t('placeholderTimezone', 'Europe/Rome')}
                    aria-invalid={!!errors.timezone}
                    required
                />
                <FieldError message={errors.timezone} />
            </div>
        </>
    );
}
