export interface DocumentListItem {
  id: number
  title: string
  extension: string
  size: string
  date: string
  status: 'active' | 'archived' | 'deleted'
  is_starred: boolean
  is_archived: boolean
}

