import { DropdownMenuAccount } from '@/components/dropdown-menu-account';
import Badge from '@/components/ui/badge';
import type { ListAccountItem } from '@/types/account';
import { formatDate } from '@/utils/format-date';
import { AccountInfo } from './account-info';

interface ListAccountProps {
    accounts: ListAccountItem[];
}

export default function ListAccount({ accounts }: ListAccountProps) {
    return (
        <div className="overflow-hidden rounded-xl bg-sidebar">
            <table className="w-full border-collapse">
                <thead>
                    <tr className="border-b border-zinc-700">
                        <th className="px-6 py-5 text-left text-sm font-medium text-gray-400">
                            NAME
                        </th>
                        <th className="px-6 py-5 text-left text-sm font-medium text-gray-400">
                            STATUS
                        </th>
                        <th className="px-6 py-5 text-left text-sm font-medium text-gray-400">
                            ROLE
                        </th>
                        <th className="px-6 py-5 text-left text-sm font-medium text-gray-400">
                            ADDED ON
                        </th>
                        <th className="w-12 px-6 py-5"></th>
                    </tr>
                </thead>

                <tbody>
                    {accounts.map((acc) => (
                        <tr
                            key={acc.id}
                            className="border-b border-zinc-700 last:border-none hover:bg-zinc-800/50"
                        >
                            <td className="w-48 px-6 py-5">
                                <AccountInfo
                                    id={acc.id}
                                    name={acc.name}
                                    email={acc.email}
                                />
                            </td>
                            <td className="w-32 px-6 py-5">
                                <Badge
                                    status={
                                        acc.is_active ? 'active' : 'inactive'
                                    }
                                />
                            </td>

                            <td className="w-32 px-6 py-5">{acc.role}</td>
                            <td className="w-32 px-6 py-5 text-gray-300">
                                {formatDate(acc.created_at)}
                            </td>
                            <td className="w-12 px-6 py-5">
                                <DropdownMenuAccount
                                    id={acc.id}
                                    name={acc.name}
                                    is_active={acc.is_active}
                                />
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
