-- fix_encoded_data.sql
-- Perbaiki data yang sudah tersimpan dengan HTML entities
-- karena admin.php sebelumnya menggunakan htmlspecialchars() saat INSERT.
-- Jalankan SEKALI saja setelah deploy fix.

-- Fix FAQ text yang ter-encode
UPDATE faq_toko SET
    pertanyaan = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        pertanyaan,
        '&amp;', '&'),
        '&lt;', '<'),
        '&gt;', '>'),
        '&quot;', '"'),
        '&#039;', ''''),
    jawaban = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        jawaban,
        '&amp;', '&'),
        '&lt;', '<'),
        '&gt;', '>'),
        '&quot;', '"'),
        '&#039;', '''');

-- Fix AI persona prompt yang ter-encode
UPDATE toko SET
    ai_persona_prompt = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        ai_persona_prompt,
        '&amp;', '&'),
        '&lt;', '<'),
        '&gt;', '>'),
        '&quot;', '"'),
        '&#039;', '''')
WHERE ai_persona_prompt IS NOT NULL;

-- Fix product data yang ter-encode
UPDATE produk SET
    nama_produk = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        nama_produk,
        '&amp;', '&'),
        '&lt;', '<'),
        '&gt;', '>'),
        '&quot;', '"'),
        '&#039;', ''''),
    deskripsi = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        deskripsi,
        '&amp;', '&'),
        '&lt;', '<'),
        '&gt;', '>'),
        '&quot;', '"'),
        '&#039;', '''');

-- Fix category names yang ter-encode
UPDATE kategori SET
    nama_kategori = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        nama_kategori,
        '&amp;', '&'),
        '&lt;', '<'),
        '&gt;', '>'),
        '&quot;', '"'),
        '&#039;', '''');

-- Fix toko profile data
UPDATE toko SET
    nama_toko = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        nama_toko,
        '&amp;', '&'),
        '&lt;', '<'),
        '&gt;', '>'),
        '&quot;', '"'),
        '&#039;', ''''),
    deskripsi_landing = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        COALESCE(deskripsi_landing, ''),
        '&amp;', '&'),
        '&lt;', '<'),
        '&gt;', '>'),
        '&quot;', '"'),
        '&#039;', '''')
WHERE deskripsi_landing IS NOT NULL;

SELECT 'Done! Data sudah diperbaiki.' AS status;
