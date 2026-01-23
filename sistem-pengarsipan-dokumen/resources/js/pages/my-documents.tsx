import AppLayout from '@/layouts/app-layout'
import { myDocuments } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/react'
import ListLayout from '@/components/list-layout'
import { DocumentData } from '@/types/document'
import { mapDocumentsToListItems } from '@/mappers/document.mapper'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'My Documents',
        href: myDocuments().url,
    },
]

interface Props {
    documents: DocumentData[]
}

export default function MyDocuments({ documents }: Props) {
    const files = mapDocumentsToListItems(documents)

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Documents" />
            <div className="flex h-full flex-1 flex-col overflow-x-auto p-6">
                <ListLayout files={files} />
            </div>
        </AppLayout>
    )
}
