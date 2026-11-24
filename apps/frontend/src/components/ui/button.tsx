import React from 'react';
import { cn } from '@/lib/utils';

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'default' | 'secondary' | 'outline' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg' | 'icon';
}

export const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant = 'default', size = 'md', children, disabled, ...props }, ref) => {
    const baseStyles = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed select-none';

    const variants = {
      default: 'bg-[#37352F] dark:bg-[#2EAADC] text-white hover:bg-black dark:hover:bg-[#2596C4] shadow-sm',
      secondary: 'bg-notion-sidebar dark:bg-[#252525] text-notion-text dark:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] border border-notion-border dark:border-[#333333]',
      outline: 'bg-transparent border border-notion-border dark:border-[#333333] text-notion-text dark:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C]',
      ghost: 'bg-transparent text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C]',
      danger: 'bg-red-600 text-white hover:bg-red-700 shadow-sm',
    };

    const sizes = {
      sm: 'text-xs px-2.5 py-1',
      md: 'text-xs sm:text-sm px-3.5 py-2',
      lg: 'text-sm sm:text-base px-4 py-2.5',
      icon: 'p-1.5 rounded-md',
    };

    return (
      <button
        ref={ref}
        disabled={disabled}
        className={cn(baseStyles, variants[variant], sizes[size], className)}
        {...props}
      >
        {children}
      </button>
    );
  }
);
Button.displayName = 'Button';

