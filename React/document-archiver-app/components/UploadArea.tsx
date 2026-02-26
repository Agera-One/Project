'use client';

import React from "react"

import { Upload, File } from 'lucide-react';
import { useState } from 'react';

export default function UploadArea() {
  const [dragActive, setDragActive] = useState(false);

  const handleDrag = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === 'dragenter' || e.type === 'dragover') {
      setDragActive(true);
    } else if (e.type === 'dragleave') {
      setDragActive(false);
    }
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);
    const files = e.dataTransfer.files;
    if (files && files.length > 0) {
      console.log('[v0] Files dropped:', files);
    }
  };

  return (
    <div
      onDragEnter={handleDrag}
      onDragLeave={handleDrag}
      onDragOver={handleDrag}
      onDrop={handleDrop}
      className={`border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors ${
        dragActive ? 'border-accent bg-accent/5' : 'border-border hover:border-accent/50'
      }`}
    >
      <div className="flex flex-col items-center gap-2">
        <div className="flex items-center justify-center w-12 h-12 rounded-full border-2 border-accent bg-accent/10">
          <Upload className="text-accent" size={24} />
        </div>
        <h3 className="font-semibold">Upload New File</h3>
        <p className="text-sm text-muted-foreground">Drag & drop your files here or click to browse</p>
      </div>
      <input
        type="file"
        multiple
        className="hidden"
        onChange={(e) => {
          if (e.target.files && e.target.files.length > 0) {
            console.log('[v0] Files selected:', e.target.files);
          }
        }}
      />
    </div>
  );
}
