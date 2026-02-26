'use client';

interface StorageData {
  type: string;
  value: number;
  color: string;
}

interface StorageChartProps {
  data: StorageData[];
}

export default function StorageChart({ data }: StorageChartProps) {
  const total = data.reduce((sum, item) => sum + item.value, 0);
  const dataWithPercentage = data.map((item) => ({
    ...item,
    percentage: (item.value / total) * 100,
  }));

  // Calculate rotation for pie chart
  let currentRotation = 0;
  const slices = dataWithPercentage.map((item) => {
    const sliceAngle = (item.percentage / 100) * 360;
    const rotation = currentRotation;
    currentRotation += sliceAngle;

    return { ...item, rotation, sliceAngle };
  });

  return (
    <div className="flex flex-col items-center gap-6">
      {/* Donut Chart */}
      <div className="relative w-40 h-40">
        <svg viewBox="0 0 100 100" className="w-full h-full">
          {slices.map((slice, index) => {
            const radius = 40;
            const innerRadius = 25;
            const startAngle = (slice.rotation * Math.PI) / 180;
            const endAngle = ((slice.rotation + slice.sliceAngle) * Math.PI) / 180;

            const x1 = 50 + radius * Math.cos(startAngle);
            const y1 = 50 + radius * Math.sin(startAngle);
            const x2 = 50 + radius * Math.cos(endAngle);
            const y2 = 50 + radius * Math.sin(endAngle);

            const ix1 = 50 + innerRadius * Math.cos(startAngle);
            const iy1 = 50 + innerRadius * Math.sin(startAngle);
            const ix2 = 50 + innerRadius * Math.cos(endAngle);
            const iy2 = 50 + innerRadius * Math.sin(endAngle);

            const largeArc = slice.sliceAngle > 180 ? 1 : 0;

            const path = `
              M ${x1} ${y1}
              A ${radius} ${radius} 0 ${largeArc} 1 ${x2} ${y2}
              L ${ix2} ${iy2}
              A ${innerRadius} ${innerRadius} 0 ${largeArc} 0 ${ix1} ${iy1}
              Z
            `;

            return <path key={index} d={path} fill={slice.color} strokeWidth="0" />;
          })}
          <circle cx="50" cy="50" r="23" fill="rgb(15, 23, 42)" />
        </svg>
      </div>

      {/* Legend */}
      <div className="grid grid-cols-2 gap-3 w-full">
        {data.map((item) => (
          <div key={item.type} className="flex items-center gap-2 text-xs">
            <div className="w-2 h-2 rounded-full" style={{ backgroundColor: item.color }} />
            <span className="text-muted-foreground">{item.type}</span>
          </div>
        ))}
      </div>
    </div>
  );
}
