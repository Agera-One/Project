import AppLayout from '@/layouts/app-layout'
import { archives } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/react'
import ListLayout from '@/components/list-layout'
import { DocumentListItem } from '@/types/document-list'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Archives',
        href: archives().url,
    },
]

interface Props {
    documents: DocumentListItem[]
}

export default function Archives({ documents }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Archives" />
            <div className="flex h-full flex-1 flex-col overflow-x-auto p-6">
                <ListLayout documents={documents} />
            </div>
        </AppLayout>
    )
}
