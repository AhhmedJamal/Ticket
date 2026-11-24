'use client';

import React, { createContext, useContext, useState } from 'react';
import { Page } from '@/types';

interface ShellContextType {
    sidebarOpen: boolean;
    setSidebarOpen: (open: boolean) => void;
    toggleSidebar: () => void;
    searchOpen: boolean;
    setSearchOpen: (open: boolean) => void;
    openSearch: () => void;
    overviewOpen: boolean;
    setOverviewOpen: (open: boolean) => void;
    toggleOverview: () => void;
    currentPage: Page | null;
    setCurrentPage: React.Dispatch<React.SetStateAction<Page | null>>;
}

const ShellContext = createContext<ShellContextType | undefined>(undefined);

export function ShellProvider({ children }: { children: React.ReactNode }) {
    const [sidebarOpen, setSidebarOpen] = useState(true);
    const [searchOpen, setSearchOpen] = useState(false);
    const [overviewOpen, setOverviewOpen] = useState(false);
    const [currentPage, setCurrentPage] = useState<Page | null>(null);

    const toggleSidebar = () => setSidebarOpen((prev) => !prev);
    const openSearch = () => setSearchOpen(true);
    const toggleOverview = () => setOverviewOpen((prev) => !prev);

    return (
        <ShellContext.Provider
            value={{
                sidebarOpen,
                setSidebarOpen,
                toggleSidebar,
                searchOpen,
                setSearchOpen,
                openSearch,
                overviewOpen,
                setOverviewOpen,
                toggleOverview,
                currentPage,
                setCurrentPage,
            }}
        >
            {children}
        </ShellContext.Provider>
    );
}

export function useShell() {
    const context = useContext(ShellContext);
    if (!context) {
        throw new Error('useShell must be used within a ShellProvider');
    }
    return context;
}
