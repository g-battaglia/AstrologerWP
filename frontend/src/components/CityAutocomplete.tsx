/**
 * CityAutocomplete — Typeahead search for cities via the GeoNames proxy.
 *
 * Debounces user input (300ms), queries `/astrologer/v1/city-search`,
 * and displays a dropdown of matching cities. Selecting a city fills
 * city, country code, latitude, longitude, and timezone.
 */

import { useState, useRef, useEffect, useCallback } from 'react';
import { fetchCitySearch, type CitySearchResult } from '@/lib/api';
import { Input } from './ui/Input';
import { cn } from '@/lib/utils';

interface CityAutocompleteProps {
    /** Current value of the city input */
    value: string;
    /** Called when the text input changes */
    onInputChange: (value: string) => void;
    /** Called when the user selects a city from the dropdown */
    onSelect: (city: CitySearchResult) => void;
    /** HTML id for the input */
    id?: string;
    /** Whether the field has a validation error */
    hasError?: boolean;
}

export function CityAutocomplete({
    value,
    onInputChange,
    onSelect,
    id,
    hasError,
}: CityAutocompleteProps) {
    const [results, setResults] = useState<CitySearchResult[]>([]);
    const [isOpen, setIsOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [activeIndex, setActiveIndex] = useState(-1);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const containerRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    // Close dropdown on outside click
    useEffect(() => {
        const handleClickOutside = (e: MouseEvent) => {
            if (
                containerRef.current &&
                !containerRef.current.contains(e.target as Node)
            ) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () =>
            document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const search = useCallback(async (query: string) => {
        if (query.trim().length < 2) {
            setResults([]);
            setIsOpen(false);
            return;
        }

        setLoading(true);
        try {
            const data = await fetchCitySearch(query);
            setResults(data);
            setIsOpen(data.length > 0);
            setActiveIndex(-1);
        } catch {
            setResults([]);
            setIsOpen(false);
        } finally {
            setLoading(false);
        }
    }, []);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const val = e.target.value;
        onInputChange(val);

        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => search(val), 300);
    };

    const handleSelect = (city: CitySearchResult) => {
        onSelect(city);
        setIsOpen(false);
        setResults([]);
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (!isOpen || results.length === 0) return;

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                setActiveIndex((prev) =>
                    prev < results.length - 1 ? prev + 1 : 0,
                );
                break;
            case 'ArrowUp':
                e.preventDefault();
                setActiveIndex((prev) =>
                    prev > 0 ? prev - 1 : results.length - 1,
                );
                break;
            case 'Enter':
                e.preventDefault();
                if (activeIndex >= 0 && activeIndex < results.length) {
                    handleSelect(results[activeIndex]);
                }
                break;
            case 'Escape':
                setIsOpen(false);
                break;
        }
    };

    // Clean up debounce on unmount
    useEffect(() => {
        return () => {
            if (debounceRef.current) clearTimeout(debounceRef.current);
        };
    }, []);

    return (
        <div ref={containerRef} className="relative">
            <Input
                ref={inputRef}
                id={id}
                value={value}
                onChange={handleChange}
                onKeyDown={handleKeyDown}
                onFocus={() => {
                    if (results.length > 0) setIsOpen(true);
                }}
                aria-invalid={hasError}
                aria-expanded={isOpen}
                aria-autocomplete="list"
                aria-controls={id ? `${id}-listbox` : undefined}
                aria-activedescendant={
                    activeIndex >= 0 && id
                        ? `${id}-option-${activeIndex}`
                        : undefined
                }
                autoComplete="off"
                required
            />

            {loading && (
                <div className="absolute right-2 top-1/2 -translate-y-1/2">
                    <div className="h-4 w-4 animate-spin rounded-full border-2 border-muted-foreground border-t-transparent" />
                </div>
            )}

            {isOpen && results.length > 0 && (
                <ul
                    id={id ? `${id}-listbox` : undefined}
                    role="listbox"
                    className="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border bg-popover text-popover-foreground shadow-md"
                >
                    {results.map((city, index) => (
                        <li
                            key={`${city.name}-${city.latitude}-${city.longitude}-${index}`}
                            id={id ? `${id}-option-${index}` : undefined}
                            role="option"
                            aria-selected={index === activeIndex}
                            className={cn(
                                'cursor-pointer px-3 py-2 text-sm',
                                index === activeIndex &&
                                    'bg-accent text-accent-foreground',
                            )}
                            onMouseDown={(e) => {
                                e.preventDefault();
                                handleSelect(city);
                            }}
                            onMouseEnter={() => setActiveIndex(index)}
                        >
                            <span className="font-medium">{city.name}</span>
                            {city.admin && (
                                <span className="text-muted-foreground">
                                    , {city.admin}
                                </span>
                            )}
                            <span className="text-muted-foreground">
                                {' '}
                                ({city.country})
                            </span>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
