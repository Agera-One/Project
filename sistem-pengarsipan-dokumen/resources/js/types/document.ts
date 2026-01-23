export interface DocumentData {
    id: number
    title: string
    size: number
    status: 'active' | 'archived' | 'deleted'
    created_at: string
    extension: string;
}

export interface Category {
    id: number
    name: string
}

