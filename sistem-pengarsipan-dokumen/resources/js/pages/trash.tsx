import AppLayout from '@/layouts/app-layout';
import { trash } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import ListLayout from '@/components/list-layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Trash',
        href: trash().url,
    },
];

export default function Trash() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Trash" />
            <div className="flex h-full flex-1 flex-col overflow-x-auto p-6">
                <ListLayout />
            </div>
        </AppLayout>
    );
}
