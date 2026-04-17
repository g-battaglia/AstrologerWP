// Type definitions for WordPress globals used in blocks
// Minimal set of types to avoid TS errors without installing full @wordpress/types

declare global {
    interface Window {
        wp: any;
    }
}

export const wp = window.wp;

export const { registerBlockType } = wp.blocks;
export const { InspectorControls, useBlockProps } = wp.blockEditor;
export const {
    PanelBody,
    TextControl,
    NumberControl,
    ToggleControl,
    SelectControl,
} = wp.components;
export const { createElement, Fragment, useState, useEffect } = wp.element;
export const { __ } = wp.i18n;

// Icons
export const AstroIcon = (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        width="24"
        height="24"
    >
        <path
            d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm1-13h-2v6l5.25 3.15.75-1.23-4-2.42z"
            fill="currentColor"
        />
    </svg>
);

export const StarIcon = (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
    >
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
    </svg>
);

export const TableIcon = (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
    >
        <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"></path>
    </svg>
);

export const PieIcon = (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
    >
        <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
        <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
    </svg>
);

export const HeartIcon = (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
    >
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
    </svg>
);

export const ClockIcon = (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
    >
        <circle cx="12" cy="12" r="10"></circle>
        <polyline points="12 6 12 12 16 14"></polyline>
    </svg>
);

// Common attributes for charts
export const birthDataAttributes = {
    name: { type: 'string', default: 'Example Subject' },
    year: { type: 'number', default: 1988 },
    month: { type: 'number', default: 3 },
    day: { type: 'number', default: 15 },
    hour: { type: 'number', default: 14 },
    minute: { type: 'number', default: 30 },
    latitude: { type: 'number', default: 51.5074 }, // London
    longitude: { type: 'number', default: -0.1278 },
    timezone: { type: 'string', default: 'Europe/London' },
    city: { type: 'string', default: 'London' },
    nation: { type: 'string', default: 'GB' },
};

// Reusable Inspector for a single subject with prefix-based attributes
export const SubjectInspector = ({
    title,
    prefix,
    attributes,
    setAttributes,
}: any) => {
    const getAttr = (key: string) => attributes[`${prefix}${key}`];
    const setAttr = (key: string, val: any) =>
        setAttributes({ [`${prefix}${key}`]: val });

    return (
        <PanelBody title={title} initialOpen={false}>
            <TextControl
                label="Name"
                value={getAttr('name')}
                onChange={(val: string) => setAttr('name', val)}
            />
            <NumberControl
                label="Year"
                value={getAttr('year')}
                onChange={(val: number) => setAttr('year', Number(val))}
                min={100}
                max={3000}
            />
            <NumberControl
                label="Month"
                value={getAttr('month')}
                onChange={(val: number) => setAttr('month', Number(val))}
                min={1}
                max={12}
            />
            <NumberControl
                label="Day"
                value={getAttr('day')}
                onChange={(val: number) => setAttr('day', Number(val))}
                min={1}
                max={31}
            />
            <NumberControl
                label="Hour"
                value={getAttr('hour')}
                onChange={(val: number) => setAttr('hour', Number(val))}
                min={0}
                max={23}
            />
            <NumberControl
                label="Minutes"
                value={getAttr('minute')}
                onChange={(val: number) => setAttr('minute', Number(val))}
                min={0}
                max={59}
            />
            <TextControl
                label="City"
                value={getAttr('city')}
                onChange={(val: string) => setAttr('city', val)}
            />
            <TextControl
                label="Country"
                value={getAttr('nation')}
                onChange={(val: string) => setAttr('nation', val)}
            />
            <NumberControl
                label="Lat"
                value={getAttr('latitude')}
                onChange={(val: number) => setAttr('latitude', Number(val))}
                step={0.0001}
            />
            <NumberControl
                label="Lon"
                value={getAttr('longitude')}
                onChange={(val: number) => setAttr('longitude', Number(val))}
                step={0.0001}
            />
            <TextControl
                label="Time zone"
                value={getAttr('timezone')}
                onChange={(val: string) => setAttr('timezone', val)}
            />
        </PanelBody>
    );
};

// Reusable Inspector for a single subject (non-prefixed attributes)
export const BirthDataInspector = ({ attributes, setAttributes }: any) => {
    return (
        <Fragment>
            <InspectorControls>
                <PanelBody title="Birth Data" initialOpen={true}>
                    <TextControl
                        label="Name"
                        value={attributes.name}
                        onChange={(value: string) =>
                            setAttributes({ name: value })
                        }
                    />
                    <NumberControl
                        label="Year"
                        value={attributes.year}
                        onChange={(value: number) =>
                            setAttributes({ year: Number(value) })
                        }
                        min={100}
                        max={3000}
                    />
                    <NumberControl
                        label="Month"
                        value={attributes.month}
                        onChange={(value: number) =>
                            setAttributes({ month: Number(value) })
                        }
                        min={1}
                        max={12}
                    />
                    <NumberControl
                        label="Day"
                        value={attributes.day}
                        onChange={(value: number) =>
                            setAttributes({ day: Number(value) })
                        }
                        min={1}
                        max={31}
                    />
                    <NumberControl
                        label="Hour"
                        value={attributes.hour}
                        onChange={(value: number) =>
                            setAttributes({ hour: Number(value) })
                        }
                        min={0}
                        max={23}
                    />
                    <NumberControl
                        label="Minutes"
                        value={attributes.minute}
                        onChange={(value: number) =>
                            setAttributes({ minute: Number(value) })
                        }
                        min={0}
                        max={59}
                    />
                </PanelBody>
                <PanelBody title="Location" initialOpen={false}>
                    <TextControl
                        label="City"
                        value={attributes.city}
                        onChange={(value: string) =>
                            setAttributes({ city: value })
                        }
                    />
                    <TextControl
                        label="Country (code)"
                        value={attributes.nation}
                        onChange={(value: string) =>
                            setAttributes({ nation: value })
                        }
                    />
                    <NumberControl
                        label="Latitude"
                        value={attributes.latitude}
                        onChange={(value: number) =>
                            setAttributes({ latitude: Number(value) })
                        }
                        step={0.0001}
                    />
                    <NumberControl
                        label="Longitude"
                        value={attributes.longitude}
                        onChange={(value: number) =>
                            setAttributes({ longitude: Number(value) })
                        }
                        step={0.0001}
                    />
                    <TextControl
                        label="Time Zone"
                        value={attributes.timezone}
                        onChange={(value: string) =>
                            setAttributes({ timezone: value })
                        }
                    />
                </PanelBody>
            </InspectorControls>
        </Fragment>
    );
};
