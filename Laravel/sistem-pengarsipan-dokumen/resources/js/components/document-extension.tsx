import { FileText } from 'lucide-react'
import type { ExtensionStat } from '@/types/dashboard'

const EXTENSION_CONFIG: Record<
    string,
    { key: string; iconBg: string; iconColor: string }
> = {
    PDF: {
        key: 'PDF',
        iconBg: 'bg-red-500/20',
        iconColor: 'text-red-500',
    },
    DOCX: {
        key: 'docx',
        iconBg: 'bg-blue-500/20',
        iconColor: 'text-blue-500',
    },
    XLSX: {
        key: 'xlsx',
        iconBg: 'bg-emerald-500/20',
        iconColor: 'text-emerald-500',
    },
    PPTX: {
        key: 'pptx',
        iconBg: 'bg-orange-500/20',
        iconColor: 'text-orange-500',
    },
    JPG: {
        key: 'jpg',
        iconBg: 'bg-purple-500/20',
        iconColor: 'text-purple-500',
    },
    PNG: {
        key: 'png',
        iconBg: 'bg-cyan-500/20',
        iconColor: 'text-cyan-500',
    },
}

const DEFAULT_STYLE = {
    key: 'unknown',
    iconBg: 'bg-slate-500/20',
    iconColor: 'text-slate-400',
}

interface Props {
    data: ExtensionStat[]
}

export default function DocumentExtension({ data }: Props) {
    return (
        <div className="xl:col-span-2 bg-sidebar p-6 border border-zinc-700/50 rounded-lg">
            <div className="flex items-center justify-between mb-9">
                <h2 className="text-2xl font-semibold">
                    Document Extension
                </h2>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {data.map((item) => {
                    const config =
                        EXTENSION_CONFIG[item.extension] ?? DEFAULT_STYLE

                    return (
                        <div
                            key={config.key}
                            className="flex justify-between bg-sidebar-accent border border-zinc-700/50 rounded-lg p-5"
                        >
                            <div className="flex gap-4">
                                <div
                                    className={`${config.iconBg} ${config.iconColor} flex p-3 rounded-lg items-center`}
                                >
                                    <FileText className="w-6 h-6" />
                                </div>

                                <div>
                                    <h3 className="text-lg font-semibold">
                                        {item.extension}
                                    </h3>
                                    <p className="text-sm text-slate-400">
                                        {item.percent}% Used
                                    </p>
                                </div>
                            </div>

                            <div className="flex flex-col items-end text-sm">
                                <span className="text-slate-400">
                                    {item.files} files
                                </span>
                                <span className="text-slate-300 font-medium">
                                    {item.size} {item.unit}
                                </span>
                            </div>
                        </div>
                    )
                })}
            </div>
        </div>
    )
}
