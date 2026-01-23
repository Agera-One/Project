import React, { useRef, useState } from 'react';

interface FileDropZoneProps {
    onFilesSelected: (files: File[]) => void;
    accept?: string;
    multiple?: boolean;
}

const FileDropZone: React.FC<FileDropZoneProps> = ({
    onFilesSelected,
    accept = ".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png",
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
        if (e.target.files && e.target.files.length > 0) {
            onFilesSelected(Array.from(e.target.files));
            e.target.value = ''; // reset input agar bisa upload file yang sama lagi
        }
    };

    return (
        <div
            onClick={handleClick}
            onDragOver={handleDragOver}
            onDragEnter={handleDragEnter}
            onDragLeave={handleDragLeave}
            onDrop={handleDrop}
            role="button"
            tabIndex={0}
            aria-label="Upload files area – drag & drop or click to select"
            className={`
                relative border-2 border-dashed rounded-2xl bg-[#1a1a1a]
                p-6 md:p-10 text-center cursor-pointer transition-all duration-300
                w-full max-w-[280px] mx-auto
                ${isDragging
                    ? 'border-[#00ff9d] bg-[#00ff9d]/10 scale-[1.02] shadow-2xl shadow-[#00ff9d]/30'
                    : 'border-[#444] hover:border-[#00ff9d]/70 hover:bg-[#1e1e1e]'
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
                aria-hidden="true"
            />

            <div className="flex flex-col items-center justify-center gap-3 select-none pointer-events-none">
                <div className="text-[#00ff9d] opacity-90">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v8M8 12h8" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                </div>

                <div>
                    <p className="text-sm font-semibold text-white">
                        Upload New File
                    </p>
                    <p className="text-xs text-gray-400 mt-1">
                        Drag & drop or click
                    </p>
                </div>
            </div>
        </div>
    );
};

export default FileDropZone;
