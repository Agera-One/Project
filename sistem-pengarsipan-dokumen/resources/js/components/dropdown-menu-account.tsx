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
import { router } from '@inertiajs/react';
import {
    Ban,
    CheckCircle2,
    EllipsisVertical,
    SquarePen,
    Trash2,
} from 'lucide-react';
import { useState } from 'react';
import { route } from 'ziggy-js';

interface Props {
    id: number;
    name: string;
    is_active: boolean;
}

export function DropdownMenuAccount({ id, name, is_active }: Props) {
    const [editOpen, setEditOpen] = useState(false);
    const [newName, setNewName] = useState(name);
    const [newPassword, setNewPassword] = useState('');

    const handleUpdate = () => {
        router.patch(route('account.update', id), {
            name: newName,
            password: newPassword || undefined,
        });
        setEditOpen(false);
    };

    const handleToggleActive = () => {
        router.patch(route('account.toggle-active', id));
    };

    const handleRemove = () => {
        if (!confirm('Yakin mau hapus user ini?')) return;
        router.delete(route('account.destroy', id));
    };

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon">
                        <EllipsisVertical className="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    align="start"
                    className="w-44 rounded-xl bg-background p-1 shadow-lg"
                >
                    {/* Edit */}
                    <DropdownMenuItem
                        className="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-accent"
                        onSelect={(e) => {
                            e.preventDefault();
                            setEditOpen(true);
                        }}
                    >
                        <SquarePen className="h-4 w-4" />
                        <span>Edit</span>
                    </DropdownMenuItem>

                    {/* Activate / Deactivate */}
                    {is_active ? (
                        <DropdownMenuItem
                            className="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-accent"
                            onSelect={(e) => {
                                e.preventDefault();
                                handleToggleActive();
                            }}
                        >
                            <Ban className="h-4 w-4" />
                            <span>Deactivate</span>
                        </DropdownMenuItem>
                    ) : (
                        <DropdownMenuItem
                            className="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-accent"
                            onSelect={(e) => {
                                e.preventDefault();
                                handleToggleActive();
                            }}
                        >
                            <CheckCircle2 className="h-4 w-4" />
                            <span>Activate</span>
                        </DropdownMenuItem>
                    )}

                    {/* Remove */}
                    <DropdownMenuItem
                        variant="destructive"
                        className="flex items-center gap-2 rounded-lg px-3 py-2 text-red-500 hover:bg-red-500/10 focus:text-red-500"
                        onSelect={(e) => {
                            e.preventDefault();
                            handleRemove();
                        }}
                    >
                        <Trash2 className="h-4 w-4" />
                        <span>Remove</span>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <Dialog open={editOpen} onOpenChange={setEditOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit User</DialogTitle>
                    </DialogHeader>

                    <div className="space-y-3">
                        <Input
                            placeholder="Username"
                            value={newName}
                            onChange={(e) => setNewName(e.target.value)}
                        />
                        <Input
                            type="password"
                            placeholder="Password baru (kosongkan jika tidak diubah)"
                            value={newPassword}
                            onChange={(e) => setNewPassword(e.target.value)}
                        />
                    </div>

                    <DialogFooter>
                        <Button
                            variant="secondary"
                            onClick={() => setEditOpen(false)}
                        >
                            Cancel
                        </Button>
                        <Button onClick={handleUpdate}>Save</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
