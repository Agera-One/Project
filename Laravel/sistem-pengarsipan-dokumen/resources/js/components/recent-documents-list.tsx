import { EllipsisVertical, FileText, ArrowRight } from "lucide-react"
import Badge from "@/components/ui/badge";
import { recently } from "@/routes";
import { Link } from '@inertiajs/react';
import { DocumentData } from "@/types/document";
import { formatDate } from "@/utils/format-date";
import { formatFileSize } from "@/utils/format-file-sizes";

const url = recently();

interface RecentFilesListProps {
    documents: DocumentData[]
}

export default function RecentDocumentsList({ documents }: RecentFilesListProps) {
    const rows = documents.map(doc => ({
        id: doc.id,
        title: doc.title,
        extension: doc.extension,
        size: formatFileSize(doc.size),
        status: doc.status,
        date: formatDate(doc.created_at),
    }));

    return (
        <div className="px-6">
            <div className="rounded-lg bg-sidebar mx-auto max-w-screen p-6">
                {/* Header */}
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">All Documents</h1>
                    <button className="flex items-center gap-2 text-gray-400 transition-colors hover:text-white">
                        <span className="text-sm"><Link href={url}>View More</Link></span>
                        <ArrowRight className="h-4 w-4" />
                    </button>
                </div>

                {/* Table */}
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead>
                            <tr className="border-b border-[#2a3544]">
                                <th className="pb-4 text-left text-sm font-medium text-gray-400">File Name</th>
                                <th className="pb-4 text-left text-sm font-medium text-gray-400">Extension</th>
                                <th className="pb-4 text-left text-sm font-medium text-gray-400">Size</th>
                                <th className="pb-4 text-left text-sm font-medium text-gray-400">Date Modified</th>
                                <th className="pb-4 text-left text-sm font-medium text-gray-400">Status</th>
                                <th className="pb-4 text-left text-sm font-medium text-gray-400"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row) => {
                                return (
                                <tr key={row.id} className="border-b border-[#2a3544] transition-colors hover:bg-sidebar-accent">
                                        <td className="py-5">
                                            <div className="flex items-center gap-3">
                                                <FileText className="h-5 w-5 text-gray-400" />
                                                <span className="text-gray-300">{row.title}</span>
                                            </div>
                                        </td>
                                        <td className="py-5 text-gray-400 w-40"><Badge status={row.extension} /></td>
                                        <td className="py-5 text-gray-400 w-40">{row.size}</td>
                                        <td className="py-5 text-gray-400 w-40">{row.date}</td>
                                        <td className="py-5 text-gray-400 w-40"><Badge status={row.status} /></td>
                                        <td className="py-5 w-15">
                                            <div className="flex items-center gap-3">
                                                <button className="text-gray-400 transition-colors hover:text-white">
                                                    <EllipsisVertical className="h-5 w-5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                )
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    )
}
