CREATE TABLE etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    matricule VARCHAR(20) UNIQUE
);

CREATE TABLE absences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT,
    date_absence DATE,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id)
);

