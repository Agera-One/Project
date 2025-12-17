<script>
    window.applicants = @json($lamarans ?? collect());
    window.currentLowongan = @json($lowongan ?? null);
</script>
