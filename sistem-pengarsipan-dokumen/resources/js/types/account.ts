export interface ListAccountItem {
    id: number
    role: 'user' | 'admin'
    name: string
    email: string
    created_at: string
    is_active: boolean
}
