import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import DocumentCategory from '@/components/document-category';
import StorageDetail from '@/components/storage-detail';
import RecentDocumentsList from '@/components/recent-documents-list';
import { DocumentStatus } from '@/components/document-status';
import { DocumentData } from '@/types/document'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface DashboardProps {
    documents: DocumentData[]
}

export default function Dashboard({ documents }: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 px-6 mt-6 rounded-lg">
                <DocumentStatus />
            </div>
            <div className="grid grid-cols-1 xl:grid-cols-3 gap-6 p-6">
                <DocumentCategory />
                <StorageDetail />
            </div>
            <RecentDocumentsList documents={documents} />
        </AppLayout>
    );
}
