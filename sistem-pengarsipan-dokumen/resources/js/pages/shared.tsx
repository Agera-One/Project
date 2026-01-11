import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { shared } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import ListLayout from '@/components/list-layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Shared',
        href: shared().url,
    },
];

export default function Shared() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Shared" />
            <div className="flex h-full flex-1 flex-col overflow-x-auto p-6">
                <ListLayout />
            </div>
        </AppLayout>
    );
}
