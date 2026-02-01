import { DropdownMenuIcons } from '@/components/dropdown-menu-icons';
import Badge from '@/components/ui/badge';
import { DocumentListItem } from '@/types/document-list';
import { formatDate } from '@/utils/format-date';
import { formatFileSize } from '@/utils/format-file-sizes';
import { FileText, Star as StarIcon } from 'lucide-react';
import { AccountInfo } from './account-info';

interface ListLayoutProps {
    documents: DocumentListItem[];
}

export default function ListLayout({ documents }: ListLayoutProps) {
    return (
        <div className="overflow-hidden rounded-xl bg-sidebar">
            <table className="w-full border-collapse">
                <thead>
                    <tr className="border-b border-zinc-700">
                        <th className="px-6 py-5 text-left text-sm font-medium text-gray-400">
                            FILE NAME
                        </th>
                        <th className="px-6 py-5 text-left text-sm font-medium text-gray-400">
                            OWNER
                        </th>
                        <th className="px-6 py-5 text-left text-sm font-medium text-gray-400">
                            EXTENSION
                        </th>
                        <th className="px-6 py-5 text-left text-sm font-medium text-gray-400">
                            SIZE
                        </th>
                        <th className="px-6 py-5 text-left text-sm font-medium text-gray-400">
                            DATE MODIFIED
                        </th>
                        <th className="px-6 py-5 text-left text-sm font-medium text-gray-400">
                            STATUS
                        </th>
                        <th className="w-12 px-6 py-5"></th>
                    </tr>
                </thead>

                <tbody>
                    {documents.map((document) => (
                        <tr
                            key={document.id}
                            className="border-b border-zinc-700 last:border-none hover:bg-zinc-800/50"
                        >
                            <td className="w-24 px-6 py-4">
                                <div className="flex items-center gap-3">
                                    <FileText className="h-5 w-5 text-gray-400" />
                                    <span className="font-medium text-gray-200">
                                        {document.title}
                                        {document.is_starred && (
                                            <StarIcon className="ml-1 inline h-4 w-4 fill-yellow-400 text-yellow-400" />
                                        )}
                                    </span>
                                </div>
                            </td>

                            {document.owner && (
                                <td className="w-32 px-6 py-5">
                                    <AccountInfo
                                        id={document.owner.id}
                                        name={document.owner.name}
                                        email={document.owner.email}
                                    />
                                </td>
                            )}

                            <td className="w-32 px-6 py-4">
                                <Badge status={document.extension} />
                            </td>

                            <td className="w-32 px-6 py-4 text-gray-300">
                                {formatFileSize(document.size)}
                            </td>

                            <td className="w-40 px-6 py-4 text-gray-300">
                                {formatDate(document.updated_at)}
                            </td>

                            <td className="w-32 px-6 py-4">
                                <Badge status={document.status} />
                            </td>

                            <td className="w-12 px-6 py-4">
                                <DropdownMenuIcons document={document} />
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
