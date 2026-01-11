import React, { useRef, useState } from 'react';

interface FileDropZoneProps {
    onFilesSelected: (files: File[]) => void;
    accept?: string;
    multiple?: boolean;
}

const FileDropZone: React.FC<FileDropZoneProps> = ({
    onFilesSelected,
    accept = ".pdf,.doc,.docx,.jpg,.jpeg,.png",
    multiple = true,
}) => {
    const [isDragging, setIsDragging] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
    };

    const handleDragEnter = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(true);
    };

    const handleDragLeave = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);
    };

    const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);

        const files = Array.from(e.dataTransfer.files);
        if (files.length > 0) {
            onFilesSelected(files);
        }
    };

    const handleClick = () => fileInputRef.current?.click();

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            onFilesSelected(Array.from(e.target.files));
            e.target.value = ''; // reset agar bisa upload file yang sama lagi
        }
    };

    return (
            <div
                onClick={handleClick}
                onDragOver={handleDragOver}
                onDragEnter={handleDragEnter}
                onDragLeave={handleDragLeave}
                onDrop={handleDrop}
                className={`
      relative border-2 border-dashed rounded-2xl bg-[#1a1a1a]
      p-12 text-center cursor-pointer transition-all duration-300
      max-w-md w-full h-40 mx-auto mb-4
      ${isDragging
                        ? 'border-[#00ff9d] bg-[#00ff9d]/5 scale-[1.02] shadow-2xl shadow-[#00ff9d]/20'
                        : 'border-[#444] hover:border-[#00ff9d] hover:bg-sidebar-accent'
                    }
    `}
            >
                <input
                    ref={fileInputRef}
                    type="file"
                    accept={accept}
                    multiple={multiple}
                    onChange={handleChange}
                    className="hidden"
                />

                {/* Tambah h-full supaya flex justify-center bekerja 100% */}
                <div className="flex flex-col items-center justify-center h-full">
                    {/* Ikon Plus Hijau */}
                    <div className="mb-4">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" className="drop-shadow-lg">
                            <circle cx="12" cy="12" r="10" stroke="#00ff9d" strokeWidth="2" className="opacity-90" />
                            <path d="M12 8v8M8 12h8" stroke="#00ff9d" strokeWidth="2.5" strokeLinecap="round" />
                        </svg>
                    </div>

                    <p className="text-sm font-semibold text-white mb-1">
                        Uploads New File
                    </p>
                    <p className="text-sm text-gray-400">
                        Drag and Drop
                    </p>
                </div>
            </div>
    );
};

export default FileDropZone;
