import { EllipsisVertical, FileText, ArrowRight } from "lucide-react"
import Badge from "@/components/ui/badge";
import { recently } from "@/routes";
import { Link } from '@inertiajs/react';

const url = recently();

interface FilesProps {
    id: number,
    name: string,
    category: string,
    size: string,
    date: string,
    status: string,
}


export default function RecentFilesList() {
    const files: FilesProps[] = [
        {
            id: 1,
            name: "Laporan_Keuangan_2026",
            category: "xlsx",
            size: "2.4 MB",
            date: "20 May, 2027",
            status: "active",
        },
        {
            id: 2,
            name: "Presentasi_Quarterly_Review",
            category: "pptx",
            size: "8.7 MB",
            date: "20 May, 2027",
            status: "active",
        },
        {
            id: 3,
            name: "Surat_Persetujuan_Direksi",
            category: "pdf",
            size: "156 KB",
            date: "20 May, 2027",
            status: "archived",
        },
        {
            id: 4,
            name: "Foto_Acara_Groundbreaking",
            category: "jpg",
            size: "4.1 MB",
            date: "20 May, 2027",
            status: "active",
        },
        {
            id: 5,
            name: "SOP_Operasional_Baru",
            category: "docx",
            size: "89 KB",
            date: "20 May, 2027",
            status: "active",
        },
        {
            id: 6,
            name: "Data_Analisis_Pasar_2027",
            category: "xlsx",
            size: "12.3 MB",
            date: "20 May, 2027",
            status: "deleted",
        },
        {
            id: 7,
            name: "Company_Profile_Update",
            category: "pdf",
            size: "3.8 MB",
            date: "20 May, 2027",
            status: "active",
        },
        {
            id: 8,
            name: "Banner_Promosi_Launch",
            category: "png",
            size: "920 KB",
            date: "20 May, 2027",
            status: "deleted",
        },
        {
            id: 9,
            name: "Kontrak_Kerjasama_PTABC",
            category: "pdf",
            size: "1.1 MB",
            date: "20 May, 2027",
            status: "archived",
        },
        {
            id: 10,
            name: "Meeting_Notes_Mei2027",
            category: "docx",
            size: "234 KB",
            date: "20 May, 2027",
            status: "deleted",
        }
    ];
    return (
        <div className="px-6">
            <div className="rounded-lg bg-sidebar mx-auto max-w-screen p-6">
                {/* Header */}
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Recent files</h1>
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
                                <th className="pb-4 text-left text-sm font-medium text-gray-400">Category</th>
                                <th className="pb-4 text-left text-sm font-medium text-gray-400">Size</th>
                                <th className="pb-4 text-left text-sm font-medium text-gray-400">Date Modified</th>
                                <th className="pb-4 text-left text-sm font-medium text-gray-400">Status</th>
                                <th className="pb-4 text-left text-sm font-medium text-gray-400"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {files.map((file) => {
                                return (
                                <tr key={file.id} className="border-b border-[#2a3544] transition-colors hover:bg-sidebar-accent">
                                        <td className="py-5">
                                            <div className="flex items-center gap-3">
                                                <FileText className="h-5 w-5 text-gray-400" />
                                                <span className="text-gray-300">{file.name}</span>
                                            </div>
                                        </td>
                                        <td className="py-5 text-gray-400 w-40"><Badge status={file.category} /></td>
                                        <td className="py-5 text-gray-400 w-40">{file.size}</td>
                                        <td className="py-5 text-gray-400 w-40">{file.date}</td>
                                        <td className="py-5 text-gray-400 w-40"><Badge status={file.status} /></td>
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
