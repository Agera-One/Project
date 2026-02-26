import { usePage } from '@inertiajs/react';

export function usePageContext() {
    const { component } = usePage();

    if (component.startsWith('Account')) {
        return 'account';
    }

    return 'default';
}
