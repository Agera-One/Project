import { EllipsisVertical, FileText } from "lucide-react"
import Badge from "@/components/ui/badge";
import { DocumentListItem } from '@/types/document-list'

interface ListLayoutProps {
    files: DocumentListItem[]
}

export default function ListLayout({ files }: ListLayoutProps) {
    return (
        <div className="rounded-xl bg-sidebar border border-zinc-700 overflow-hidden">
            <table className="w-full border-collapse">
                <thead>
                    <tr className="border-b border-zinc-700 bg-sidebar-accent/50">
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400">File Name</th>
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400">Extension</th>
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400">Size</th>
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400">Date Modified</th>
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400">Status</th>
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400 w-12"></th>
                    </tr>
                </thead>
                <tbody>
                    {files.map(({ id, title, extension, size, date, status }) => (
                        <tr
                            key={id}
                            className="border-b border-zinc-800 hover:bg-sidebar-accent/30 transition-colors"
                        >
                            <td className="px-6 py-4">
                                <div className="flex items-center gap-3">
                                    <FileText className="h-5 w-5 text-gray-400" />
                                    <span className="text-gray-200 font-medium">{title}</span>
                                </div>
                            </td>
                            <td className="px-6 py-4 text-gray-300 w-32">
                                <Badge
                                    status={extension?.toLowerCase() || 'unknown'}
                                />
                            </td>
                            <td className="px-6 py-4 text-gray-300 w-32">{size}</td>
                            <td className="px-6 py-4 text-gray-300 w-40">{date}</td>
                            <td className="px-6 py-4 text-gray-300 w-32">
                                <Badge status={status} />
                            </td>
                            <td className="px-6 py-4 w-12">
                                <button
                                    className="text-gray-400 hover:text-gray-200 transition-colors"
                                    aria-label="More options"
                                >
                                    <EllipsisVertical className="h-5 w-5" />
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    )
}
