import AppLayout from '@/layouts/app-layout'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head, usePage } from '@inertiajs/react'
import DocumentExtension from '@/components/document-extension'
import { DocumentStatus } from '@/components/document-status'
import StorageDetail from '@/components/storage-detail'
import type {
    DocumentStats,
    ExtensionStat,
    StorageStat,
} from '@/types/dashboard'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
]

interface PageProps {
    documentStats: DocumentStats
    extensionStats: ExtensionStat[]
    storageStats: StorageStat
}

export default function Dashboard() {
    const {
        documentStats,
        extensionStats,
        storageStats,
    } = usePage<PageProps>().props

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            {/* STATUS */}
            <div className="mt-6 grid grid-cols-1 gap-6 px-6 md:grid-cols-2 xl:grid-cols-3">
                <DocumentStatus
                    stats={documentStats}
                />
            </div>

            {/* EXTENSION + STORAGE */}
            <div className="grid grid-cols-1 gap-6 p-6 xl:grid-cols-3">
                <DocumentExtension data={extensionStats} />
                <StorageDetail data={storageStats} />
            </div>
        </AppLayout>
    )
}
