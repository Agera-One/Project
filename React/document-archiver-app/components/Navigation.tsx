'use client';

import { LayoutGrid, Clock, Star, FileText, Archive, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface NavigationProps {
  activeTab: string;
  setActiveTab: (tab: string) => void;
  className?: string;
  onSelect?: () => void;
}

const navItems = [
  { id: 'dashboard', label: 'Dashboard', icon: LayoutGrid },
  { id: 'recently', label: 'Recently', icon: Clock },
  { id: 'starred', label: 'Starred', icon: Star },
  { id: 'documents', label: 'My Documents', icon: FileText },
  { id: 'archives', label: 'Archives', icon: Archive },
  { id: 'trash', label: 'Trash', icon: Trash2 },
];

export default function Navigation({ activeTab, setActiveTab, className, onSelect }: NavigationProps) {
  const handleTabChange = (tabId: string) => {
    setActiveTab(tabId);
    onSelect?.();
  };

  return (
    <nav className={`flex flex-col w-64 border-r border-border bg-card ${className || ''}`}>
      <div className="p-4 md:p-6 space-y-2">
        {navItems.map((item) => {
          const Icon = item.icon;
          const isActive = activeTab === item.id;

          return (
            <Button
              key={item.id}
              variant={isActive ? 'default' : 'ghost'}
              className={`w-full justify-start gap-3 ${isActive ? 'bg-accent text-accent-foreground' : 'hover:bg-accent/10'}`}
              onClick={() => handleTabChange(item.id)}
            >
              <Icon size={20} />
              <span>{item.label}</span>
            </Button>
          );
        })}
      </div>

      {/* Upload Section */}
      <div className="mt-auto p-4 md:p-6 border-t border-border">
        <div className="flex flex-col items-center justify-center p-6 border-2 border-dashed border-border rounded-lg hover:border-accent/50 transition-colors cursor-pointer">
          <div className="flex items-center justify-center w-12 h-12 rounded-full border-2 border-accent mb-2">
            <span className="text-2xl text-accent font-light">+</span>
          </div>
          <p className="text-sm font-medium text-center">Upload New File</p>
          <p className="text-xs text-muted-foreground text-center mt-1">Drag & drop or click</p>
        </div>
      </div>

      {/* User Profile */}
      <div className="p-4 md:p-6 border-t border-border">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-full bg-accent/20 text-accent font-bold flex items-center justify-center">
            DP
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-medium truncate">Dzaki Prasetyo</p>
            <p className="text-xs text-muted-foreground truncate">dzakiprasetyo98@gmail.com</p>
          </div>
        </div>
      </div>
    </nav>
  );
}
