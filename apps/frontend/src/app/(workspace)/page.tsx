'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useWorkspace } from '@/lib/workspace-context';
import { Loader2 } from 'lucide-react';

export default function DashboardRootPage() {
    const { workspaces, isLoading, currentWorkspace } = useWorkspace();
    const router = useRouter();

    useEffect(() => {
        if (!isLoading) {
            if (currentWorkspace) {
                router.replace(`/workspaces/${currentWorkspace.id}`);
            } else if (workspaces.length > 0) {
                router.replace(`/workspaces/${workspaces[0].id}`);
            } else {
                router.replace('/dashboard');
            }
        }
    }, [isLoading, workspaces, currentWorkspace, router]);

    return (
        <div className="min-h-[50vh] flex items-center justify-center">
            <Loader2 className="w-6 h-6 animate-spin text-notion-muted" />
        </div>
    );
}

