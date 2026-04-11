import { serve } from "https://deno.land/std@0.168.0/http/server.ts";
import { createClient } from "https://esm.sh/@supabase/supabase-js@2";

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Headers": "authorization, x-client-info, apikey, content-type",
};

serve(async (req) => {
  if (req.method === "OPTIONS") return new Response("ok", { headers: corsHeaders });

  try {
    const authHeader = req.headers.get("Authorization");
    if (!authHeader) throw new Error("Missing Authorization header");

    const supabase = createClient(
      Deno.env.get("SUPABASE_URL")!,
      Deno.env.get("SUPABASE_ANON_KEY")!,
      { global: { headers: { Authorization: authHeader } } }
    );

    const { data: { user }, error: authError } = await supabase.auth.getUser();
    if (authError || !user) throw new Error("Unauthorized");

    const { document_id } = await req.json();
    if (!document_id) throw new Error("Missing document_id");

    // Fetch document to verify ownership and get file_path
    const { data: doc, error: fetchError } = await supabase
      .from("documents")
      .select("id, owner_id, file_path")
      .eq("id", document_id)
      .single();

    if (fetchError || !doc) throw new Error("Document not found");
    if (doc.owner_id !== user.id) throw new Error("Forbidden");

    // Delete from Storage
    if (doc.file_path) {
      const { error: storageError } = await supabase.storage
        .from("documents")
        .remove([doc.file_path]);
      if (storageError) console.warn("Storage delete warning:", storageError.message);
    }

    // Delete from DB
    const { error: dbError } = await supabase
      .from("documents")
      .delete()
      .eq("id", document_id);

    if (dbError) throw new Error(`DB delete failed: ${dbError.message}`);

    return new Response(JSON.stringify({ success: true }), {
      headers: { ...corsHeaders, "Content-Type": "application/json" },
      status: 200,
    });
  } catch (err) {
    return new Response(
      JSON.stringify({ success: false, error: (err as Error).message }),
      { headers: { ...corsHeaders, "Content-Type": "application/json" }, status: 400 }
    );
  }
});