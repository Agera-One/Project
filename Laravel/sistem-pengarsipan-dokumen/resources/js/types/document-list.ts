export interface DocumentOwner {
    id: number
    name: string
    email: string
}

export interface DocumentListItem {
    id: number
    title: string
    extension: string
    size: number
    status: 'active' | 'archived' | 'deleted'
    updated_at: string
    deleted_at: string | null
    is_starred: boolean
    is_archived: boolean
    file_url: string | null
    owner: DocumentOwner | null
}
