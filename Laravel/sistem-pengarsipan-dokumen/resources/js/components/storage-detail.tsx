import { DoughnutChart } from "@/components/doughnut-chart";

export default function StorageDetail() {

    return (
        <div className="xl:col-span-1 bg-sidebar border border-zinc-700/50 rounded-lg p-6">
            <div className="mb-7">
                <h2 className="text-2xl font-semibold">Storage Details</h2>
                <p className="text-sm text-slate-400">585 GB of total space used</p>
            </div>


            {/* Donut Chart */}
            <div className="relative mb-8">
                <DoughnutChart />
            </div>

            {/* Legend */}
            <div className="flex gap-5 flex-wrap justify-center">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span className="text-sm text-slate-300">DOCX</span>
                    </div>
                </div>
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 bg-emerald-500 rounded-full"></div>
                        <span className="text-sm text-slate-300">XLSX</span>
                    </div>
                </div>
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 bg-red-500 rounded-full"></div>
                        <span className="text-sm text-slate-300">PDF</span>
                    </div>
                </div>
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 bg-orange-500 rounded-full"></div>
                        <span className="text-sm text-slate-300">PPTX</span>
                    </div>
                </div>
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 bg-purple-500 rounded-full"></div>
                        <span className="text-sm text-slate-300">JPG</span>
                    </div>
                </div>
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 bg-cyan-500 rounded-full"></div>
                        <span className="text-sm text-slate-300">PNG</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
