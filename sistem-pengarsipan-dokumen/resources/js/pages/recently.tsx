import AppLayout from '@/layouts/app-layout'
import { recently } from '@/routes'
import { Head } from '@inertiajs/react'
import ListLayout from '@/components/list-layout'
import { DocumentData } from '@/types/document'
import { mapDocumentsToListItems } from '@/mappers/document.mapper'

interface Props {
    documents: DocumentData[]
}

export default function Recently({ documents }: Props) {
    const files = mapDocumentsToListItems(documents)

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
