'use client';

import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { Workspace, Page } from '@/types';
import { workspaceService, pageService } from '@/services/api';
import { useAuth } from './auth-context';

interface WorkspaceContextType {
    workspaces: Workspace[];
    currentWorkspace: Workspace | null;
    isLoading: boolean;
    setCurrentWorkspace: (workspace: Workspace | null) => void;
    refreshWorkspaces: () => Promise<void>;
    refreshCurrentWorkspace: () => Promise<void>;
    createPage: (parentId?: number) => Promise<Page | null>;
    deletePage: (pageId: number) => Promise<void>;
    togglePageFavorite: (pageId: number) => Promise<boolean>;
}

const WorkspaceContext = createContext<WorkspaceContextType | undefined>(undefined);

export function WorkspaceProvider({ children }: { children: React.ReactNode }) {
    const { token, isAuthenticated } = useAuth();
    const [workspaces, setWorkspaces] = useState<Workspace[]>([]);
    const [currentWorkspace, setCurrentWorkspace] = useState<Workspace | null>(null);
    const [isLoading, setIsLoading] = useState(true);

    const refreshWorkspaces = useCallback(async () => {
        if (!token) {
            setWorkspaces([]);
            setIsLoading(false);
            return;
        }
        try {
            const list = await workspaceService.getAll();
            setWorkspaces(list);
            if (list.length > 0 && !currentWorkspace) {
                // If there's an active workspace saved in localStorage, restore it
                const savedId = localStorage.getItem('active_workspace_id');
                const matched = savedId ? list.find(w => w.id === Number(savedId)) : null;
                const active = matched || list[0];
                const fullWs = await workspaceService.getById(active.id);
                setCurrentWorkspace(fullWs);
            }
        } catch {
            // handle error
        } finally {
            setIsLoading(false);
        }
    }, [token, currentWorkspace]);

    const refreshCurrentWorkspace = useCallback(async () => {
        if (!currentWorkspace) return;
        try {
            const fullWs = await workspaceService.getById(currentWorkspace.id);
            setCurrentWorkspace(fullWs);
        } catch {
            // ignore
        }
    }, [currentWorkspace]);

    useEffect(() => {
        if (isAuthenticated) {
            refreshWorkspaces();
        } else {
            setWorkspaces([]);
            setCurrentWorkspace(null);
            setIsLoading(false);
        }
    }, [isAuthenticated, refreshWorkspaces]);

    const handleSetCurrentWorkspace = (ws: Workspace | null) => {
        setCurrentWorkspace(ws);
        if (ws) {
            localStorage.setItem('active_workspace_id', String(ws.id));
        } else {
            localStorage.removeItem('active_workspace_id');
        }
    };

    const createPage = async (parentId?: number): Promise<Page | null> => {
        if (!currentWorkspace) return null;
        try {
            const newPage = await pageService.create(currentWorkspace.id, {
                title: 'صفحة جديدة',
                parent_id: parentId,
            });
            await refreshCurrentWorkspace();
            return newPage;
        } catch {
            return null;
        }
    };

    const deletePage = async (pageId: number) => {
        try {
            await pageService.delete(pageId);
            await refreshCurrentWorkspace();
        } catch {
            // ignore
        }
    };

    const togglePageFavorite = async (pageId: number): Promise<boolean> => {
        try {
            const res = await pageService.toggleFavorite(pageId);
            await refreshCurrentWorkspace();
            return res.is_favorite;
        } catch {
            return false;
        }
    };

    return (
        <WorkspaceContext.Provider
            value={{
                workspaces,
                currentWorkspace,
                isLoading,
                setCurrentWorkspace: handleSetCurrentWorkspace,
                refreshWorkspaces,
                refreshCurrentWorkspace,
                createPage,
                deletePage,
                togglePageFavorite,
            }}
        >
            {children}
        </WorkspaceContext.Provider>
    );
}

export function useWorkspace() {
    const context = useContext(WorkspaceContext);
    if (!context) {
        throw new Error('useWorkspace must be used within a WorkspaceProvider');
    }
    return context;
}

