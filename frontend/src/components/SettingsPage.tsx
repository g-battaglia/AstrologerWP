import { useCallback, useEffect, useRef, useState } from 'react';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from './ui/Card';
import { Button } from './ui/Button';
import { Input } from './ui/Input';
import { Label } from './ui/Label';
import { Loader } from './ui/Loader';
import { Checkbox } from './ui/Checkbox';
import { Switch } from './ui/Switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from './ui/Table';
import { apiRequest, cn, t } from '@/lib/utils';
import {
    ASPECTS,
    ASTROLOGICAL_POINTS,
    DEFAULT_ACTIVE_ASPECTS,
    DEFAULT_ACTIVE_POINTS,
    DEFAULT_DISTRIBUTION_WEIGHTS,
} from '@/lib/constants';

function formatEnumLabel(value: string): string {
    return value
        .replace(/[_-]+/g, ' ')
        .split(' ')
        .filter(Boolean)
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ');
}

interface RawSettingsResponse {
    rapidapi_key?: string;
    geonames_username?: string;
    base_url?: string;
    language?: string;
    house_system?: string;
    theme?: string;
    ui_theme_mode?: string;
    sidereal?: boolean;
    sidereal_mode?: string;
    perspective_type?: string;
    split_chart?: boolean;
    transparent_background?: boolean;
    show_house_position_comparison?: boolean;
    show_cusp_position_comparison?: boolean;
    show_degree_indicators?: boolean;
    collapse_birth_form_on_submit?: boolean;
    custom_title?: string;
    distribution_method?: string;
    active_points?: unknown;
    active_aspects?: unknown;
    custom_distribution_weights?: unknown;
    synastry_include_house_comparison?: boolean;
    synastry_include_relationship_score?: boolean;
    transit_include_house_comparison?: boolean;
    returns_include_house_comparison?: boolean;
    returns_wheel_type?: string;
    form_output_mode?: string;
}

interface AspectSetting {
    name: string;
    active: boolean;
    orb: number;
}

interface SettingsFormState {
    rapidapi_key: string;
    geonames_username: string;
    base_url: string;
    language: string;
    house_system: string;
    theme: string;
    ui_theme_mode: string;
    sidereal: boolean;
    sidereal_mode: string;
    perspective_type: string;
    split_chart: boolean;
    transparent_background: boolean;
    show_house_position_comparison: boolean;
    show_cusp_position_comparison: boolean;
    show_degree_indicators: boolean;
    collapse_birth_form_on_submit: boolean;
    custom_title: string;
    distribution_method: string;
    synastry_include_house_comparison: boolean;
    synastry_include_relationship_score: boolean;
    transit_include_house_comparison: boolean;
    returns_include_house_comparison: boolean;
    returns_wheel_type: string;
    form_output_mode: string;

    // New structured state
    active_points: string[];
    active_aspects: AspectSetting[];
    distribution_weights: Record<string, number>;
}

// Ensure default weights object is flat numbers
const getSafeDefaultWeights = () => ({ ...DEFAULT_DISTRIBUTION_WEIGHTS });

function mapSettingsToForm(settings: RawSettingsResponse): SettingsFormState {
    // Map Active Points
    let activePoints: string[] = [];
    if (Array.isArray(settings.active_points)) {
        activePoints = (settings.active_points as unknown[])
            .map((p) => String(p))
            .filter((p) => ASTROLOGICAL_POINTS.includes(p));
    } else {
        activePoints = [...DEFAULT_ACTIVE_POINTS];
    }

    if (activePoints.length === 0) activePoints = [...DEFAULT_ACTIVE_POINTS];

    // Map Active Aspects
    let activeAspects: AspectSetting[] = [];

    // Create a map of existing settings for easy lookup
    const serverAspectsMap = new Map<string, number>();
    if (Array.isArray(settings.active_aspects)) {
        for (const raw of settings.active_aspects as any[]) {
            if (raw && typeof raw === 'object' && raw.name) {
                const name = String(raw.name);
                if (!ASPECTS.some((a) => a.name === name)) continue;
                serverAspectsMap.set(name, Number(raw.orb) || 0);
            }
        }
    }

    const useDefaults = serverAspectsMap.size === 0;

    const defaultAspectsMap = new Map<string, number>();
    for (const a of DEFAULT_ACTIVE_ASPECTS) {
        defaultAspectsMap.set(a.name, a.orb);
    }

    activeAspects = ASPECTS.map((std) => {
        if (useDefaults) {
            const orb = defaultAspectsMap.get(std.name) ?? std.defaultOrb;
            return {
                name: std.name,
                active: defaultAspectsMap.has(std.name),
                orb,
            };
        }
        const userOrb = serverAspectsMap.get(std.name);
        return {
            name: std.name,
            active: userOrb !== undefined,
            orb: userOrb !== undefined ? userOrb : std.defaultOrb,
        };
    });

    // Map Distribution Weights
    let weights: Record<string, number> = getSafeDefaultWeights();
    if (
        settings.custom_distribution_weights &&
        typeof settings.custom_distribution_weights === 'object'
    ) {
        const rawWeights = settings.custom_distribution_weights as Record<
            string,
            unknown
        >;
        // Merge valid numbers into the defaults
        for (const [key, val] of Object.entries(rawWeights)) {
            const num = Number(val);
            if (!Number.isNaN(num)) {
                weights[key] = num;
            }
        }
    }

    return {
        rapidapi_key: settings.rapidapi_key ?? '',
        geonames_username: settings.geonames_username ?? '',
        base_url: settings.base_url ?? 'https://astrologer.p.rapidapi.com',
        language: settings.language ?? 'EN',
        house_system: settings.house_system ?? 'P',
        theme: settings.theme ?? 'classic',
        ui_theme_mode: settings.ui_theme_mode ?? 'light',
        sidereal: Boolean(settings.sidereal),
        sidereal_mode: settings.sidereal_mode ?? 'LAHIRI',
        perspective_type: settings.perspective_type ?? 'Apparent Geocentric',
        split_chart: Boolean(settings.split_chart),
        transparent_background: Boolean(settings.transparent_background),
        show_house_position_comparison: Boolean(
            settings.show_house_position_comparison,
        ),
        show_cusp_position_comparison: Boolean(
            settings.show_cusp_position_comparison,
        ),
        show_degree_indicators: Boolean(settings.show_degree_indicators),
        collapse_birth_form_on_submit: Boolean(
            settings.collapse_birth_form_on_submit,
        ),
        custom_title: settings.custom_title ?? '',
        distribution_method: settings.distribution_method ?? 'weighted',
        synastry_include_house_comparison: Boolean(
            settings.synastry_include_house_comparison,
        ),
        synastry_include_relationship_score: Boolean(
            settings.synastry_include_relationship_score,
        ),
        transit_include_house_comparison: Boolean(
            settings.transit_include_house_comparison,
        ),
        returns_include_house_comparison: Boolean(
            settings.returns_include_house_comparison,
        ),
        returns_wheel_type: settings.returns_wheel_type ?? 'dual',
        form_output_mode: settings.form_output_mode ?? 'inline',

        active_points: activePoints,
        active_aspects: activeAspects,
        distribution_weights: weights,
    };
}

function buildPayloadFromForm(
    form: SettingsFormState,
): Record<string, unknown> {
    // Reconstruct active aspects list for API: only name and orb of active ones
    const activeAspectsPayload = form.active_aspects
        .filter((a) => a.active)
        .map((a) => ({ name: a.name, orb: a.orb }));

    return {
        rapidapi_key: form.rapidapi_key,
        geonames_username: form.geonames_username,
        base_url: form.base_url,
        language: form.language,
        house_system: form.house_system,
        theme: form.theme,
        ui_theme_mode: form.ui_theme_mode,
        sidereal: form.sidereal ? '1' : '',
        sidereal_mode: form.sidereal_mode,
        perspective_type: form.perspective_type,
        split_chart: form.split_chart ? '1' : '',
        transparent_background: form.transparent_background ? '1' : '',
        show_house_position_comparison: form.show_house_position_comparison
            ? '1'
            : '',
        show_cusp_position_comparison: form.show_cusp_position_comparison
            ? '1'
            : '',
        show_degree_indicators: form.show_degree_indicators ? '1' : '',
        collapse_birth_form_on_submit: form.collapse_birth_form_on_submit
            ? '1'
            : '',
        custom_title: form.custom_title,
        distribution_method: form.distribution_method,

        active_points: form.active_points, // Array of strings is fine
        active_aspects: activeAspectsPayload, // Array of objects {name, orb}
        custom_distribution_weights: form.distribution_weights, // Object

        synastry_include_house_comparison:
            form.synastry_include_house_comparison ? '1' : '',
        synastry_include_relationship_score:
            form.synastry_include_relationship_score ? '1' : '',
        transit_include_house_comparison: form.transit_include_house_comparison
            ? '1'
            : '',
        returns_include_house_comparison: form.returns_include_house_comparison
            ? '1'
            : '',
        returns_wheel_type: form.returns_wheel_type,
        form_output_mode: form.form_output_mode,
    };
}

export function SettingsPage() {
    const [form, setForm] = useState<SettingsFormState | null>(null);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState<string | null>(null);
    const [dirty, setDirty] = useState(false);

    // Ref to hold the success auto-dismiss timer so we can clear it on unmount
    const successTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    // Load settings on mount
    useEffect(() => {
        let mounted = true;

        const load = async () => {
            try {
                const data = await apiRequest<RawSettingsResponse>(
                    'settings-get',
                    {},
                );
                if (!mounted) return;
                setForm(mapSettingsToForm(data));
            } catch (err) {
                if (!mounted) return;
                setError(
                    err instanceof Error
                        ? err.message
                        : 'Error while loading settings',
                );
            } finally {
                if (!mounted) return;
                setLoading(false);
            }
        };

        load();

        return () => {
            mounted = false;
        };
    }, []);

    // L2: Warn on navigation when there are unsaved changes
    const handleBeforeUnload = useCallback(
        (e: BeforeUnloadEvent) => {
            if (dirty) {
                e.preventDefault();
            }
        },
        [dirty],
    );

    useEffect(() => {
        window.addEventListener('beforeunload', handleBeforeUnload);
        return () => {
            window.removeEventListener('beforeunload', handleBeforeUnload);
        };
    }, [handleBeforeUnload]);

    // Cleanup success timer on unmount
    useEffect(() => {
        return () => {
            if (successTimerRef.current) {
                clearTimeout(successTimerRef.current);
            }
        };
    }, []);

    const updateField = <K extends keyof SettingsFormState>(
        key: K,
        value: SettingsFormState[K],
    ) => {
        setDirty(true);
        setForm((prev) => (prev ? { ...prev, [key]: value } : prev));
    };

    const togglePoint = (point: string, checked: boolean) => {
        if (!form) return;
        let newPoints = [...form.active_points];
        if (checked) {
            if (!newPoints.includes(point)) newPoints.push(point);
        } else {
            newPoints = newPoints.filter((p) => p !== point);
        }
        updateField('active_points', newPoints);
    };

    const toggleAspect = (index: number, checked: boolean) => {
        if (!form) return;
        const newAspects = [...form.active_aspects];
        newAspects[index] = { ...newAspects[index], active: checked };
        updateField('active_aspects', newAspects);
    };

    const updateAspectOrb = (index: number, orb: number) => {
        if (!form) return;
        const newAspects = [...form.active_aspects];
        newAspects[index] = { ...newAspects[index], orb };
        updateField('active_aspects', newAspects);
    };

    const updateWeight = (key: string, value: number) => {
        if (!form) return;
        const newWeights = { ...form.distribution_weights, [key]: value };
        updateField('distribution_weights', newWeights);
    };

    const handleSubmit = async (event: React.FormEvent) => {
        event.preventDefault();
        if (!form) return;

        setSaving(true);
        setError(null);
        setSuccess(null);

        // Clear any existing auto-dismiss timer
        if (successTimerRef.current) {
            clearTimeout(successTimerRef.current);
            successTimerRef.current = null;
        }

        try {
            const payload = buildPayloadFromForm(form);
            const saved = await apiRequest<RawSettingsResponse>(
                'settings-update',
                { settings: payload },
            );
            setForm(mapSettingsToForm(saved));
            setDirty(false);
            setSuccess(t('settingsSaved', 'Settings saved successfully.'));

            // L3: Auto-dismiss success message after 5 seconds
            successTimerRef.current = setTimeout(() => {
                setSuccess(null);
                successTimerRef.current = null;
            }, 5000);
        } catch (err) {
            setError(
                err instanceof Error
                    ? err.message
                    : 'Error while saving settings',
            );
        } finally {
            setSaving(false);
        }
    };

    if (loading || !form) {
        return (
            <div className="flex items-center justify-center py-12">
                <div className="flex items-center gap-3 text-sm text-muted-foreground">
                    <Loader size="md" />
                    <span>{t('loading', 'Loading...')}</span>
                </div>
            </div>
        );
    }

    return (
        <div className="astrologer-settings-page space-y-6">
            {error && (
                <div className="border-destructive/40 bg-destructive/5 text-destructive border rounded-md px-4 py-3 text-sm">
                    {error}
                </div>
            )}

            {success && (
                <div className="border-emerald-500/40 bg-emerald-500/5 text-emerald-700 border rounded-md px-4 py-3 text-sm dark:text-emerald-200">
                    {success}
                </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>
                            {t('apiCredentials', 'API Credentials')}
                        </CardTitle>
                        <CardDescription>
                            {t(
                                'apiCredentialsDescription',
                                'Configure RapidAPI key, GeoNames username and base URL for the Astrologer API.',
                            )}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="rapidapi_key">RapidAPI Key</Label>
                            <Input
                                id="rapidapi_key"
                                type="password"
                                value={form.rapidapi_key}
                                onChange={(e) =>
                                    updateField('rapidapi_key', e.target.value)
                                }
                                placeholder="X-RapidAPI-Key"
                            />
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="geonames_username">
                                    GeoNames Username
                                </Label>
                                <Input
                                    id="geonames_username"
                                    value={form.geonames_username}
                                    onChange={(e) =>
                                        updateField(
                                            'geonames_username',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Optional, for automatic location lookup"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="base_url">Base URL</Label>
                                <Input
                                    id="base_url"
                                    value={form.base_url}
                                    onChange={(e) =>
                                        updateField('base_url', e.target.value)
                                    }
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            {t('chartsConfiguration', 'Charts configuration')}
                        </CardTitle>
                        <CardDescription>
                            {t(
                                'chartsConfigurationDescription',
                                'Default language, theme and rendering options for charts and components.',
                            )}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="space-y-2">
                                <Label htmlFor="language">Language</Label>
                                <select
                                    id="language"
                                    className={cn(
                                        'border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                    )}
                                    value={form.language}
                                    onChange={(e) =>
                                        updateField('language', e.target.value)
                                    }
                                >
                                    <option value="EN">English</option>
                                    <option value="IT">Italian</option>
                                    <option value="FR">French</option>
                                    <option value="ES">Spanish</option>
                                    <option value="PT">Portuguese</option>
                                    <option value="DE">German</option>
                                    <option value="RU">Russian</option>
                                    <option value="TR">Turkish</option>
                                    <option value="CN">Chinese</option>
                                    <option value="HI">Hindi</option>
                                </select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="house_system">
                                    House system
                                </Label>
                                <select
                                    id="house_system"
                                    className={cn(
                                        'border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                    )}
                                    value={form.house_system}
                                    onChange={(e) =>
                                        updateField(
                                            'house_system',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="P">Placidus</option>
                                    <option value="K">Koch</option>
                                    <option value="O">Porphyrius</option>
                                    <option value="R">Regiomontanus</option>
                                    <option value="C">Campanus</option>
                                    <option value="E">Equal</option>
                                    <option value="W">Whole Sign</option>
                                    <option value="M">Morinus</option>
                                </select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="theme">Chart theme</Label>
                                <select
                                    id="theme"
                                    className={cn(
                                        'border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                    )}
                                    value={form.theme}
                                    onChange={(e) =>
                                        updateField('theme', e.target.value)
                                    }
                                >
                                    <option value="classic">Classic</option>
                                    <option value="light">Light</option>
                                    <option value="dark">Dark</option>
                                    <option value="dark-high-contrast">
                                        Dark High Contrast
                                    </option>
                                    <option value="strawberry">
                                        Strawberry
                                    </option>
                                    <option value="black-and-white">
                                        Black and White
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="space-y-2">
                                <Label htmlFor="ui_theme_mode">
                                    UI theme (components)
                                </Label>
                                <select
                                    id="ui_theme_mode"
                                    className={cn(
                                        'border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                    )}
                                    value={form.ui_theme_mode}
                                    onChange={(e) =>
                                        updateField(
                                            'ui_theme_mode',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="light">Light</option>
                                    <option value="dark">Dark</option>
                                </select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="perspective_type">
                                    Perspective
                                </Label>
                                <select
                                    id="perspective_type"
                                    className={cn(
                                        'border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                    )}
                                    value={form.perspective_type}
                                    onChange={(e) =>
                                        updateField(
                                            'perspective_type',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="Apparent Geocentric">
                                        Apparent Geocentric
                                    </option>
                                    <option value="Heliocentric">
                                        Heliocentric
                                    </option>
                                </select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="sidereal_mode">
                                    Sidereal mode
                                </Label>
                                <select
                                    id="sidereal_mode"
                                    className={cn(
                                        'border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                    )}
                                    value={form.sidereal_mode}
                                    onChange={(e) =>
                                        updateField(
                                            'sidereal_mode',
                                            e.target.value,
                                        )
                                    }
                                    disabled={!form.sidereal}
                                >
                                    <option value="LAHIRI">Lahiri</option>
                                    <option value="FAGAN_BRADLEY">
                                        Fagan/Bradley
                                    </option>
                                    <option value="RAMAN">Raman</option>
                                    <option value="KRISHNAMURTI">
                                        Krishnamurti (KP)
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="custom_title">
                                    Custom chart title
                                </Label>
                                <Input
                                    id="custom_title"
                                    value={form.custom_title}
                                    onChange={(e) =>
                                        updateField(
                                            'custom_title',
                                            e.target.value,
                                        )
                                    }
                                    placeholder={t(
                                        'customTitlePlaceholder',
                                        'Optional default title (max 40 characters)',
                                    )}
                                />
                            </div>
                        </div>

                        <div className="grid gap-3 md:grid-cols-2">
                            <ToggleRow
                                id="sidereal"
                                label={t('siderealZodiac', 'Sidereal zodiac')}
                                description={t(
                                    'siderealZodiacDescription',
                                    'Use the sidereal zodiac instead of the tropical one.',
                                )}
                                checked={form.sidereal}
                                onChange={(checked) =>
                                    updateField('sidereal', checked)
                                }
                            />

                            <ToggleRow
                                id="split_chart"
                                label={t('splitChart', 'Split chart / grid')}
                                description={t(
                                    'splitChartDescription',
                                    'Returns wheel and grid separately when supported.',
                                )}
                                checked={form.split_chart}
                                onChange={(checked) =>
                                    updateField('split_chart', checked)
                                }
                            />

                            <ToggleRow
                                id="transparent_background"
                                label={t(
                                    'transparentBackground',
                                    'Transparent background',
                                )}
                                description={t(
                                    'transparentBackgroundDescription',
                                    'Use a transparent background in SVG charts.',
                                )}
                                checked={form.transparent_background}
                                onChange={(checked) =>
                                    updateField(
                                        'transparent_background',
                                        checked,
                                    )
                                }
                            />

                            <ToggleRow
                                id="show_house_position_comparison"
                                label={t(
                                    'houseComparisonTable',
                                    'House comparison table',
                                )}
                                description={t(
                                    'houseComparisonTableDescription',
                                    'Show the house comparison table when available.',
                                )}
                                checked={form.show_house_position_comparison}
                                onChange={(checked) =>
                                    updateField(
                                        'show_house_position_comparison',
                                        checked,
                                    )
                                }
                            />

                            <ToggleRow
                                id="show_cusp_position_comparison"
                                label={t(
                                    'cuspComparisonTable',
                                    'Cusp comparison table',
                                )}
                                description={t(
                                    'cuspComparisonTableDescription',
                                    'Show the cusp comparison table for dual charts.',
                                )}
                                checked={form.show_cusp_position_comparison}
                                onChange={(checked) =>
                                    updateField(
                                        'show_cusp_position_comparison',
                                        checked,
                                    )
                                }
                            />

                            <ToggleRow
                                id="show_degree_indicators"
                                label={t(
                                    'degreeIndicators',
                                    'Degree indicators',
                                )}
                                description={t(
                                    'degreeIndicatorsDescription',
                                    'Show radial lines and degree markings on the wheel.',
                                )}
                                checked={form.show_degree_indicators}
                                onChange={(checked) =>
                                    updateField(
                                        'show_degree_indicators',
                                        checked,
                                    )
                                }
                            />

                            <ToggleRow
                                id="collapse_birth_form_on_submit"
                                label={t(
                                    'collapseBirthFormOnSubmit',
                                    'Collapse birth form after calculation',
                                )}
                                description={t(
                                    'collapseBirthFormOnSubmitDescription',
                                    'Hide the birth data form after the natal chart has been calculated (results remain visible).',
                                )}
                                checked={form.collapse_birth_form_on_submit}
                                onChange={(checked) =>
                                    updateField(
                                        'collapse_birth_form_on_submit',
                                        checked,
                                    )
                                }
                            />
                        </div>

                        {/* Form Output Mode */}
                        <div className="space-y-2">
                            <Label htmlFor="form_output_mode">
                                {t('formOutputMode', 'Form output mode')}
                            </Label>
                            <select
                                id="form_output_mode"
                                className={cn(
                                    'border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none',
                                    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                )}
                                value={form.form_output_mode}
                                onChange={(e) =>
                                    updateField(
                                        'form_output_mode',
                                        e.target.value,
                                    )
                                }
                            >
                                <option value="inline">
                                    {t(
                                        'outputInline',
                                        'Inline (chart and data below form)',
                                    )}
                                </option>
                                <option value="separated">
                                    {t(
                                        'outputSeparated',
                                        'Separated (use separate blocks for outputs)',
                                    )}
                                </option>
                            </select>
                            <p className="text-muted-foreground text-xs">
                                {t(
                                    'formOutputModeDescription',
                                    'In "Separated" mode, output blocks (chart, aspects, elements) are invisible until the form is submitted. Place them as separate Gutenberg blocks.',
                                )}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                {/* Computation Configuration */}
                <Card>
                    <CardHeader>
                        <CardTitle>
                            {t(
                                'computationConfiguration',
                                'Computation configuration',
                            )}
                        </CardTitle>
                        <CardDescription>
                            {t(
                                'computationConfigurationDescription',
                                'Points, aspects and distributions used for advanced computations.',
                            )}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-8">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="distribution_method">
                                    Distribution method
                                </Label>
                                <select
                                    id="distribution_method"
                                    className={cn(
                                        'border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                    )}
                                    value={form.distribution_method}
                                    onChange={(e) =>
                                        updateField(
                                            'distribution_method',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="weighted">Weighted</option>
                                    <option value="pure_count">
                                        Pure count
                                    </option>
                                </select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="returns_wheel_type">
                                    Returns: wheel type
                                </Label>
                                <select
                                    id="returns_wheel_type"
                                    className={cn(
                                        'border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                    )}
                                    value={form.returns_wheel_type}
                                    onChange={(e) =>
                                        updateField(
                                            'returns_wheel_type',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="dual">
                                        Dual (natal + return)
                                    </option>
                                    <option value="single">
                                        Single (return only)
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <ToggleRow
                                id="synastry_include_house_comparison"
                                label={t(
                                    'synastryIncludeHouseComparison',
                                    'Synastry: include house comparison',
                                )}
                                description={t(
                                    'synastryIncludeHouseComparisonDescription',
                                    'Include house comparison data in synastry calculations.',
                                )}
                                checked={form.synastry_include_house_comparison}
                                onChange={(checked) =>
                                    updateField(
                                        'synastry_include_house_comparison',
                                        checked,
                                    )
                                }
                            />

                            <ToggleRow
                                id="synastry_include_relationship_score"
                                label={t(
                                    'synastryIncludeRelationshipScore',
                                    'Synastry: include relationship score',
                                )}
                                description={t(
                                    'synastryIncludeRelationshipScoreDescription',
                                    'Include compatibility score details in synastry calculations.',
                                )}
                                checked={
                                    form.synastry_include_relationship_score
                                }
                                onChange={(checked) =>
                                    updateField(
                                        'synastry_include_relationship_score',
                                        checked,
                                    )
                                }
                            />

                            <ToggleRow
                                id="transit_include_house_comparison"
                                label={t(
                                    'transitIncludeHouseComparison',
                                    'Transits: include house comparison',
                                )}
                                description={t(
                                    'transitIncludeHouseComparisonDescription',
                                    'Include house comparison data in transit calculations.',
                                )}
                                checked={form.transit_include_house_comparison}
                                onChange={(checked) =>
                                    updateField(
                                        'transit_include_house_comparison',
                                        checked,
                                    )
                                }
                            />

                            <ToggleRow
                                id="returns_include_house_comparison"
                                label={t(
                                    'returnsIncludeHouseComparison',
                                    'Returns: include house comparison',
                                )}
                                description={t(
                                    'returnsIncludeHouseComparisonDescription',
                                    'Include house comparison data in solar/lunar return calculations.',
                                )}
                                checked={form.returns_include_house_comparison}
                                onChange={(checked) =>
                                    updateField(
                                        'returns_include_house_comparison',
                                        checked,
                                    )
                                }
                            />
                        </div>

                        {/* Active Points Grid */}
                        <div className="space-y-3">
                            <div className="flex items-center justify-between">
                                <Label>Active points</Label>
                                <span className="text-xs text-muted-foreground">
                                    {t(
                                        'activePointsDescription',
                                        'Select the planets and points to include in calculations.',
                                    )}
                                </span>
                            </div>
                            <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 p-4 border rounded-md bg-muted/20">
                                {ASTROLOGICAL_POINTS.map((point) => (
                                    <div
                                        key={point}
                                        className="flex items-center gap-2"
                                    >
                                        <Checkbox
                                            id={`point-${point}`}
                                            checked={form.active_points.includes(
                                                point,
                                            )}
                                            onCheckedChange={(checked) =>
                                                togglePoint(point, !!checked)
                                            }
                                        />
                                        <label
                                            htmlFor={`point-${point}`}
                                            className="text-sm cursor-pointer select-none"
                                        >
                                            {formatEnumLabel(point)}
                                        </label>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Active Aspects List */}
                        <div className="space-y-3">
                            <div className="flex items-center justify-between">
                                <Label>Active aspects</Label>
                                <span className="text-xs text-muted-foreground">
                                    {t(
                                        'activeAspectsDescription',
                                        'Enable aspects and define their orb (tolerance).',
                                    )}
                                </span>
                            </div>
                            <div className="border rounded-md divide-y overflow-hidden max-h-[300px] overflow-y-auto">
                                {form.active_aspects.map((aspect, index) => (
                                    <div
                                        key={aspect.name}
                                        className="flex items-center justify-between p-3 bg-card hover:bg-muted/30 transition-colors"
                                    >
                                        <div className="flex items-center gap-3">
                                            <Switch
                                                id={`aspect-${aspect.name}`}
                                                checked={aspect.active}
                                                onCheckedChange={(checked) =>
                                                    toggleAspect(index, checked)
                                                }
                                            />
                                            <label
                                                htmlFor={`aspect-${aspect.name}`}
                                                className="font-medium capitalize cursor-pointer"
                                            >
                                                {formatEnumLabel(aspect.name)}
                                            </label>
                                        </div>
                                        {aspect.active && (
                                            <div className="flex items-center gap-2">
                                                <span className="text-xs text-muted-foreground">
                                                    Orb:
                                                </span>
                                                <Input
                                                    type="number"
                                                    className="w-20 h-8 text-right"
                                                    step="0.1"
                                                    min="0"
                                                    max="15"
                                                    value={aspect.orb}
                                                    onChange={(e) =>
                                                        updateAspectOrb(
                                                            index,
                                                            Number(
                                                                e.target.value,
                                                            ),
                                                        )
                                                    }
                                                />
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Distribution Weights */}
                        <div className="space-y-3">
                            <div className="flex items-center justify-between">
                                <Label>Distribution weights</Label>
                                <span className="text-xs text-muted-foreground">
                                    {t(
                                        'distributionWeightsDescription',
                                        'Customize weights for chart distribution calculations.',
                                    )}
                                </span>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4 border rounded-md bg-muted/10">
                                {Object.entries(form.distribution_weights).map(
                                    ([key, value]) => (
                                        <div key={key} className="space-y-1.5">
                                            <div className="flex justify-between items-center text-xs">
                                                <span className="capitalize font-medium">
                                                    {key}
                                                </span>
                                                <span className="text-muted-foreground">
                                                    {value}
                                                </span>
                                            </div>
                                            <Input
                                                type="number"
                                                step="0.5"
                                                min="0"
                                                value={value}
                                                className="h-8"
                                                onChange={(e) =>
                                                    updateWeight(
                                                        key,
                                                        Number(e.target.value),
                                                    )
                                                }
                                            />
                                        </div>
                                    ),
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('reference', 'Reference')}</CardTitle>
                        <CardDescription>
                            {t(
                                'referenceDescription',
                                'Shortcodes and REST endpoints exposed by the plugin.',
                            )}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div className="space-y-2">
                            <div className="text-sm font-medium">
                                {t(
                                    'availableShortcodes',
                                    'Available shortcodes',
                                )}
                            </div>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>
                                            {t('shortcode', 'Shortcode')}
                                        </TableHead>
                                        <TableHead>
                                            {t('description', 'Description')}
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow>
                                        <TableCell>
                                            <code>
                                                [astrologer_natal_chart]
                                            </code>
                                        </TableCell>
                                        <TableCell>
                                            {t(
                                                'shortcodeNatalChart',
                                                'Displays the natal chart.',
                                            )}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>
                                            <code>
                                                [astrologer_aspects_table]
                                            </code>
                                        </TableCell>
                                        <TableCell>
                                            {t(
                                                'shortcodeAspectsTable',
                                                'Displays the aspects table.',
                                            )}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>
                                            <code>
                                                [astrologer_elements_chart]
                                            </code>
                                        </TableCell>
                                        <TableCell>
                                            {t(
                                                'shortcodeElementsChart',
                                                'Displays the elements distribution.',
                                            )}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>
                                            <code>
                                                [astrologer_modalities_chart]
                                            </code>
                                        </TableCell>
                                        <TableCell>
                                            {t(
                                                'shortcodeModalitiesChart',
                                                'Displays the modalities distribution.',
                                            )}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>
                                            <code>[astrologer_birth_form]</code>
                                        </TableCell>
                                        <TableCell>
                                            {t(
                                                'shortcodeBirthForm',
                                                'Displays the complete birth data input form.',
                                            )}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div className="space-y-2">
                            <div className="text-sm font-medium">
                                {t('restApiEndpoints', 'REST API endpoints')}
                            </div>
                            <ul className="text-muted-foreground text-xs space-y-1">
                                <li>
                                    <code>
                                        POST /wp-json/astrologer/v1/natal-chart
                                    </code>
                                </li>
                                <li>
                                    <code>
                                        POST
                                        /wp-json/astrologer/v1/natal-chart-data
                                    </code>
                                </li>
                                <li>
                                    <code>
                                        POST
                                        /wp-json/astrologer/v1/synastry-chart
                                    </code>
                                </li>
                                <li>
                                    <code>
                                        POST
                                        /wp-json/astrologer/v1/transit-chart
                                    </code>
                                </li>
                            </ul>
                        </div>
                    </CardContent>
                </Card>

                <div className="flex justify-end">
                    <Button
                        type="submit"
                        disabled={saving}
                        className="min-w-[160px]"
                    >
                        {saving ? (
                            <span className="inline-flex items-center gap-2">
                                <Loader size="sm" />
                                <span>{t('saving', 'Saving...')}</span>
                            </span>
                        ) : (
                            t('saveSettings', 'Save settings')
                        )}
                    </Button>
                </div>
            </form>
        </div>
    );
}

interface ToggleRowProps {
    id: string;
    label: string;
    description?: string;
    checked: boolean;
    onChange: (checked: boolean) => void;
}

function ToggleRow({
    id,
    label,
    description,
    checked,
    onChange,
}: ToggleRowProps) {
    return (
        <div className="flex items-center justify-between space-x-2 rounded-lg border p-4">
            <div className="space-y-0.5">
                <Label htmlFor={id} className="text-base">
                    {label}
                </Label>
                {description && (
                    <p className="text-sm text-muted-foreground">
                        {description}
                    </p>
                )}
            </div>
            <Switch id={id} checked={checked} onCheckedChange={onChange} />
        </div>
    );
}
