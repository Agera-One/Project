import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { recently } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import ListLayout from '@/components/list-layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Recently',
        href: recently().url,
    },
];

export default function Recently() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Recently" />
            <div className="flex h-full flex-1 flex-col overflow-x-auto p-6">
                <ListLayout />
            </div>
        </AppLayout>
    );
}
