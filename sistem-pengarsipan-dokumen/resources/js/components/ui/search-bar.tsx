import { Search } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

export function SearchBar() {
    return (
        <div className={cn(
            'relative',
            'w-full',
        )}>
            <Search className="absolute left-2 top-1/2 size-4 -translate-y-1/2 text-neutral-500" />
            <Input
                type="search"
                placeholder="Search..."
                className="pl-8 pr-4 py-2 min-w-[1180px] w-full"
            />
        </div>
    );
}

