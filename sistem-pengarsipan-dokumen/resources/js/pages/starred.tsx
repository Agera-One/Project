import AppLayout from '@/layouts/app-layout'
import { starred } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/react'
import ListLayout from '@/components/list-layout'
import { DocumentData } from '@/types/document'
import { mapDocumentsToListItems } from '@/mappers/document.mapper'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Starred',
        href: starred().url,
    },
]

interface Props {
    documents: DocumentData[]
}

export default function Starred({ documents }: Props) {
    const files = mapDocumentsToListItems(documents)

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Starred" />
            <div className="flex h-full flex-1 flex-col overflow-x-auto p-6">
                <ListLayout files={files} />
            </div>
        </AppLayout>
    )
}
