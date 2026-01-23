export function formatDate(date?: string | null): string {
    if (!date) return '-'

    const parsed = new Date(date)
    if (isNaN(parsed.getTime())) return '-'

    return parsed.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}
