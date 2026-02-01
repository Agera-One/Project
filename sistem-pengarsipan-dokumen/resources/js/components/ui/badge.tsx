import React from 'react';

interface BadgeProps {
  status: string;
  children?: React.ReactNode;
  variant?: 'default' | 'outline' | 'solid'; // tambahan optional untuk fleksibilitas
}

const Badge = ({ status, children, variant = 'default' }: BadgeProps) => {
  const baseClasses = "inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold tracking-wide";

  // Warna untuk extension file (extension lowercase)
  const extensionStyles: Record<string, string> = {
    pdf:   "bg-red-500/20 text-red-400 border border-red-500/30",
    jpg:   "bg-purple-500/20 text-purple-400 border border-purple-500/30",
    jpeg:  "bg-purple-500/20 text-purple-400 border border-purple-500/30",
    png:   "bg-cyan-500/20 text-cyan-400 border border-cyan-500/30",
    xlsx:  "bg-emerald-500/20 text-emerald-400 border border-emerald-500/30",
    xls:   "bg-emerald-500/20 text-emerald-400 border border-emerald-500/30",
    docx:  "bg-blue-500/20 text-blue-400 border border-blue-500/30",
    doc:   "bg-blue-500/20 text-blue-400 border border-blue-500/30",
    pptx:  "bg-orange-500/20 text-orange-400 border border-orange-500/30",
    ppt:   "bg-orange-500/20 text-orange-400 border border-orange-500/30",
    unknown: "bg-gray-500/20 text-gray-400 border border-gray-500/30",
  };

  // Warna untuk status dokumen
  const statusStyles: Record<string, string> = {
    active:   "bg-green-500/20 text-green-400 border border-green-500/30",
    inactive: "bg-yellow-500/20 text-yellow-400 border border-yellow-500/30",
    archived: "bg-yellow-500/20 text-yellow-400 border border-yellow-500/30",
    deleted:  "bg-red-500/20 text-red-400 border border-red-500/30",
  };

  // Normalisasi status/extension ke lowercase
  const key = status?.toLowerCase() || 'unknown';

  // Pilih style berdasarkan apakah ini extension atau status
  let style = extensionStyles[key] || statusStyles[key] || extensionStyles.unknown;

  // Jika variant outline (opsional)
  if (variant === 'outline') {
    style = style.replace(/\/20/g, '/10').replace(/bg-/g, 'border ').replace(/text-/g, 'text-');
  }

  return (
    <span className={`${baseClasses} ${style}`}>
      {children || key.toUpperCase()}
    </span>
  );
};

export default Badge;
