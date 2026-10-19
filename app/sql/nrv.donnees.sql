-- Utilisateurs
INSERT INTO utilisateur (id, nom, prenom, mail, telephone, mot_de_passe, role) VALUES
('11111111-0000-0000-0000-000000000001', 'Martin',  'Lucie', 'lucie.martin@example.com', '0612345678', '$2y$10$OURn2gDBWVo4a9FpA/KSY.q5VHIe0QncWR..2U6s3o8KVLBMuDFqW', 'client'),
('11111111-0000-0000-0000-000000000002', 'Durand',  'Karim', 'karim.durand@example.com', '0687654321', '$2y$10$OURn2gDBWVo4a9FpA/KSY.q5VHIe0QncWR..2U6s3o8KVLBMuDFqW', 'client'),
('11111111-0000-0000-0000-000000000003', 'Petit',   'Sophie', 'orga@nrv.net',            NULL,         '$2y$10$OURn2gDBWVo4a9FpA/KSY.q5VHIe0QncWR..2U6s3o8KVLBMuDFqW', 'organisateur'),
('11111111-0000-0000-0000-000000000004', 'Admin',   'Nrv',    'admin@nrv.net',           NULL,         '$2y$10$OURn2gDBWVo4a9FpA/KSY.q5VHIe0QncWR..2U6s3o8KVLBMuDFqW', 'administrateur');

-- Soirées (festival de 2 semaines)
INSERT INTO soiree (id, nom, thematique, date, lieu, capacite) VALUES
('22222222-0000-0000-0000-000000000001', 'Ouverture Électrique',  'Classic rock', '2027-06-04 20:00', 'Place Stanislas',     800),
('22222222-0000-0000-0000-000000000002', 'Blues sur la Meurthe',  'Blues',        '2027-06-06 19:30', 'Parc de la Pépinière', 500),
('22222222-0000-0000-0000-000000000003', 'Nuit Métal',            'Métal',        '2027-06-09 20:30', 'Zénith de Nancy',     1200),
('22222222-0000-0000-0000-000000000004', 'Chansons d''Ici',       'Chanson',      '2027-06-11 20:00', 'Opéra national',       400),
('22222222-0000-0000-0000-000000000005', 'Mix & Rock',            'Mélange',      '2027-06-13 19:00', 'Parc de la Pépinière', 600),
('22222222-0000-0000-0000-000000000006', 'Grande Finale',         'Rock',         '2027-06-17 20:00', 'Zénith de Nancy',     1200),
('22222222-0000-0000-0000-000000000007', 'Session Intimiste',     'Blues rock',   '2027-06-15 21:00', 'Café Le Terminus',       4);

-- Artistes
INSERT INTO artiste (id, nom_scene) VALUES
('33333333-0000-0000-0000-000000000001', 'The Stanislas Boys'),
('33333333-0000-0000-0000-000000000002', 'Route 54'),
('33333333-0000-0000-0000-000000000003', 'Blue Meurthe'),
('33333333-0000-0000-0000-000000000004', 'Mama Delta'),
('33333333-0000-0000-0000-000000000005', 'Iron Lorraine'),
('33333333-0000-0000-0000-000000000006', 'Black Tide'),
('33333333-0000-0000-0000-000000000007', 'Hélène Verlaine'),
('33333333-0000-0000-0000-000000000008', 'Les Chardons'),
('33333333-0000-0000-0000-000000000009', 'Neon Nancy'),
('33333333-0000-0000-0000-000000000010', 'Pierre Lambert'),
('33333333-0000-0000-0000-000000000011', 'Static Fire');

-- Spectacles (styles validés côté métier)
INSERT INTO spectacle (id, soiree_id, style, titre, description, chemin_image, chemin_video, horaire) VALUES
-- Soirée 1
('44444444-0000-0000-0000-000000000001', '22222222-0000-0000-0000-000000000001', 'classic rock', 'Retour aux sources',   'Les grands classiques du rock des années 70.', 'img/retour-aux-sources.jpg', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-04 20:30'),
('44444444-0000-0000-0000-000000000002', '22222222-0000-0000-0000-000000000001', 'classic rock', 'Highway Night',        'Un set énergique sur la route 54.',             'img/highway-night.jpg',      'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-04 22:00'),
-- Soirée 2
('44444444-0000-0000-0000-000000000003', '22222222-0000-0000-0000-000000000002', 'blues rock',    'Blues de la rivière', 'Du blues rock chaud au bord de l''eau.',        'img/blues-riviere.jpg',      'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-06 20:00'),
('44444444-0000-0000-0000-000000000004', '22222222-0000-0000-0000-000000000002', 'blues rock',    'Delta Stories',       'Racines du delta et slide guitar.',             'img/delta-stories.jpg',      'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-06 21:30'),
-- Soirée 3
('44444444-0000-0000-0000-000000000005', '22222222-0000-0000-0000-000000000003', 'métal',         'Forge d''acier',      'Riffs lourds et batterie double pédale.',       'img/forge-acier.jpg',        'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-09 21:00'),
('44444444-0000-0000-0000-000000000006', '22222222-0000-0000-0000-000000000003', 'métal',         'Marée noire',         'Un metal sombre et atmosphérique.',             'img/maree-noire.jpg',        'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-09 22:30'),
-- Soirée 4
('44444444-0000-0000-0000-000000000007', '22222222-0000-0000-0000-000000000004', 'chanson',       'Poèmes en musique',   'Textes et mélodies intimistes.',                'img/poemes-musique.jpg',     'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-11 20:30'),
('44444444-0000-0000-0000-000000000008', '22222222-0000-0000-0000-000000000004', 'chanson',       'Folk des chardons',   'Chanson française aux accents folk.',           'img/folk-chardons.jpg',      'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-11 21:45'),
-- Soirée 5
('44444444-0000-0000-0000-000000000009', '22222222-0000-0000-0000-000000000005', 'rock',          'Néons et guitares',   'Rock moderne aux couleurs néon.',               'img/neons-guitares.jpg',     'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-13 19:30'),
('44444444-0000-0000-0000-000000000010', '22222222-0000-0000-0000-000000000005', 'chanson',       'Piano-voix',          'Un récital de chansons au piano.',              'img/piano-voix.jpg',         'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-13 20:45'),
('44444444-0000-0000-0000-000000000011', '22222222-0000-0000-0000-000000000005', 'métal',         'Étincelles',          'Un final métal explosif.',                      'img/etincelles.jpg',         'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-13 22:00'),
-- Soirée 6
('44444444-0000-0000-0000-000000000012', '22222222-0000-0000-0000-000000000006', 'rock',          'Tous ensemble',       'Les artistes du festival réunis sur scène.',    'img/tous-ensemble.jpg',      'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-17 20:30'),
('44444444-0000-0000-0000-000000000013', '22222222-0000-0000-0000-000000000006', 'classic rock',  'Dernier rappel',      'Les classiques pour clôturer le festival.',     'img/dernier-rappel.jpg',     'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-17 22:00'),
-- Soirée 7
('44444444-0000-0000-0000-000000000014', '22222222-0000-0000-0000-000000000007', 'blues rock',    'Acoustique au comptoir', 'Un concert acoustique en petit comité.',     'img/acoustique-comptoir.jpg','https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2027-06-15 21:30');

-- Artistes <-> spectacles
INSERT INTO artiste_spectacle (artiste_id, spectacle_id) VALUES
('33333333-0000-0000-0000-000000000001', '44444444-0000-0000-0000-000000000001'),
('33333333-0000-0000-0000-000000000002', '44444444-0000-0000-0000-000000000002'),
('33333333-0000-0000-0000-000000000003', '44444444-0000-0000-0000-000000000003'),
('33333333-0000-0000-0000-000000000004', '44444444-0000-0000-0000-000000000004'),
('33333333-0000-0000-0000-000000000003', '44444444-0000-0000-0000-000000000004'), -- 2 artistes
('33333333-0000-0000-0000-000000000005', '44444444-0000-0000-0000-000000000005'),
('33333333-0000-0000-0000-000000000006', '44444444-0000-0000-0000-000000000006'),
('33333333-0000-0000-0000-000000000007', '44444444-0000-0000-0000-000000000007'),
('33333333-0000-0000-0000-000000000008', '44444444-0000-0000-0000-000000000008'),
('33333333-0000-0000-0000-000000000009', '44444444-0000-0000-0000-000000000009'),
('33333333-0000-0000-0000-000000000010', '44444444-0000-0000-0000-000000000010'),
('33333333-0000-0000-0000-000000000011', '44444444-0000-0000-0000-000000000011'),
('33333333-0000-0000-0000-000000000001', '44444444-0000-0000-0000-000000000012'),
('33333333-0000-0000-0000-000000000009', '44444444-0000-0000-0000-000000000012'),
('33333333-0000-0000-0000-000000000011', '44444444-0000-0000-0000-000000000012'),
('33333333-0000-0000-0000-000000000002', '44444444-0000-0000-0000-000000000013'),
('33333333-0000-0000-0000-000000000004', '44444444-0000-0000-0000-000000000014');
*
-- Commandes et billets (tarifs : normal 25 €, réduit 15 €)
INSERT INTO commande (id, utilisateur_id, statut, montant_total, created_at, paid_at) VALUES
('55555555-0000-0000-0000-000000000001', '11111111-0000-0000-0000-000000000001', 'validee',    65.00, '2026-10-19 10:00', '2026-10-19 10:02'),
('55555555-0000-0000-0000-000000000002', '11111111-0000-0000-0000-000000000002', 'validee',   100.00, '2026-10-19 14:00', '2026-10-19 14:03'),
('55555555-0000-0000-0000-000000000003', '11111111-0000-0000-0000-000000000001', 'en_attente', 25.00, '2026-10-19 09:00', NULL);

INSERT INTO billet (commande_id, soiree_id, ref, token, tarif_reduit, prix) VALUES
('55555555-0000-0000-0000-000000000001', '22222222-0000-0000-0000-000000000001', 'NRV-0001', md5(random()::text) || md5(random()::text), FALSE, 25.00),
('55555555-0000-0000-0000-000000000001', '22222222-0000-0000-0000-000000000001', 'NRV-0002', md5(random()::text) || md5(random()::text), FALSE, 25.00),
('55555555-0000-0000-0000-000000000001', '22222222-0000-0000-0000-000000000001', 'NRV-0003', md5(random()::text) || md5(random()::text), TRUE,  15.00),
('55555555-0000-0000-0000-000000000002', '22222222-0000-0000-0000-000000000007', 'NRV-0004', md5(random()::text) || md5(random()::text), FALSE, 25.00),
('55555555-0000-0000-0000-000000000002', '22222222-0000-0000-0000-000000000007', 'NRV-0005', md5(random()::text) || md5(random()::text), FALSE, 25.00),
('55555555-0000-0000-0000-000000000002', '22222222-0000-0000-0000-000000000007', 'NRV-0006', md5(random()::text) || md5(random()::text), FALSE, 25.00),
('55555555-0000-0000-0000-000000000002', '22222222-0000-0000-0000-000000000007', 'NRV-0007', md5(random()::text) || md5(random()::text), FALSE, 25.00);