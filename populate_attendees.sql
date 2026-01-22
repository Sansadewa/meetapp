-- Query to populate rapat_user table with unit_kerja attendees for all existing meetings
-- This ensures every meeting has at least its own unit_kerja as an attendee

-- Option 1: Insert only if the record doesn't already exist (RECOMMENDED)
INSERT INTO rapat_user (rapat_id, attendee_id, attendee_type)
SELECT 
    r.id AS rapat_id,
    r.unit_kerja AS attendee_id,
    'App\UnitKerjaModel' AS attendee_type
FROM rapat r
WHERE r.unit_kerja IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 
      FROM rapat_user ru 
      WHERE ru.rapat_id = r.id 
        AND ru.attendee_id = r.unit_kerja 
        AND ru.attendee_type = 'App\UnitKerjaModel'
  );

-- Option 2: If you want to see what will be inserted first (run this to preview)
-- SELECT 
--     r.id AS rapat_id,
--     r.unit_kerja AS attendee_id,
--     'App\UnitKerjaModel' AS attendee_type,
--     r.nama AS nama_rapat
-- FROM rapat r
-- WHERE r.unit_kerja IS NOT NULL
--   AND NOT EXISTS (
--       SELECT 1 
--       FROM rapat_user ru 
--       WHERE ru.rapat_id = r.id 
--         AND ru.attendee_id = r.unit_kerja 
--         AND ru.attendee_type = 'App\UnitKerjaModel'
--   );

