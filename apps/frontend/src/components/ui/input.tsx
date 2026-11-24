import React from 'react';
import { cn } from '@/lib/utils';

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  error?: string;
}

export const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, error, ...props }, ref) => {
    return (
      <div className="w-full">
        <input
          ref={ref}
          className={cn(
            'w-full px-3 py-2 bg-white dark:bg-[#191919] border border-notion-border dark:border-[#2E2E2E] rounded-lg text-sm text-notion-text dark:text-[#E3E3E0] placeholder-notion-muted focus:outline-none focus:ring-2 focus:ring-[#2EAADC] focus:border-transparent transition-all',
            error ? 'border-red-500 focus:ring-red-500' : '',
            className
          )}
          {...props}
        />
        {error && <p className="text-xs text-red-500 mt-1">{error}</p>}
      </div>
    );
  }
);
Input.displayName = 'Input';

