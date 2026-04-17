/**
 * Loader component - Loading spinner.
 */

import { cn } from '@/lib/utils';

interface LoaderProps {
    /** Additional CSS class */
    className?: string;
    /** Spinner size */
    size?: 'sm' | 'md' | 'lg';
}

/**
 * Loader component - shadcn/ui style.
 *
 * Responsive loader with consistent styles.
 */
export function Loader({ className, size = 'md' }: LoaderProps) {
    const sizeClasses = {
        sm: 'w-4 h-4 border-2',
        md: 'w-6 h-6 border-2',
        lg: 'w-8 h-8 border-3',
    };

    return (
        <div
            className={cn(
                'animate-spin rounded-full border-solid border-current border-t-transparent',
                sizeClasses[size],
                className,
            )}
            role="status"
            aria-label="Loading"
        >
            <span className="sr-only">Loading...</span>
        </div>
    );
}
