import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import DocumentCategory from '@/components/document-category';
import StorageDetail from '@/components/storage-detail';
import RecentFilesList from '@/components/recent-files-list';
import { DocumentStatus } from '@/components/document-status';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

export default function Dashboard() {
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
            <RecentFilesList />
        </AppLayout>
    );
}
