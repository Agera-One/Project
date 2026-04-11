-- ============================================================
-- PART 3: STORAGE SETUP — document-archiver
-- ============================================================
-- Run SQL below in Supabase Dashboard > SQL Editor
-- OR use Supabase CLI commands shown in comments
-- ============================================================


-- ─── Step 1: Create the bucket ──────────────────────────────
-- Via Dashboard: Storage > New Bucket > name="documents", Public=OFF
-- Via SQL:
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES (
    'documents',
    'documents',
    false,
    52428800,   -- 50 MB per file
    ARRAY[
        'application/pdf',
        'image/png',
        'image/jpeg',
        'image/jpg',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/msword',
        'application/vnd.ms-powerpoint',
        'application/vnd.ms-excel',
        'text/plain',
        'application/octet-stream'
    ]
)
ON CONFLICT (id) DO NOTHING;


-- ─── Step 2: Storage RLS Policies ───────────────────────────
-- Path convention: {owner_id}/{document_id}/{filename.ext}
-- RLS checks the first segment of the path equals auth.uid()

-- Allow authenticated users to UPLOAD to their own folder
CREATE POLICY "storage_insert_own"
    ON storage.objects
    FOR INSERT
    TO authenticated
    WITH CHECK (
        bucket_id = 'documents'
        AND (storage.foldername(name))[1] = auth.uid()::text
    );

-- Allow authenticated users to READ their own files
CREATE POLICY "storage_select_own"
    ON storage.objects
    FOR SELECT
    TO authenticated
    USING (
        bucket_id = 'documents'
        AND (storage.foldername(name))[1] = auth.uid()::text
    );

-- Allow authenticated users to UPDATE their own files
CREATE POLICY "storage_update_own"
    ON storage.objects
    FOR UPDATE
    TO authenticated
    USING (
        bucket_id = 'documents'
        AND (storage.foldername(name))[1] = auth.uid()::text
    );

-- Allow authenticated users to DELETE their own files
CREATE POLICY "storage_delete_own"
    ON storage.objects
    FOR DELETE
    TO authenticated
    USING (
        bucket_id = 'documents'
        AND (storage.foldername(name))[1] = auth.uid()::text
    );


-- ─── Step 3: CLI Commands (alternative) ─────────────────────
-- supabase storage create-bucket documents --no-public
-- Policies must still be applied via SQL Editor above.


-- ─── File Path Convention ───────────────────────────────────
-- Upload path: {uid}/{doc_uuid}/{original_filename_with_ext}
-- Example:     a1b2c3.../d4e5f6.../CV_Dzaki.pdf
--
-- When generating a signed URL in Flutter:
--   final url = await supabase.storage
--       .from('documents')
--       .createSignedUrl(filePath, 3600); // 1-hour expiry
