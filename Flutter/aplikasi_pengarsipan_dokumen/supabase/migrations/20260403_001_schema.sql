-- ============================================================
-- Document Archiver — Supabase Migration
-- Project: document-archiver
-- ============================================================

-- ─── 1. ENUM TYPES ──────────────────────────────────────────
CREATE TYPE document_status AS ENUM ('active', 'archived', 'deleted');
CREATE TYPE file_extension  AS ENUM ('pdf', 'png', 'docx', 'pptx', 'xlsx', 'jpg', 'txt', 'other');


-- ─── 2. MAIN TABLE ──────────────────────────────────────────
CREATE TABLE public.documents (
    id             UUID              PRIMARY KEY DEFAULT gen_random_uuid(),
    file_name      TEXT              NOT NULL,
    owner_id       UUID              NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
    owner_name     TEXT              NOT NULL DEFAULT '',
    owner_email    TEXT              NOT NULL DEFAULT '',
    owner_initials TEXT              NOT NULL DEFAULT '',
    extension      file_extension    NOT NULL DEFAULT 'other',
    size_kb        DOUBLE PRECISION  NOT NULL DEFAULT 0,
    date_modified  TIMESTAMPTZ       NOT NULL DEFAULT now(),
    status         document_status   NOT NULL DEFAULT 'active',
    is_starred     BOOLEAN           NOT NULL DEFAULT false,
    file_path      TEXT              NOT NULL DEFAULT '',   -- {owner_id}/{document_id}/{file_name.ext}
    created_at     TIMESTAMPTZ       NOT NULL DEFAULT now(),
    updated_at     TIMESTAMPTZ       NOT NULL DEFAULT now()
);

-- Indexes for common query patterns
CREATE INDEX idx_documents_owner_id      ON public.documents (owner_id);
CREATE INDEX idx_documents_status        ON public.documents (status);
CREATE INDEX idx_documents_is_starred    ON public.documents (is_starred);
CREATE INDEX idx_documents_date_modified ON public.documents (date_modified DESC);
CREATE INDEX idx_documents_extension     ON public.documents (extension);
-- Full-text search index on file_name
CREATE INDEX idx_documents_file_name_fts ON public.documents
    USING GIN (to_tsvector('english', file_name));


-- ─── 3. TRIGGER: auto-update updated_at ─────────────────────
CREATE OR REPLACE FUNCTION public.handle_updated_at()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_documents_updated_at
    BEFORE UPDATE ON public.documents
    FOR EACH ROW EXECUTE FUNCTION public.handle_updated_at();


-- ─── 4. FUNCTION: total storage per user (in KB) ────────────
CREATE OR REPLACE FUNCTION public.get_user_storage_kb(p_user_id UUID)
RETURNS DOUBLE PRECISION
LANGUAGE sql STABLE SECURITY DEFINER AS $$
    SELECT COALESCE(SUM(size_kb), 0)
    FROM public.documents
    WHERE owner_id = p_user_id
      AND status = 'active';
$$;


-- ─── 5. FUNCTION: storage breakdown by extension ────────────
-- Returns JSON: { "pdf": 1024.5, "png": 204.8, ... }
CREATE OR REPLACE FUNCTION public.get_storage_by_extension(p_user_id UUID)
RETURNS JSON
LANGUAGE sql STABLE SECURITY DEFINER AS $$
    SELECT json_object_agg(extension, total_kb)
    FROM (
        SELECT extension::text, SUM(size_kb) AS total_kb
        FROM public.documents
        WHERE owner_id = p_user_id
          AND status = 'active'
        GROUP BY extension
    ) sub;
$$;


-- ─── 6. FUNCTION: recent documents (last 20, non-deleted) ───
CREATE OR REPLACE FUNCTION public.get_recent_documents(p_user_id UUID)
RETURNS SETOF public.documents
LANGUAGE sql STABLE SECURITY DEFINER AS $$
    SELECT *
    FROM public.documents
    WHERE owner_id = p_user_id
      AND status != 'deleted'
    ORDER BY date_modified DESC
    LIMIT 20;
$$;


-- ─── 7. FUNCTION: new documents this week count ─────────────
CREATE OR REPLACE FUNCTION public.get_new_this_week_count(p_user_id UUID)
RETURNS BIGINT
LANGUAGE sql STABLE SECURITY DEFINER AS $$
    SELECT COUNT(*)
    FROM public.documents
    WHERE owner_id = p_user_id
      AND status = 'active'
      AND date_modified >= now() - INTERVAL '7 days';
$$;


-- ============================================================
-- ROW LEVEL SECURITY POLICIES
-- ============================================================

ALTER TABLE public.documents ENABLE ROW LEVEL SECURITY;

-- SELECT: user can only read their own documents
CREATE POLICY "documents_select_own"
    ON public.documents
    FOR SELECT
    USING (owner_id = auth.uid());

-- INSERT: user can only insert documents with their own owner_id
CREATE POLICY "documents_insert_own"
    ON public.documents
    FOR INSERT
    WITH CHECK (owner_id = auth.uid());

-- UPDATE: user can only update their own documents
CREATE POLICY "documents_update_own"
    ON public.documents
    FOR UPDATE
    USING (owner_id = auth.uid())
    WITH CHECK (owner_id = auth.uid());

-- DELETE: user can only delete their own documents
CREATE POLICY "documents_delete_own"
    ON public.documents
    FOR DELETE
    USING (owner_id = auth.uid());


-- ============================================================
-- STORAGE BUCKET: documents
-- Run these in Supabase Dashboard > Storage > New Bucket
-- OR via CLI (see Part 3 guide)
-- ============================================================

-- Storage RLS policies are configured in Part 3 (Storage Setup).
-- The SQL below is for reference — apply via Dashboard or CLI.

/*
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES (
    'documents',
    'documents',
    false,               -- private bucket
    52428800,            -- 50 MB per file max
    ARRAY[
        'application/pdf',
        'image/png',
        'image/jpeg',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/msword',
        'application/vnd.ms-powerpoint',
        'application/vnd.ms-excel',
        'text/plain',
        'application/octet-stream'
    ]
);
*/
