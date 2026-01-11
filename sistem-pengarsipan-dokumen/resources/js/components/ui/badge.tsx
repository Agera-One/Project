import React from 'react';

interface BadgeProps {
  status: string;
  children?: React.ReactNode;
}

const Badge = ({ status, children }: BadgeProps) => {
  const baseClasses = "px-3 py-1 rounded-full text-xs font-semibold tracking-wide";

  const badgeStyle = {
    docx      : "bg-blue-500/20 text-blue-500",
    pptx      : "bg-orange-500/20 text-orange-500",
    xlsx      : "bg-emerald-500/20 text-emerald-500",
    pdf       : "bg-red-500/20 text-red-500",
    jpg       : "bg-purple-500/20 text-purple-500",
    png       : "bg-cyan-500/20 text-cyan-500",
    active    : "bg-green-500/20 text-green-500",
    archived  : "bg-yellow-500/20 text-yellow-500",
    deleted   : "bg-red-500/20 text-red-500",
    unknown   : "bg-gray-500/20 text-gray-500",
  };

  const style = badgeStyle[status as keyof typeof badgeStyle] || badgeStyle.unknown;

  return (
    <span className={`${baseClasses} ${style}`}>
      {children || status.toUpperCase()}
    </span>
  );
};

export default Badge;
