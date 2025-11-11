INSERT INTO artworks (uuid, title, slug, description, year, technique, dimensions, price, currency, image_path, thumbnail_path, webp_path, is_featured, is_published, display_order, metadata)
VALUES
    ('11111111-1111-1111-1111-111111111111', 'Placeholder Sunrise', 'placeholder-sunrise', 'Warm gradients representing a sunrise over the Bournemouth coast.', 2023, 'Acrylic on canvas', '60 x 80 cm', NULL, 'GBP', 'uploads/sample-01.png', 'uploads/thumbs/sample-01.png', NULL, 1, 1, 3, '{"technique":"Acrylic on canvas","dimensions":"60 x 80 cm"}'),
    ('22222222-2222-2222-2222-222222222222', 'Azure Coastline', 'azure-coastline', 'Cool-toned study with layered blues and subtle texture.', 2022, 'Oil on canvas', '50 x 70 cm', NULL, 'GBP', 'uploads/sample-02.png', 'uploads/thumbs/sample-02.png', NULL, 0, 1, 2, '{"technique":"Oil on canvas","dimensions":"50 x 70 cm"}'),
    ('33333333-3333-3333-3333-333333333333', 'Monochrome Sketch', 'monochrome-sketch', 'Quick monochrome study hinting at future commissions.', 2024, 'Charcoal and gesso', '42 x 59 cm', NULL, 'GBP', 'uploads/sample-03.png', 'uploads/thumbs/sample-03.png', NULL, 0, 1, 1, '{"technique":"Charcoal and gesso","dimensions":"42 x 59 cm"}')
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    technique = VALUES(technique),
    dimensions = VALUES(dimensions),
    is_featured = VALUES(is_featured),
    display_order = VALUES(display_order);
