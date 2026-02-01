export interface DocumentStatItem {
    value: number
    change: number
}

export interface DocumentStats {
    active: DocumentStatItem
    archived: DocumentStatItem
    deleted: DocumentStatItem
}

export type FileSizeUnit = 'B' | 'KB' | 'MB' | 'GB'

export interface ExtensionStat {
    key: string
    extension: string
    files: number
    size: number
    unit: FileSizeUnit
    percent: number
}

export interface StorageStat {
    value: number
    unit: FileSizeUnit
}
