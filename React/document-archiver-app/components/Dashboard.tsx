'use client';

import { CheckCircle2, Archive, Trash2, FileJson, FileSpreadsheet, FileText, ImageIcon } from 'lucide-react';
import StatCard from '@/components/StatCard';
import StorageChart from '@/components/StorageChart';
import FileTypeCard from '@/components/FileTypeCard';

export default function Dashboard() {
  const fileTypes = [
    { name: 'PDF', count: 5, size: '953.46 KB', percentage: 33, color: '#ef4444', icon: FileText },
    { name: 'DOCX', count: 1, size: '14.39 KB', percentage: 0, color: '#3b82f6', icon: FileText },
    { name: 'XLSX', count: 2, size: '30.96 KB', percentage: 1, color: '#06b6d4', icon: FileSpreadsheet },
    { name: 'PPTX', count: 1, size: '1.58 MB', percentage: 55, color: '#f97316', icon: FileText },
    { name: 'JPG', count: 0, size: '0 B', percentage: 0, color: '#8b5cf6', icon: ImageIcon },
    { name: 'PNG', count: 3, size: '300.7 KB', percentage: 10, color: '#06b6d4', icon: ImageIcon },
  ];

  const storageData = [
    { type: 'DOCX', value: 14.39, color: '#3b82f6' },
    { type: 'XLSX', value: 30.96, color: '#06b6d4' },
    { type: 'PDF', value: 953.46, color: '#ef4444' },
    { type: 'PPTX', value: 1580, color: '#f97316' },
    { type: 'JPG', value: 0, color: '#8b5cf6' },
    { type: 'PNG', value: 300.7, color: '#06b6d4' },
  ];

  return (
    <div className="flex-1 overflow-y-auto">
      <div className="p-4 md:p-8 max-w-7xl mx-auto">
        <div className="mb-8">
          <h1 className="text-3xl font-bold mb-2">Dashboard</h1>
          <p className="text-muted-foreground">View your document statistics and storage usage</p>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
          <StatCard
            icon={CheckCircle2}
            label="Total Active Documents"
            value="10"
            change="+0 file today"
            color="text-emerald-400"
            bgColor="bg-emerald-500/10"
          />
          <StatCard
            icon={Archive}
            label="Total Archived Documents"
            value="2"
            change="+0 file today"
            color="text-yellow-400"
            bgColor="bg-yellow-500/10"
          />
          <StatCard
            icon={Trash2}
            label="Total Deleted Documents"
            value="3"
            change="+0 file today"
            color="text-red-400"
            bgColor="bg-red-500/10"
          />
        </div>

        {/* Main Content Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          {/* Document Extension */}
          <div className="lg:col-span-2 bg-card rounded-lg border border-border p-6">
            <h2 className="text-xl font-semibold mb-6">Document Extension</h2>
            <div className="grid grid-cols-2 gap-4">
              {fileTypes.map((type) => (
                <FileTypeCard key={type.name} {...type} />
              ))}
            </div>
          </div>

          {/* Storage Details */}
          <div className="bg-card rounded-lg border border-border p-6">
            <h2 className="text-xl font-semibold mb-2">Storage Details</h2>
            <p className="text-muted-foreground text-sm mb-6">585 GB of total space used</p>
            <StorageChart data={storageData} />
          </div>
        </div>
      </div>
    </div>
  );
}
