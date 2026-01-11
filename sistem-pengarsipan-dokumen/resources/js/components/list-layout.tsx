import { EllipsisVertical, FileText } from "lucide-react"
import Badge from "@/components/ui/badge";

const files = [
    {
        id: 1,
        title: "Laporan_Keuangan_2026",
        name: "John Doe",
        email: "johndoe@gmail.com",
        category: "xlsx",
        size: "2.4 MB",
        date: "20 May, 2027",
        status: "active",
    },
    {
        id: 2,
        title: "Presentasi_Quarterly_Review",
        name: "John Doe",
        email: "johndoe@gmail.com",
        category: "pptx",
        size: "8.7 MB",
        date: "20 May, 2027",
        status: "active",
    },
    {
        id: 3,
        title: "Surat_Persetujuan_Direksi",
        name: "John Doe",
        email: "johndoe@gmail.com",
        category: "pdf",
        size: "156 KB",
        date: "20 May, 2027",
        status: "archived",
    },
    {
        id: 4,
        title: "Foto_Acara_Groundbreaking",
        name: "John Doe",
        email: "johndoe@gmail.com",
        category: "jpg",
        size: "4.1 MB",
        date: "20 May, 2027",
        status: "active",
    },
    {
        id: 5,
        title: "SOP_Operasional_Baru",
        name: "John Doe",
        email: "johndoe@gmail.com",
        category: "docx",
        size: "89 KB",
        date: "20 May, 2027",
        status: "active",
    },
    {
        id: 6,
        title: "Data_Analisis_Pasar_2027",
        name: "John Doe",
        email: "johndoe@gmail.com",
        category: "xlsx",
        size: "12.3 MB",
        date: "20 May, 2027",
        status: "deleted",
    },
    {
        id: 7,
        title: "Company_Profile_Update",
        name: "John Doe",
        email: "johndoe@gmail.com",
        category: "pdf",
        size: "3.8 MB",
        date: "20 May, 2027",
        status: "active",
    },
    {
        id: 8,
        title: "Banner_Promosi_Launch",
        name: "John Doe",
        email: "johndoe@gmail.com",
        category: "png",
        size: "920 KB",
        date: "20 May, 2027",
        status: "deleted",
    },
    {
        id: 9,
        title: "Kontrak_Kerjasama_PTABC",
        name: "John Doe",
        email: "johndoe@gmail.com",
        category: "pdf",
        size: "1.1 MB",
        date: "20 May, 2027",
        status: "archived",
    },
    {
        id: 10,
        title: "Meeting_Notes_Mei2027",
        name: "John Doe",
        email: "johndoe@gmail.com",
        category: "docx",
        size: "234 KB",
        date: "20 May, 2027",
        status: "deleted",
    }
];

export default function ListLayout() {
    return (
        <div className="rounded-xl bg-sidebar border border-zinc-700">
            <table className="w-full">
                <thead>
                    <tr className="border-b border-zinc-700 bg-sidebar-accent">
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400 ">File Name</th>
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400">Category</th>
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400">Size</th>
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400">Date Modified</th>
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400">Status</th>
                        <th className="px-6 py-4 text-left text-sm font-medium text-gray-400"></th>
                    </tr>
                </thead>
                <tbody>
                    {files.map(({id, title, category, size, date, status}) => {
                        return (
                            <tr key={id} className="border-b border-zinc-700 transition-colors hover:bg-sidebar-accent">
                                <td className="p-6">
                                    <div className="flex items-center gap-3">
                                        <FileText className="h-5 w-5 text-gray-400" />
                                        <span className="text-gray-300">{title}</span>
                                    </div>
                                </td>
                                <td className="p-6 text-gray-400 w-40"><Badge status={category} /></td>
                                <td className="p-6 text-gray-400 w-40">{size}</td>
                                <td className="p-6 text-gray-400 w-40">{date}</td>
                                <td className="p-6 text-gray-400 w-40"><Badge status={status} /></td>
                                <td className="p-6 w-15">
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
    )
}
