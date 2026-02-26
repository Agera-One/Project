'use client';

import { useState } from 'react';
import { FileText, Star, MoreVertical, Download, Archive, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface DocumentCardProps {
  document: {
    id: string;
    name: string;
    owner: string;
    email: string;
    extension: string;
    size: string;
    date: string;
    status: string;
    starred?: boolean;
  };
}

const extensionColors: { [key: string]: { bg: string; text: string; color: string } } = {
  PDF: { bg: 'bg-red-500/10', text: 'text-red-400', color: '#ef4444' },
  PNG: { bg: 'bg-cyan-500/10', text: 'text-cyan-400', color: '#06b6d4' },
  DOCX: { bg: 'bg-blue-500/10', text: 'text-blue-400', color: '#3b82f6' },
  PPTX: { bg: 'bg-orange-500/10', text: 'text-orange-400', color: '#f97316' },
  XLSX: { bg: 'bg-cyan-500/10', text: 'text-cyan-400', color: '#06b6d4' },
  JPG: { bg: 'bg-purple-500/10', text: 'text-purple-400', color: '#8b5cf6' },
};

const statusColors: { [key: string]: string } = {
  ACTIVE: 'bg-emerald-500/10 text-emerald-400',
  ARCHIVED: 'bg-yellow-500/10 text-yellow-400',
  DELETED: 'bg-red-500/10 text-red-400',
};

export default function DocumentCard({ document }: DocumentCardProps) {
  const [starred, setStarred] = useState(document.starred || false);
  const [showMenu, setShowMenu] = useState(false);

  const extColor = extensionColors[document.extension] || extensionColors.PDF;
  const statusColor = statusColors[document.status] || statusColors.ACTIVE;

  return (
    <div className="bg-card rounded-lg border border-border p-4 hover:border-accent/50 transition-all">
      <div className="flex items-start justify-between gap-4">
        <div className="flex items-start gap-4 flex-1 min-w-0">
          {/* File Icon */}
          <div className={`flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-lg ${extColor.bg}`}>
            <FileText size={24} className={extColor.text} />
          </div>

          {/* Document Info */}
          <div className="flex-1 min-w-0">
            <div className="flex items-center gap-2 mb-1">
              <p className="font-semibold truncate">{document.name}</p>
              {starred && <Star size={16} className="text-yellow-400 fill-yellow-400 flex-shrink-0" />}
            </div>

            <p className="text-xs text-muted-foreground mb-3 truncate">
              {document.owner} • {document.email}
            </p>

            <div className="flex flex-wrap items-center gap-2">
              <span className={`inline-block px-2 py-1 rounded text-xs font-medium ${extColor.text} ${extColor.bg}`}>
                {document.extension}
              </span>
              <span className={`inline-block px-2 py-1 rounded text-xs font-medium ${statusColor}`}>
                {document.status}
              </span>
              <span className="text-xs text-muted-foreground">{document.size}</span>
              <span className="text-xs text-muted-foreground">{document.date}</span>
            </div>
          </div>
        </div>

        {/* Actions */}
        <div className="flex-shrink-0 flex items-center gap-1 relative">
          <Button
            variant="ghost"
            size="icon"
            className="h-8 w-8"
            onClick={() => setStarred(!starred)}
          >
            <Star
              size={18}
              className={starred ? 'text-yellow-400 fill-yellow-400' : 'text-muted-foreground'}
            />
          </Button>

          <div className="relative">
            <Button
              variant="ghost"
              size="icon"
              className="h-8 w-8"
              onClick={() => setShowMenu(!showMenu)}
            >
              <MoreVertical size={18} />
            </Button>

            {showMenu && (
              <div className="absolute right-0 top-8 w-48 bg-card border border-border rounded-lg shadow-lg z-50">
                <button className="w-full px-4 py-2 text-left text-sm hover:bg-secondary/50 flex items-center gap-2 border-b border-border">
                  <Download size={16} />
                  Download
                </button>
                <button className="w-full px-4 py-2 text-left text-sm hover:bg-secondary/50 flex items-center gap-2 border-b border-border">
                  <Archive size={16} />
                  Archive
                </button>
                <button className="w-full px-4 py-2 text-left text-sm hover:bg-secondary/50 flex items-center gap-2 text-destructive">
                  <Trash2 size={16} />
                  Delete
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
