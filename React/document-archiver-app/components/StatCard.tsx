import { type LucideIcon } from 'lucide-react';

interface StatCardProps {
  icon: LucideIcon;
  label: string;
  value: string;
  change: string;
  color: string;
  bgColor: string;
}

export default function StatCard({ icon: Icon, label, value, change, color, bgColor }: StatCardProps) {
  return (
    <div className="bg-card rounded-lg border border-border p-6">
      <div className="flex items-start justify-between">
        <div>
          <p className="text-muted-foreground text-sm mb-2">{label}</p>
          <p className="text-4xl font-bold mb-2">{value}</p>
          <p className={`text-sm font-medium ${color}`}>{change}</p>
        </div>
        <div className={`flex items-center justify-center w-12 h-12 rounded-lg ${bgColor}`}>
          <Icon className={`${color} w-6 h-6`} />
        </div>
      </div>
    </div>
  );
}
