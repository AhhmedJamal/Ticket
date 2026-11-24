import React from 'react';
import { cn } from '@/lib/utils';

export interface AvatarProps extends React.HTMLAttributes<HTMLDivElement> {
  name?: string;
  size?: 'sm' | 'md' | 'lg';
}

export function Avatar({ name = '', size = 'md', className, ...props }: AvatarProps) {
  const initial = name ? name.charAt(0) : 'م';
  const sizeClasses = {
    sm: 'w-6 h-6 text-[10px]',
    md: 'w-8 h-8 text-xs',
    lg: 'w-10 h-10 text-base',
  };

  return (
    <div
      className={cn(
        'rounded-full bg-[#37352F] dark:bg-[#3A3A3A] text-white font-bold flex items-center justify-center shrink-0 select-none shadow-xs',
        sizeClasses[size],
        className
      )}
      {...props}
    >
      {initial}
    </div>
  );
}

