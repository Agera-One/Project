import { BadgeCheck, Archive, Trash2 } from "lucide-react"
import { DocumentStats } from '@/types/dashboard'

interface Props {
    stats: DocumentStats
}

export function DocumentStatus({ stats }: Props) {
    const statusCard = [
        {
            id: 1,
            icon: <BadgeCheck className="w-6 h-6" />,
            iconBg: "bg-green-500/20",
            iconColor: "text-green-500",
            value: stats.active.value,
            label: "Total Active Documents",
            change: stats.active.change,
        },
        {
            id: 2,
            icon: <Archive className="w-6 h-6" />,
            iconBg: "bg-yellow-500/20",
            iconColor: "text-yellow-500",
            value: stats.archived.value,
            label: "Total Archived Documents",
            change: stats.archived.change,
        },
        {
            id: 3,
            icon: <Trash2 className="w-6 h-6" />,
            iconBg: "bg-red-500/20",
            iconColor: "text-red-500",
            value: stats.deleted.value,
            label: "Total Deleted Documents",
            change: stats.deleted.change,
        },
    ]

    return (
        <>
            {statusCard.map(({ id, icon, iconBg, iconColor, value, label, change }) => (
                <div
                    key={id}
                    className="bg-sidebar border border-sidebar-accent rounded-2xl p-6 flex items-center gap-6"
                >
                    <div className={`${iconBg} ${iconColor} flex p-4 rounded-lg items-center`}>
                        {icon}
                    </div>
                    <div className="flex-1">
                        <div className="flex justify-between items-start gap-3">
                            <h3 className="text-3xl font-semibold text-white">
                                {value}
                            </h3>
                            <span className="text-sm font-medium text-emerald-400 bg-emerald-400/10 px-2 py-1 rounded">
                                +{change} file today
                            </span>
                        </div>
                        <p className="text-sm text-gray-400 mt-1">
                            {label}
                        </p>
                    </div>
                </div>
            ))}
        </>
    )
}
