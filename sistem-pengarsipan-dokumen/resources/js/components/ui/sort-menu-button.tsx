import { Button } from '@/components/ui/button';
import { SlidersHorizontal } from 'lucide-react';

function SortMenuButton() {
    return (
        <Button variant="ghost" className="!px-3 !py-1">
            <SlidersHorizontal size={20} />
            Sort
        </Button>
    );
}

export default SortMenuButton;
