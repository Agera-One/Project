import { useCallback } from 'react';

export function useInitials() {
    return useCallback((fullName: string): string => {
        const names = fullName.trim().split(' ');

        if (names.length === 0) return '';
        if (names.length === 1) return names[0].charAt(0).toUpperCase();

        const firstInitial = names[0].charAt(0);
        const secondInitial = names[1].charAt(0);

        return `${firstInitial}${secondInitial}`.toUpperCase();
    }, []);
}
