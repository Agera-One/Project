import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import SortMenuButton from '@/components/ui/sort-menu-button';
import SortMenuContent from '@/components/sort-menu-content';

export function SortMenu() {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger>
                <SortMenuButton />
            </DropdownMenuTrigger>
            <DropdownMenuContent>
                <SortMenuContent />
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
