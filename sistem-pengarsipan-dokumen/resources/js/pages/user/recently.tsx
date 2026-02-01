import AppLayout from '@/layouts/app-layout'
import { recently } from '@/routes'
import { Head } from '@inertiajs/react'
import ListLayout from '@/components/list-layout'
import { DocumentListItem } from '@/types/document-list';

interface Props {
    documents: DocumentListItem[];
}

export default function Recently({ documents }: Props) {
    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Recently', href: recently().url },
            ]}
        >
            <Head title="Recently" />

            <div className="flex h-full flex-1 flex-col overflow-x-auto p-6">
                <ListLayout documents={documents} />
            </div>
        </AppLayout>
    )
}
