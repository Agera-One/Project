import { DocumentData } from '@/types/document'
import { DocumentListItem } from '@/types/document-list'
import { formatDate } from '@/utils/format-date'
import { formatFileSize } from '@/utils/format-file-sizes'

export function mapDocumentToListItem(doc: DocumentData): DocumentListItem {
    return {
        id: doc.id,
        title: doc.title,
        extension: doc.extension ?? 'unknown',
        size: formatFileSize(doc.size),
        date: doc.updated_at ? formatDate(doc.updated_at) : '-',
        status: doc.status,
    }
}

export function mapDocumentsToListItems(
    documents: DocumentData[]
): DocumentListItem[] {
    return documents.map(mapDocumentToListItem)
}
