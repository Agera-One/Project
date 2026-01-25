import { DropdownMenuItem } from '@/components/ui/dropdown-menu'
import { Toggle } from '@/components/ui/toggle'


export default function SortMenuContent() {
    return (
        <>
            <DropdownMenuItem asChild>
                <Toggle className="w-full justify-start">
                    Sort by name (A–Z)
                </Toggle>
            </DropdownMenuItem>

            <DropdownMenuItem asChild>
                <Toggle className="w-full justify-start">
                    Sort by name (Z–A)
                </Toggle>
            </DropdownMenuItem>

            <DropdownMenuItem asChild>
                <Toggle className="w-full justify-start">
                    Sort by date (newest first)
                </Toggle>
            </DropdownMenuItem>

            <DropdownMenuItem asChild>
                <Toggle className="w-full justify-start">
                    Sort by date (oldest first)
                </Toggle>
            </DropdownMenuItem>
        </>
    )
}
