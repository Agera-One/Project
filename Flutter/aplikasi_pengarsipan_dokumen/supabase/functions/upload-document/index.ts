import { serve } from "https://deno.land/std@0.168.0/http/server.ts";
import { createClient } from "https://esm.sh/@supabase/supabase-js@2";

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Headers":
    "authorization, x-client-info, apikey, content-type",
};

serve(async (req) => {
  if (req.method === "OPTIONS") {
    return new Response("ok", { headers: corsHeaders });
  }

  try {
    // ── Auth ──────────────────────────────────────────────
    const authHeader = req.headers.get("Authorization");
    if (!authHeader) throw new Error("Missing Authorization header");

    const supabase = createClient(
      Deno.env.get("SUPABASE_URL")!,
      Deno.env.get("SUPABASE_ANON_KEY")!,
      { global: { headers: { Authorization: authHeader } } }
    );

    const {
      data: { user },
      error: authError,
    } = await supabase.auth.getUser();
    if (authError || !user) throw new Error("Unauthorized");

    // ── Parse multipart form data ─────────────────────────
    const form = await req.formData();
    const file = form.get("file") as File;
    const fileName = form.get("file_name") as string;       // without extension
    const extension = form.get("extension") as string;      // e.g. "pdf"
    const ownerName = form.get("owner_name") as string;
    const ownerEmail = form.get("owner_email") as string;
    const ownerInitials = form.get("owner_initials") as string;

    if (!file || !fileName || !extension) {
      throw new Error("Missing required fields: file, file_name, extension");
    }

    // ── Generate document ID & storage path ───────────────
    const docId = crypto.randomUUID();
    const originalName = `${fileName}.${extension}`;
    const storagePath = `${user.id}/${docId}/${originalName}`;
    const sizeKb = file.size / 1024;

    // ── Upload to Storage ─────────────────────────────────
    const { error: storageError } = await supabase.storage
      .from("documents")
      .upload(storagePath, file, {
        contentType: file.type,
        upsert: false,
      });

    if (storageError) throw new Error(`Storage upload failed: ${storageError.message}`);

    // ── Insert into documents table ───────────────────────
    const { data: doc, error: dbError } = await supabase
      .from("documents")
      .insert({
        id: docId,
        file_name: fileName,
        owner_id: user.id,
        owner_name: ownerName || user.user_metadata?.full_name || "",
        owner_email: ownerEmail || user.email || "",
        owner_initials: ownerInitials || "",
        extension: extension,
        size_kb: sizeKb,
        date_modified: new Date().toISOString(),
        status: "active",
        is_starred: false,
        file_path: storagePath,
      })
      .select()
      .single();

    if (dbError) {
      // Rollback: remove uploaded file
      await supabase.storage.from("documents").remove([storagePath]);
      throw new Error(`DB insert failed: ${dbError.message}`);
    }

    return new Response(JSON.stringify({ success: true, document: doc }), {
      headers: { ...corsHeaders, "Content-Type": "application/json" },
      status: 200,
    });
  } catch (err) {
    return new Response(
      JSON.stringify({ success: false, error: (err as Error).message }),
      {
        headers: { ...corsHeaders, "Content-Type": "application/json" },
        status: 400,
      }
    );
  }
});