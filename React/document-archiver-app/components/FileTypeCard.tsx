import { type LucideIcon } from 'lucide-react';

interface FileTypeCardProps {
  name: string;
  count: number;
  size: string;
  percentage: number;
  color: string;
  icon: LucideIcon;
}

export default function FileTypeCard({ name, count, size, percentage, color, icon: Icon }: FileTypeCardProps) {
  return (
    <div className="bg-secondary/40 rounded-lg p-4 border border-border/50">
      <div className="flex items-start justify-between mb-3">
        <div className="flex items-center gap-3">
          <div className="flex items-center justify-center w-10 h-10 rounded-lg" style={{ backgroundColor: `${color}20` }}>
            <Icon size={20} style={{ color }} />
          </div>
          <div>
            <p className="font-semibold">{name}</p>
            <p className="text-xs text-muted-foreground">{percentage}% Used</p>
          </div>
        </div>
      </div>
      <div className="w-full bg-border rounded-full h-2 mb-2">
        <div className="bg-gradient-to-r h-2 rounded-full" style={{ backgroundColor: color, width: `${percentage}%` }} />
      </div>
      <div className="flex justify-between text-xs text-muted-foreground">
        <span>{count} files</span>
        <span>{size}</span>
      </div>
    </div>
  );
}
