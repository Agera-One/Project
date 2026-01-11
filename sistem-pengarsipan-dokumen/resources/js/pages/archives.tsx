import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { archives } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import ListLayout from '@/components/list-layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Archives',
        href: archives().url,
    },
];

export default function Archives() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Archives" />
            <div className="flex h-full flex-1 flex-col overflow-x-auto p-6">
                <ListLayout />
            </div>
        </AppLayout>
    );
}
