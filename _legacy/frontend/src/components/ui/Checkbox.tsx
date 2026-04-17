import * as React from 'react';
import { Check } from 'lucide-react';

import { cn } from '@/lib/utils';

function Checkbox({
    className,
    checked,
    onCheckedChange,
    ...props
}: React.ComponentProps<'input'> & {
    onCheckedChange?: (checked: boolean) => void;
}) {
    return (
        <div className="relative flex items-center">
            <input
                type="checkbox"
                className={cn(
                    'peer h-4 w-4 shrink-0 rounded-sm border border-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 appearance-none bg-background checked:bg-primary checked:text-primary-foreground',
                    className
                )}
                checked={checked}
                onChange={(e) => onCheckedChange?.(e.target.checked)}
                {...props}
            />
            <Check
                className="absolute top-0 left-0 h-4 w-4 text-primary-foreground pointer-events-none hidden peer-checked:block"
                strokeWidth={3}
            />
        </div>
    );
}

export { Checkbox };
