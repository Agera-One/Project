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
import { DocumentData } from '@/types/document';
import { router } from '@inertiajs/react';
import {
    Archive,
    Download,
    EllipsisVertical,
    Eye,
    SquarePen,
    Star,
    Trash2,
} from 'lucide-react';
import { useState } from 'react';
import { route } from 'ziggy-js';

interface Props {
    document: DocumentData;
}

export function DropdownMenuIcons({ document }: Props) {
    const [editOpen, setEditOpen] = useState(false);
    const [newTitle, setNewTitle] = useState(document.title);

    const handleEdit = () => {
        router.patch(route('documents.update', document.id), {
            title: newTitle,
        });
        setEditOpen(false);
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon">
                    <EllipsisVertical className="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem
                    onSelect={(e) => {
                        e.preventDefault();
                        router.get(route('documents.show', document.id));
                    }}
                >
                    <Eye className="mr-2 h-4 w-4" />
                    View
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
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
                    onSelect={(e) => {
                        e.preventDefault();
                        setEditOpen(true);
                    }}
                >
                    <SquarePen className="mr-2 h-4 w-4" />
                    Edit
                </DropdownMenuItem>

                <DropdownMenuItem
                    onSelect={(e) => {
                        e.preventDefault();
                        router.patch(route('documents.star', document.id));
                    }}
                >
                    <Star
                        className="mr-2 h-4 w-4"
                        fill={document.is_starred ? 'currentColor' : 'none'}
                    />
                    {document.is_starred ? 'Unstar' : 'Star'}
                </DropdownMenuItem>

                <DropdownMenuItem
                    onSelect={(e) => {
                        e.preventDefault();
                        router.patch(route('documents.archive', document.id));
                    }}
                >
                    <Archive className="mr-2 h-4 w-4" />
                    {document.is_archived ? 'Unarchive' : 'Archive'}
                </DropdownMenuItem>

                <DropdownMenuItem
                    variant="destructive"
                    onSelect={(e) => {
                        e.preventDefault();
                        router.delete(route('documents.destroy', document.id));
                    }}
                >
                    <Trash2 className="mr-2 h-4 w-4" />
                    Remove
                </DropdownMenuItem>
            </DropdownMenuContent>

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
        </DropdownMenu>
    );
}
