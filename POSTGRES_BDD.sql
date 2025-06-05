-- Création de la table Utilisateurs
CREATE TABLE Utilisateurs (
    ID_U SERIAL PRIMARY KEY,
    Nom VARCHAR(50) NOT NULL,
    Role VARCHAR(50) NOT NULL
);

-- Création de la table Anecdote
CREATE TABLE Anecdote (
    ID_Ann SERIAL PRIMARY KEY,
    Nom VARCHAR(50) NOT NULL,
    text TEXT NOT NULL,
    Date_ DATE NOT NULL,
    tags VARCHAR(50) NOT NULL
);

-- Création de la table Articles
CREATE TABLE Articles (
    ID_A SERIAL PRIMARY KEY,
    image_miniature VARCHAR(50) NOT NULL,
    Nom VARCHAR(50) NOT NULL,
    Date_creation TIMESTAMP NOT NULL,
    text TEXT NOT NULL,
    tags VARCHAR(50) NOT NULL,
    ID_Ann INTEGER NOT NULL,
    ID_U INTEGER NOT NULL,
    CONSTRAINT fk_articles_anecdote FOREIGN KEY (ID_Ann) REFERENCES Anecdote(ID_Ann),
    CONSTRAINT fk_articles_utilisateur FOREIGN KEY (ID_U) REFERENCES Utilisateurs(ID_U)
);

-- Création d'index pour améliorer les performances
CREATE INDEX idx_articles_id_ann ON Articles(ID_Ann);
CREATE INDEX idx_articles_id_u ON Articles(ID_U);
CREATE INDEX idx_articles_date_creation ON Articles(Date_creation);
CREATE INDEX idx_anecdote_date ON Anecdote(Date_);

-- Commentaires pour documenter les tables
COMMENT ON TABLE Utilisateurs IS 'Table des utilisateurs du système';
COMMENT ON TABLE Anecdote IS 'Table des anecdotes';
COMMENT ON TABLE Articles IS 'Table des articles liés aux anecdotes et utilisateurs';

COMMENT ON COLUMN Utilisateurs.ID_U IS 'Identifiant unique de l''utilisateur';
COMMENT ON COLUMN Utilisateurs.Nom IS 'Nom de l''utilisateur';
COMMENT ON COLUMN Utilisateurs.Role IS 'Rôle de l''utilisateur dans le système';

COMMENT ON COLUMN Anecdote.ID_Ann IS 'Identifiant unique de l''anecdote';
COMMENT ON COLUMN Anecdote.text IS 'Contenu textuel de l''anecdote';
COMMENT ON COLUMN Anecdote.Date_ IS 'Date de l''anecdote';
COMMENT ON COLUMN Anecdote.tags IS 'Tags associés à l''anecdote';

COMMENT ON COLUMN Articles.ID_A IS 'Identifiant unique de l''article';
COMMENT ON COLUMN Articles.Date_creation IS 'Date et heure de création de l''article';
COMMENT ON COLUMN Articles.text IS 'Contenu textuel de l''article';
COMMENT ON COLUMN Articles.ID_Ann IS 'Référence vers l''anecdote associée';
COMMENT ON COLUMN Articles.ID_U IS 'Référence vers l''utilisateur auteur';