// <DOCUMENT filename="doughnut-chart.tsx">
import { useEffect, useRef } from 'react'
import * as echarts from 'echarts/core'
import {
    TooltipComponent,
    TooltipComponentOption,
    LegendComponent,
    LegendComponentOption,
} from 'echarts/components'
import { PieChart, PieSeriesOption } from 'echarts/charts'
import { LabelLayout } from 'echarts/features'
import { CanvasRenderer } from 'echarts/renderers'

echarts.use([
    TooltipComponent,
    LegendComponent,
    PieChart,
    CanvasRenderer,
    LabelLayout,
])

type EChartsOption = echarts.ComposeOption<
    TooltipComponentOption | LegendComponentOption | PieSeriesOption
>

type DoughnutChartProps = {
    option?: EChartsOption
    className?: string
}

export function DoughnutChart({ option, className = '' }: DoughnutChartProps) {
    const chartRef = useRef<HTMLDivElement>(null)

    useEffect(() => {
        if (!chartRef.current) return

        const chart = echarts.init(chartRef.current, null, {
            renderer: 'canvas',
            devicePixelRatio: window.devicePixelRatio || 1,
        })

        const defaultOption: EChartsOption = {
            backgroundColor: 'transparent',

            tooltip: {
                trigger: 'item',
                formatter: '{b}: {c} GB ({d}%)',
                backgroundColor: 'rgba(0,0,0,0.75)',
                borderColor: 'transparent',
                textStyle: { color: '#fff' },
            },

            legend: {
                show: false,
            },

            series: [
                {
                    name: 'File Types',
                    type: 'pie',
                    radius: ['45%', '100%'],
                    center: ['50%', '50%'],
                    avoidLabelOverlap: false,
                    padAngle: 4,
                    itemStyle: {
                        borderRadius: 10,
                        color: (params) => {
                            const colors = [
                                '#2B7FFF',
                                '#00BC7D',
                                '#FB2C36',
                                '#FF6900',
                                '#AD46FF',
                                '#00B8DB',
                            ]
                            return colors[params.dataIndex % colors.length]
                        }
                    },
                    emphasis: {
                        scale: false,
                    },
                    label: {
                        show: false,
                        position: 'center',
                    },
                    labelLine: {
                        show: false,
                    },
                    data: [
                        {
                            value: 1048,
                            name: 'DOCX'
                        },
                        {
                            value: 735,
                            name: 'XLSX'
                        },
                        {
                            value: 580,
                            name: 'PDF'
                        },
                        {
                            value: 484,
                            name: 'PPTX'
                        },
                        {
                            value: 300,
                            name: 'JPG'
                        },
                        {
                            value: 300,
                            name: 'PNG'
                        },
                    ],
                },
            ],
        }

        chart.setOption(option ?? defaultOption)

        const handleResize = () => chart.resize()
        window.addEventListener('resize', handleResize)

        return () => {
            window.removeEventListener('resize', handleResize)
            chart.dispose()
        }
    }, [option])

    return (
        <div className="w-64 h-64 mx-auto">
            <div ref={chartRef} className="w-full h-full" />
        </div>
    )
}
