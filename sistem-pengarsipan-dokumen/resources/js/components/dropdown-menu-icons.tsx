import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { DocumentListItem } from '@/types/document-list';
import { router, usePage } from '@inertiajs/react';
import {
    Archive,
    Download,
    EllipsisVertical,
    Eye,
    RotateCcw,
    SquarePen,
    Star,
    Trash2,
} from 'lucide-react';
import { useState } from 'react';
import { route } from 'ziggy-js';

interface Props {
    document: DocumentListItem;
}

export function DropdownMenuIcons({ document }: Props) {
    const [editOpen, setEditOpen] = useState(false);
    const [previewAlertOpen, setPreviewAlertOpen] = useState(false);
    const [newTitle, setNewTitle] = useState(document.title);
    const { props }: any = usePage();
    const role = props.auth?.user?.role;
    const isAdmin = role === 'admin';

    const officeExtensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

    const handleEdit = () => {
        router.patch(route('documents.update', document.id), {
            title: newTitle,
        });
        setEditOpen(false);
    };

    const handleView = () => {
        const ext = document.extension.toLowerCase();

        if (officeExtensions.includes(ext)) {
            setPreviewAlertOpen(true);
            return;
        }

        // previewable file (pdf / image)
        window.open(route('documents.show', document.id), '_blank');
    };

    /* ===========================
        TRASH (DELETED DOCUMENT)
    ============================ */
    if (document.deleted_at !== null) {
        return (
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon">
                        <EllipsisVertical className="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent align="start" className="bg-background">
                    <DropdownMenuItem
                        onSelect={(e) => {
                            e.preventDefault();
                            router.post(
                                route('documents.restore', document.id),
                            );
                        }}
                    >
                        <RotateCcw className="mr-2 h-4 w-4" />
                        Restore
                    </DropdownMenuItem>

                    <DropdownMenuItem
                        variant="destructive"
                        onSelect={(e) => {
                            e.preventDefault();
                            router.delete(
                                route('documents.forceDelete', document.id),
                            );
                        }}
                    >
                        <Trash2 className="mr-2 h-4 w-4" />
                        Delete Permanently
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        );
    }

    /* ===========================
        NORMAL DOCUMENT
    ============================ */
    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon">
                        <EllipsisVertical className="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent align="start" className="bg-background">
                    <DropdownMenuItem
                        className="hover:!bg-sidebar-accent"
                        onSelect={(e) => {
                            e.preventDefault();
                            handleView();
                        }}
                    >
                        <Eye className="mr-2 h-4 w-4" />
                        View
                    </DropdownMenuItem>

                    <DropdownMenuItem
                        className="hover:!bg-sidebar-accent"
                        asChild
                    >
                        <a
                            href={document.file_url}
                            download
                            onClick={(e) => e.stopPropagation()}
                        >
                            <Download className="mr-2 h-4 w-4" />
                            Download
                        </a>
                    </DropdownMenuItem>

                    <DropdownMenuItem
                        className="hover:!bg-sidebar-accent"
                        onSelect={(e) => {
                            e.preventDefault();
                            setEditOpen(true);
                        }}
                    >
                        <SquarePen className="mr-2 h-4 w-4" />
                        Edit
                    </DropdownMenuItem>

                    {!isAdmin && (
                        <DropdownMenuItem
                            className="hover:!bg-sidebar-accent"
                            onSelect={(e) => {
                                e.preventDefault();
                                router.patch(
                                    route('documents.star', document.id),
                                );
                            }}
                        >
                            <Star
                                className="mr-2 h-4 w-4"
                                fill={
                                    document.is_starred
                                        ? 'currentColor'
                                        : 'none'
                                }
                            />
                            {document.is_starred ? 'Unstar' : 'Star'}
                        </DropdownMenuItem>
                    )}

                    {!isAdmin && (
                        <DropdownMenuItem
                            className="hover:!bg-sidebar-accent"
                            onSelect={(e) => {
                                e.preventDefault();
                                router.patch(
                                    route('documents.archive', document.id),
                                );
                            }}
                        >
                            <Archive className="mr-2 h-4 w-4" />
                            {document.is_archived ? 'Unarchive' : 'Archive'}
                        </DropdownMenuItem>
                    )}

                    <DropdownMenuItem
                        variant="destructive"
                        onSelect={(e) => {
                            e.preventDefault();
                            router.delete(
                                route('documents.destroy', document.id),
                            );
                        }}
                    >
                        <Trash2 className="mr-2 h-4 w-4" />
                        Remove
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            {/* ===========================
                EDIT TITLE DIALOG
            ============================ */}
            <Dialog open={editOpen} onOpenChange={setEditOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Title</DialogTitle>
                    </DialogHeader>

                    <Input
                        value={newTitle}
                        onChange={(e) => setNewTitle(e.target.value)}
                    />

                    <DialogFooter>
                        <Button
                            variant="secondary"
                            onClick={() => setEditOpen(false)}
                        >
                            Cancel
                        </Button>
                        <Button onClick={handleEdit}>Save</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* ===========================
                PREVIEW WARNING DIALOG
            ============================ */}
            <Dialog open={previewAlertOpen} onOpenChange={setPreviewAlertOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Preview tidak tersedia</DialogTitle>
                    </DialogHeader>

                    <p className="text-sm text-muted-foreground">
                        Dokumen dengan format{' '}
                        <strong>.{document.extension}</strong> tidak dapat
                        ditampilkan langsung di browser.
                        <br />
                        Apakah kamu ingin mengunduh dokumen ini?
                    </p>

                    <DialogFooter>
                        <Button
                            variant="secondary"
                            onClick={() => setPreviewAlertOpen(false)}
                        >
                            Cancel
                        </Button>

                        <Button
                            onClick={() => {
                                window.open(document.file_url, '_blank');
                                setPreviewAlertOpen(false);
                            }}
                        >
                            Download
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
