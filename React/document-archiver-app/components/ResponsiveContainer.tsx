import React from "react"
interface ResponsiveContainerProps {
  children: React.ReactNode;
  className?: string;
}

export default function ResponsiveContainer({ children, className = '' }: ResponsiveContainerProps) {
  return (
    <div className={`w-full mx-auto px-4 sm:px-6 md:px-8 py-6 md:py-8 max-w-7xl ${className}`}>
      {children}
    </div>
  );
}
