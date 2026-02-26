'use client';

import { useState } from 'react';
import { Search, MoreVertical, Star, Trash2, Archive, Download } from 'lucide-react';
import { Button } from '@/components/ui/button';
import DocumentCard from '@/components/DocumentCard';

interface DocumentsListProps {
  title: string;
  subtitle: string;
  status?: string;
}

interface Document {
  id: string;
  name: string;
  owner: string;
  email: string;
  extension: string;
  size: string;
  date: string;
  status: string;
  starred?: boolean;
}

const mockDocuments: Document[] = [
  {
    id: '1',
    name: 'erd(2)',
    owner: 'Dzaki Prasetyo',
    email: 'dzakiprasetyo98@gmail.com',
    extension: 'PNG',
    size: '86.0 KB',
    date: '04 Feb 2026',
    status: 'ACTIVE',
    starred: true,
  },
  {
    id: '2',
    name: 'MAGAN 2',
    owner: 'Dzaki Prasetyo',
    email: 'dzakiprasetyo98@gmail.com',
    extension: 'PPTX',
    size: '1.6 MB',
    date: '04 Feb 2026',
    status: 'ACTIVE',
    starred: true,
  },
  {
    id: '3',
    name: 'CV_Dzaki_Prasetyo',
    owner: 'Dzaki Prasetyo',
    email: 'dzakiprasetyo98@gmail.com',
    extension: 'PDF',
    size: '190.7 KB',
    date: '04 Feb 2026',
    status: 'ACTIVE',
    starred: true,
  },
  {
    id: '4',
    name: 'erd(1)',
    owner: 'Dzaki Prasetyo',
    email: 'dzakiprasetyo98@gmail.com',
    extension: 'PNG',
    size: '125.3 KB',
    date: '02 Feb 2026',
    status: 'ACTIVE',
  },
];

export default function DocumentsList({ title, subtitle, status }: DocumentsListProps) {
  const [searchQuery, setSearchQuery] = useState('');
  const [viewMode, setViewMode] = useState<'list' | 'grid'>('list');

  const filteredDocuments = mockDocuments.filter((doc) => {
    const matchesSearch = doc.name.toLowerCase().includes(searchQuery.toLowerCase());
    if (status) {
      return matchesSearch && doc.status.toLowerCase() === status.toLowerCase();
    }
    return matchesSearch;
  });

  return (
    <div className="flex-1 overflow-y-auto">
      <div className="p-4 md:p-8 max-w-7xl mx-auto">
        <div className="mb-8">
          <h1 className="text-3xl font-bold mb-2">{title}</h1>
          <p className="text-muted-foreground">{subtitle}</p>
        </div>

        {/* Search Bar */}
        <div className="mb-6 flex flex-col sm:flex-row gap-3">
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-3 text-muted-foreground" size={20} />
            <input
              type="text"
              placeholder="Search documents..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full pl-10 pr-4 py-2 rounded-lg border border-border bg-secondary/50 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-accent"
            />
          </div>
        </div>

        {/* Documents Grid/List */}
        {filteredDocuments.length > 0 ? (
          <div className="grid grid-cols-1 gap-4">
            {filteredDocuments.map((doc) => (
              <DocumentCard key={doc.id} document={doc} />
            ))}
          </div>
        ) : (
          <div className="flex flex-col items-center justify-center py-16 text-center">
            <div className="w-16 h-16 rounded-full bg-secondary/50 flex items-center justify-center mb-4">
              <Search className="text-muted-foreground" size={32} />
            </div>
            <p className="text-lg font-medium mb-2">No documents found</p>
            <p className="text-muted-foreground">Try adjusting your search criteria</p>
          </div>
        )}
      </div>
    </div>
  );
}
