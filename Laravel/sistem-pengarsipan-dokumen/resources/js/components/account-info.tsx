import { AvatarWithBadge } from './ui/avatar-badge';

interface AccountProps {
    id: number;
    name: string;
    email: string;
}

export function AccountInfo({ name, email }: AccountProps) {
    return (
        <div className="flex items-center gap-3">
            <AvatarWithBadge name={name} />

            <div className="leading-tight">
                <div className="text-sm font-medium text-gray-300">{name}</div>
                <div className="text-xs text-gray-400">{email}</div>
            </div>
        </div>
    );
}
