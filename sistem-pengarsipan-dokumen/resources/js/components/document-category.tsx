import type React from "react"
import { FileText } from "lucide-react"

interface CategoryCard {
    id: string
    title: string
    iconBg: string
    iconColor: string
    percentUsed: string
    files: number
    size: string
}


export default function DocumentCategory() {
    const categoryCard: CategoryCard[] = [
        {
            id: "3",
            title: "DOCX",
            iconBg: "bg-blue-500/20",
            iconColor: "text-blue-500",
            percentUsed: "24% Used",
            files: 245,
            size: "26.40 GB",
        },
        {
            id: "1",
            title: "XLSX",
            iconBg: "bg-emerald-500/20",
            iconColor: "text-emerald-500",
            percentUsed: "17% Used",
            files: 245,
            size: "26.40 GB",
        },
        {
            id: "2",
            title: "PDF",
            iconBg: "bg-red-500/20",
            iconColor: "text-red-500",
            percentUsed: "22% Used",
            files: 245,
            size: "26.40 GB",
        },
        {
            id: "4",
            title: "PPTX",
            iconBg: "bg-orange-500/20",
            iconColor: "text-orange-500",
            percentUsed: "46% Used",
            files: 245,
            size: "26.40 GB",
        },
        {
            id: "4",
            title: "JPG",
            iconBg: "bg-purple-500/20",
            iconColor: "text-purple-500",
            percentUsed: "46% Used",
            files: 245,
            size: "26.40 GB",
        },
        {
            id: "4",
            title: "PNG",
            iconBg: "bg-cyan-500/20",
            iconColor: "text-cyan-500",
            percentUsed: "46% Used",
            files: 245,
            size: "26.40 GB",
        },
    ]
    return (
        <div className="xl:col-span-2 bg-sidebar p-6 border border-zinc-700/50 rounded-lg">
            <div className="flex items-center justify-between mb-9">
                <h2 className="text-2xl font-semibold">Document Category</h2>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                {categoryCard.map(({id, title, iconBg, iconColor, percentUsed, files, size}) => (
                    <div key={id} className="flex justify-between bg-sidebar-accent border border-zinc-700/50 rounded-lg p-5">
                        <div className="flex gap-4">
                            <div className={`${iconBg} ${iconColor} flex p-3 rounded-lg items-center`}>
                                <FileText className="w-6 h-6" />
                            </div>
                            <div>
                                <h3 className="text-lg font-semibold">{title}</h3>
                                <p className="text-sm text-slate-400">{percentUsed}</p>
                            </div>
                        </div>
                        <div className="flex flex-col items-end text-sm">
                            <span className="text-slate-400">{files} files</span>
                            <span className="text-slate-300 font-medium">{size}</span>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    )
}
