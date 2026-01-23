import AppLayout from '@/layouts/app-layout'
import { recently } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/react'
import ListLayout from '@/components/list-layout'
import { DocumentData } from '@/types/document'
import { mapDocumentsToListItems } from '@/mappers/document.mapper'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Recently',
        href: recently().url,
    },
]

interface Props {
    documents: DocumentData[]
}

export default function Recently({ documents }: Props) {
    const files = mapDocumentsToListItems(documents)

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Recently" />
            <div className="flex h-full flex-1 flex-col overflow-x-auto p-6">
                <ListLayout files={files} />
            </div>
        </AppLayout>
    )
}
