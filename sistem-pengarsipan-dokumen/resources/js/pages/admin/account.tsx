import AppLayout from '@/layouts/app-layout'
import { account } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/react'
import ListAccount from '@/components/list-account'
import { ListAccountItem } from '@/types/account'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Account',
        href: account().url,
    },
]

interface Props {
    accounts: ListAccountItem[]
}

export default function Account({ accounts }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Account" />
            <div className="flex h-full flex-1 flex-col overflow-x-auto p-6">
                <ListAccount accounts={accounts} />
            </div>
        </AppLayout>
    )
}
