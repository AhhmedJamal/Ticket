'use client';

import React, { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/lib/auth-context';
import { WorkspaceProvider } from '@/lib/workspace-context';
import { ShellProvider, useShell } from '@/lib/shell-context';
import { Sidebar } from '@/components/workspace/Sidebar';
import { Topbar } from '@/components/workspace/Topbar';
import { SearchPaletteModal } from '@/components/workspace/SearchPaletteModal';
import { useTheme } from '@/lib/theme-context';
import { Loader2 } from 'lucide-react';

function DashboardShellInner({ children }: { children: React.ReactNode }) {
    const { isAuthenticated, isLoading } = useAuth();
    const router = useRouter();
    const { toggleTheme } = useTheme();
    const {
        sidebarOpen,
        setSidebarOpen,
        toggleSidebar,
        searchOpen,
        setSearchOpen,
        openSearch,
        overviewOpen,
        toggleOverview,
        currentPage,
    } = useShell();

    // Authentication guard
    useEffect(() => {
        if (!isLoading && !isAuthenticated) {
            router.push('/login');
        }
    }, [isLoading, isAuthenticated, router]);

    // Handle window resize and keyboard shortcuts
    useEffect(() => {
        const handleResize = () => {
            if (window.innerWidth < 768) {
                setSidebarOpen(false);
            } else {
                setSidebarOpen(true);
            }
        };

        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.ctrlKey && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                setSearchOpen(true);
            } else if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'l') {
                e.preventDefault();
                toggleTheme();
            } else if (e.key === '/' && document.activeElement === document.body) {
                e.preventDefault();
                setSearchOpen(true);
            }
        };

        window.addEventListener('resize', handleResize);
        window.addEventListener('keydown', handleKeyDown);

        // Initial check for mobile
        if (window.innerWidth < 768) {
            setSidebarOpen(false);
        }

        return () => {
            window.removeEventListener('resize', handleResize);
            window.removeEventListener('keydown', handleKeyDown);
        };
    }, [setSidebarOpen, setSearchOpen, toggleTheme]);

    if (isLoading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-white dark:bg-[#191919]">
                <Loader2 className="w-8 h-8 animate-spin text-notion-muted" />
            </div>
        );
    }

    if (!isAuthenticated) {
        return null;
    }

    return (
        <div className="h-screen overflow-hidden flex flex-col font-sans antialiased text-notion-text bg-white dark:bg-[#191919] dark:text-[#E3E3E0]">
            <div className="flex-1 flex overflow-hidden relative">
                {/* خلفية إغلاق السايدبار على الموبايل */}
                {sidebarOpen && (
                    <div
                        onClick={() => setSidebarOpen(false)}
                        className="fixed inset-0 bg-black/50 backdrop-blur-[1px] z-30 md:hidden transition-opacity"
                    />
                )}

                {/* الشريط الجانبي (Sidebar) */}
                <aside
                    className={`fixed inset-y-0 right-0 w-64 md:w-60 md:static bg-notion-sidebar dark:bg-[#202020] border-l border-notion-border dark:border-[#2A2A2A] flex flex-col h-full shrink-0 select-none z-40 md:z-20 shadow-2xl md:shadow-none transition-transform duration-200 ease-out ${
                        sidebarOpen ? 'translate-x-0' : 'translate-x-full md:hidden'
                    }`}
                >
                    <Sidebar onOpenSearch={openSearch} />
                </aside>

                {/* منطقة المحتوى الرئيسية */}
                <main className="flex-1 flex flex-col min-w-0 overflow-y-auto bg-white dark:bg-[#191919] relative">
                    <Topbar
                        onToggleSidebar={toggleSidebar}
                        onOpenSearch={openSearch}
                        onToggleOverview={toggleOverview}
                        overviewOpen={overviewOpen}
                        currentPage={currentPage}
                    />

                    <div className="flex-1 px-4 sm:px-8 md:px-12 py-6 md:py-8 max-w-5xl w-full mx-auto">
                        {children}
                    </div>
                </main>
            </div>

            {/* نافذة البحث المنبثقة */}
            <SearchPaletteModal
                isOpen={searchOpen}
                onClose={() => setSearchOpen(false)}
                currentPageId={currentPage?.id}
            />
        </div>
    );
}

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
    return (
        <WorkspaceProvider>
            <ShellProvider>
                <DashboardShellInner>{children}</DashboardShellInner>
            </ShellProvider>
        </WorkspaceProvider>
    );
}

